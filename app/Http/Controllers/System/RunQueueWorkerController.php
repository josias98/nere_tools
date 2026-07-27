<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class RunQueueWorkerController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $username = config('services.webcron.username');
        $password = config('services.webcron.password');

        if (! is_string($username)
            || ! is_string($password)
            || $username === ''
            || $password === ''
            || ! hash_equals($username, (string) $request->getUser())
            || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Nere Tools WebCron"',
            ]);
        }

        // ponytail: one job keeps a cold Graph send inside the shared-hosting
        // HTTP window; use a supervised worker if throughput exceeds four jobs/hour.
        $exitCode = Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-jobs' => 1,
            '--max-time' => 45,
            '--timeout' => 40,
            '--tries' => 3,
            '--no-interaction' => true,
        ]);

        return $exitCode === 0
            ? response('Queue processed.')
            : response('Queue processing failed.', 500);
    }
}
