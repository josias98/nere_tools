<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Leaves\LeaveRequestController;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\SignatureDocument;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ViewErrorBag;

// Render production Blade templates with fictitious data and an in-memory database.
require __DIR__.'/../../vendor/autoload.php';
foreach ([
    'APP_ENV' => 'testing', 'APP_KEY' => 'base64:'.base64_encode(str_repeat('t', 32)),
    'APP_URL' => 'https://tools.example.test', 'APP_CONFIG_CACHE' => __DIR__.'/unused-config.php',
    'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:', 'DB_URL' => '',
    'SESSION_DRIVER' => 'array', 'CACHE_STORE' => 'array', 'MAIL_MAILER' => 'array',
    'GRAPH_MAIL_ENABLED' => 'false', 'SENTRY_LARAVEL_DSN' => '', 'PULSE_ENABLED' => 'false',
] as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $_SERVER[$key] = $value;
}
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (config('database.connections.sqlite.database') !== ':memory:') {
    throw new RuntimeException('The DOM fixtures require an in-memory database.');
}
Artisan::call('migrate', ['--force' => true]);
Vite::useHotFile(__DIR__.'/no-hot');
URL::forceRootUrl('https://tools.example.test');
URL::forceScheme('https');
$app['request']->setLaravelSession($app['session']->driver());
View::share('errors', new ViewErrorBag);
$user = User::factory()->create(['name' => 'Zoé Kaboré', 'email' => 'zoe@example.test', 'is_active' => true]);
Auth::setUser($user);
Employee::query()->create([
    'email' => $user->email, 'first_name' => 'Zoé', 'last_name' => 'Kaboré', 'display_name' => $user->name,
    'entity' => 'NERE', 'location' => 'Ouagadougou', 'job_title' => 'Test', 'analytic_code' => 'TEST',
    'is_active' => true, 'leave_eligible' => true, 'hire_date' => '2025-01-01',
]);
foreach ([['annual_leave', 'Congé administratif', false], ['other_absence', 'Autre absence', false], ['non_occupational_illness', 'Maladie', true]] as [$slug, $name, $attachment]) {
    LeaveType::query()->create(['slug' => $slug, 'name' => $name, 'unit' => 'working_day', 'is_active' => true, 'requires_attachment' => $attachment]);
}
$certificate = ['ready' => true, 'mode' => 'test', 'fingerprint' => str_repeat('a', 64), 'subject' => 'Néré — Test', 'issuer' => 'Néré — Test', 'valid_until' => '2030-01-01'];
$document = new SignatureDocument([
    'uuid' => '00000000-0000-4000-8000-000000000001', 'user_id' => $user->id,
    'file_name' => 'Document de test.pdf', 'page_count' => 2, 'original_size' => 2048,
    'original_sha256' => str_repeat('b', 64), 'created_at' => now(),
]);
$context = ['document' => $document, 'certificate' => $certificate, 'integrityError' => null];
$pages = [
    'signatureImport' => view('signatures.index', ['certificate' => $certificate, 'documents' => new LengthAwarePaginator([], 0, 12)])->render(),
    'signatureReview' => view('signatures.show', $context)->render(),
    'leaves' => app(LeaveRequestController::class)->create()->render(),
    'dashboard' => app(DashboardController::class)()->render(),
];
$document->forceFill(['signed_at' => now(), 'signature_mode' => 'test', 'signer_name' => $user->name, 'signer_email' => $user->email, 'reason' => 'Bon pour accord', 'certificate_subject' => $certificate['subject'], 'signed_sha256' => str_repeat('c', 64), 'certificate_sha256' => $certificate['fingerprint']]);
$pages['signatureSigned'] = view('signatures.show', $context)->render();
$pages['signatureUnavailable'] = view('signatures.show', ['document' => $document->forceFill(['signed_at' => null]), 'certificate' => ['ready' => false, 'message' => 'Certificat indisponible.'], 'integrityError' => null])->render();
echo json_encode($pages, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
