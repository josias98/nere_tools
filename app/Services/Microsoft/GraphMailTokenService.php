<?php

namespace App\Services\Microsoft;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GraphMailTokenService
{
    public function accessToken(): string
    {
        if (! config('services.graph_mail.enabled')) {
            throw new RuntimeException('Microsoft Graph Mail est desactive.');
        }

        $this->ensureConfigured();

        $cacheKey = (string) config('services.graph_mail.token_cache_key');
        $cachedToken = Cache::get($cacheKey);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        return $this->requestToken($cacheKey);
    }

    private function requestToken(string $cacheKey): string
    {
        $response = $this->microsoftHttp()->asForm()
            ->timeout(config('services.graph_mail.timeout'))
            ->connectTimeout(config('services.graph_mail.connect_timeout'))
            ->post('https://login.microsoftonline.com/'.config('services.graph_mail.tenant_id').'/oauth2/v2.0/token', [
                'client_id' => config('services.graph_mail.client_id'),
                'client_secret' => config('services.graph_mail.client_secret'),
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ])
            ->throw()
            ->json();

        $token = $response['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new RuntimeException("Microsoft Graph n'a pas renvoye de token utilisable.");
        }

        Cache::put(
            $cacheKey,
            $token,
            now()->addSeconds(max(300, ((int) ($response['expires_in'] ?? 3600)) - 300)),
        );

        return $token;
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

    private function ensureConfigured(): void
    {
        foreach (['tenant_id', 'client_id', 'client_secret', 'from_address'] as $key) {
            if (blank(config("services.graph_mail.{$key}"))) {
                throw new RuntimeException('La configuration Microsoft Graph Mail est incomplete.');
            }
        }
    }
}
