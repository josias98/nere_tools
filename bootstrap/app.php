<?php

use App\Http\Middleware\EnsureUserCanAccessTool;
use App\Http\Middleware\EnsureUserCanAdmin;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\ReportHttpStatusToSentry;
use App\Models\AuditLog;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '127.0.0.1');
        $middleware->append(ReportHttpStatusToSentry::class);

        $middleware->alias([
            'admin-area' => EnsureUserCanAdmin::class,
            'role' => EnsureUserHasRole::class,
            'tool' => EnsureUserCanAccessTool::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);
        $auditHttpError = function (int $status, Throwable $exception, Request $request): void {
            if (! in_array($status, [403, 419], true) && $status < 500) {
                return;
            }

            try {
                AuditLog::query()->create([
                    'user_id' => $request->user()?->id,
                    'action' => 'http_error.rendered',
                    'metadata' => [
                        'status' => $status,
                        'exception' => $exception::class,
                        'message' => Str::limit($exception->getMessage(), 500),
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'ip' => $request->ip(),
                    ],
                ]);
            } catch (Throwable) {
                //
            }
        };

        $exceptions->context(function (Throwable $exception): array {
            try {
                $request = request();

                return [
                    'exception_class' => $exception::class,
                    'method' => $request->method(),
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                ];
            } catch (Throwable) {
                return [];
            }
        });

        $exceptions->report(function (Throwable $exception): void {
            try {
                $request = request();

                AuditLog::query()->create([
                    'user_id' => $request->user()?->id,
                    'action' => 'exception.reported',
                    'metadata' => [
                        'exception' => $exception::class,
                        'message' => Str::limit($exception->getMessage(), 500),
                        'method' => $request->method(),
                        'path' => $request->path(),
                        'ip' => $request->ip(),
                    ],
                ]);
            } catch (Throwable) {
                //
            }
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($auditHttpError) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return null;
            }

            $status = $exception->getStatusCode();
            $auditHttpError($status, $exception, $request);
            $view = view()->exists('errors.'.$status) ? 'errors.'.$status : 'errors.default';

            return response()->view($view, [
                'exception' => $exception,
                'status' => $status,
            ], $status, $exception->getHeaders());
        });

        $exceptions->render(function (Throwable $exception, Request $request) {
            if ($request->expectsJson()
                || $request->is('api/*')
                || $exception instanceof AuthenticationException
                || $exception instanceof HttpResponseException
                || $exception instanceof ValidationException) {
                return null;
            }

            return response()->view('errors.500', [
                'exception' => $exception,
                'status' => 500,
            ], 500);
        });
    })->create();
