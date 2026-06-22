<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class MicrosoftAuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function redirect(Request $request): RedirectResponse
    {
        $this->ensureMicrosoftIsConfigured();

        $state = Str::random(64);
        $request->session()->put('microsoft_oauth_state', $state);

        $query = http_build_query([
            'client_id' => config('services.microsoft.client_id'),
            'response_type' => 'code',
            'redirect_uri' => config('services.microsoft.redirect_uri'),
            'response_mode' => 'query',
            'scope' => implode(' ', config('services.microsoft.scopes')),
            'state' => $state,
            'prompt' => 'select_account',
        ]);

        return redirect()->away($this->authorityUrl().'/oauth2/v2.0/authorize?'.$query);
    }

    public function callback(Request $request): RedirectResponse|Response
    {
        if ($request->string('state')->toString() !== $request->session()->pull('microsoft_oauth_state')) {
            return redirect()
                ->route('login')
                ->withErrors(['microsoft' => 'La session de connexion Microsoft a expire. Veuillez recommencer.']);
        }

        if ($request->filled('error')) {
            Log::warning('Microsoft sign-in failed.', [
                'error' => $request->input('error'),
                'error_description' => $request->input('error_description'),
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['microsoft' => 'La connexion Microsoft a ete annulee ou refusee.']);
        }

        $this->ensureMicrosoftIsConfigured();

        try {
            $token = $this->exchangeCodeForToken($request->string('code')->toString());
            $profile = $this->fetchMicrosoftProfile($token['access_token']);
        } catch (Throwable $exception) {
            Log::error('Microsoft sign-in callback failed.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('login')
                ->withErrors(['microsoft' => 'Impossible de finaliser la connexion Microsoft pour le moment.']);
        }

        $email = Str::lower($profile['mail'] ?: $profile['userPrincipalName'] ?: '');

        if ($email === '') {
            Log::warning('Microsoft profile did not include an email address.', [
                'microsoft_id' => $profile['id'] ?? null,
            ]);

            return response()
                ->view('auth.denied', [
                    'email' => null,
                    'reason' => "Microsoft n'a pas renvoye d'adresse email utilisable pour ce compte.",
                ], 403);
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $user) {
            $employee = Employee::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->first();

            if ($employee) {
                // ponytail: employee email is the local allow-list; create the app user on first sign-in.
                $user = User::query()->create([
                    'name' => $profile['displayName'] ?: $employee->name(),
                    'email' => $email,
                    'microsoft_id' => $profile['id'] ?? null,
                    'role' => User::ROLE_USER,
                    'is_active' => true,
                    'last_login_at' => now(),
                ]);
            }
        }

        if (! $user || ! $user->is_active) {
            Log::notice('Microsoft authenticated user is not locally authorized.', [
                'email' => $email,
                'microsoft_id' => $profile['id'] ?? null,
            ]);

            return response()
                ->view('auth.denied', [
                    'email' => $email,
                    'reason' => "Votre compte est authentifie, mais il n'est pas autorise a acceder a ce portail.",
                ], 403);
        }

        $user->forceFill([
            'name' => $profile['displayName'] ?: $user->name,
            'email' => $email,
            'microsoft_id' => $profile['id'],
            'last_login_at' => now(),
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * @return array{access_token: string}
     */
    private function exchangeCodeForToken(string $code): array
    {
        $response = $this->microsoftHttp()->asForm()->post($this->authorityUrl().'/oauth2/v2.0/token', [
            'client_id' => config('services.microsoft.client_id'),
            'client_secret' => config('services.microsoft.client_secret'),
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.microsoft.redirect_uri'),
        ])->throw();

        return $response->json();
    }

    /**
     * @return array{id: string, displayName: string|null, mail: string|null, userPrincipalName: string|null}
     */
    private function fetchMicrosoftProfile(string $accessToken): array
    {
        return $this->microsoftHttp()
            ->withToken($accessToken)
            ->get('https://graph.microsoft.com/v1.0/me', [
                '$select' => 'id,displayName,mail,userPrincipalName',
            ])
            ->throw()
            ->json();
    }

    private function authorityUrl(): string
    {
        return sprintf(
            'https://login.microsoftonline.com/%s',
            config('services.microsoft.tenant_id'),
        );
    }

    private function microsoftHttp(): PendingRequest
    {
        $request = Http::acceptJson();
        $caBundle = config('services.microsoft.ca_bundle');

        if (filled($caBundle)) {
            $caBundlePath = base_path($caBundle);

            if (File::exists($caBundlePath)) {
                $request = $request->withOptions([
                    'verify' => $caBundlePath,
                ]);
            }
        }

        return $request;
    }

    private function ensureMicrosoftIsConfigured(): void
    {
        foreach (['tenant_id', 'client_id', 'client_secret', 'redirect_uri'] as $key) {
            if (blank(config("services.microsoft.$key"))) {
                abort(503, 'La connexion Microsoft 365 n\'est pas encore configuree.');
            }
        }
    }
}
