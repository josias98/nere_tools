<?php

namespace App\Http\Middleware;

use App\Services\Monitoring\HttpStatusSentryReporter;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ReportHttpStatusToSentry
{
    public function __construct(private HttpStatusSentryReporter $reporter) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $status = $this->statusFor($exception);
            if ($status < 500) {
                $this->reportSafely($request, $status, $exception);
            }

            throw $exception;
        }

        if ($response->getStatusCode() >= 300 && $response->getStatusCode() < 600) {
            $this->reportSafely($request, $response->getStatusCode());
        }

        return $response;
    }

    private function statusFor(Throwable $exception): int
    {
        return match (true) {
            $exception instanceof HttpResponseException => $exception->getResponse()->getStatusCode(),
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            $exception instanceof ValidationException => 422,
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AuthorizationException => 403,
            $exception instanceof ModelNotFoundException => 404,
            $exception instanceof TokenMismatchException => 419,
            default => 500,
        };
    }

    private function reportSafely(Request $request, int $status, ?Throwable $exception = null): void
    {
        try {
            $this->reporter->report($request, $status, $exception);
        } catch (Throwable) {
            // Monitoring must never alter the application response.
        }
    }
}
