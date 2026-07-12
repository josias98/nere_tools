<?php

namespace App\Services\Monitoring;

use Illuminate\Http\Request;
use Sentry\Severity;
use Sentry\State\Scope;
use Throwable;

use function Sentry\captureMessage;
use function Sentry\withScope;

class HttpStatusSentryReporter
{
    public function report(Request $request, int $status, ?Throwable $exception = null): void
    {
        $route = $request->route();
        $routeIdentifier = $route?->getName() ?? $route?->uri() ?? 'unmatched';

        withScope(function (Scope $scope) use ($request, $status, $exception, $routeIdentifier): void {
            $scope->setTags([
                'http.status_code' => (string) $status,
                'http.status_family' => intdiv($status, 100).'xx',
                'http.method' => $request->method(),
            ]);
            $scope->setContext('http_response', array_filter([
                'status_code' => $status,
                'method' => $request->method(),
                'route' => $routeIdentifier,
                'exception' => $exception ? $exception::class : null,
            ]));
            $scope->setFingerprint(['http-status', (string) $status, $routeIdentifier]);

            captureMessage(
                sprintf('HTTP %d %s %s', $status, $request->method(), $routeIdentifier),
                match (intdiv($status, 100)) {
                    3 => Severity::info(),
                    4 => Severity::warning(),
                    default => Severity::error(),
                },
            );
        });
    }
}
