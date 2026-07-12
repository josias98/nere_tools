<?php

namespace Tests\Feature;

use App\Services\Monitoring\HttpStatusSentryReporter;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class HttpStatusSentryReportingTest extends TestCase
{
    public function test_every_3xx_4xx_and_5xx_response_is_reported_to_sentry(): void
    {
        $this->mock(HttpStatusSentryReporter::class, function (MockInterface $mock): void {
            foreach ([302, 404, 503] as $status) {
                $mock->shouldReceive('report')
                    ->once()
                    ->withArgs(fn (Request $request, int $actualStatus, ?Throwable $exception = null): bool => $actualStatus === $status);
            }
        });

        Route::get('/_test-sentry-redirect', fn () => redirect('/'));
        Route::get('/_test-sentry-not-found', fn () => abort(404));
        Route::get('/_test-sentry-server-error', fn () => response('unavailable', 503));

        $this->get('/_test-sentry-redirect')->assertRedirect('/');
        $this->get('/_test-sentry-not-found')->assertNotFound();
        $this->get('/_test-sentry-server-error')->assertStatus(503);
    }

    public function test_successful_responses_are_not_reported(): void
    {
        $this->mock(HttpStatusSentryReporter::class, fn (MockInterface $mock) => $mock->shouldNotReceive('report'));
        Route::get('/_test-sentry-ok', fn () => response('ok'));

        $this->get('/_test-sentry-ok')->assertOk();
    }

    public function test_monitoring_failure_never_changes_the_response(): void
    {
        $this->mock(HttpStatusSentryReporter::class, fn (MockInterface $mock) => $mock->shouldReceive('report')->once()->andThrow(new RuntimeException('Sentry unavailable')));
        Route::get('/_test-sentry-safe', fn () => response('missing', 404));

        $this->get('/_test-sentry-safe')->assertNotFound()->assertSee('missing');
    }

    public function test_framework_exceptions_keep_their_real_http_status(): void
    {
        $this->mock(HttpStatusSentryReporter::class, function (MockInterface $mock): void {
            $mock->shouldReceive('report')->once()->withArgs(fn (Request $request, int $status) => $status === 409);
            $mock->shouldReceive('report')->once()->withArgs(fn (Request $request, int $status) => $status === 404);
        });
        Route::get('/_test-sentry-conflict', fn () => throw new HttpResponseException(response('conflict', 409)));
        Route::get('/_test-sentry-model-missing', fn () => throw (new ModelNotFoundException)->setModel('Example'));

        $this->get('/_test-sentry-conflict')->assertStatus(409);
        $this->get('/_test-sentry-model-missing')->assertNotFound();
    }

    public function test_statuses_below_error_families_are_not_reported(): void
    {
        $this->mock(HttpStatusSentryReporter::class, fn (MockInterface $mock) => $mock->shouldNotReceive('report'));
        Route::get('/_test-sentry-299', fn () => response('ok', 299));

        $this->get('/_test-sentry-299')->assertStatus(299);
    }
}
