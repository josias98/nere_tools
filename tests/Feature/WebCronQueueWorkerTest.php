<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\NotificationLog;
use App\Services\Leaves\LeaveApprovalReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebCronQueueWorkerTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_worker_rejects_requests_without_webcron_credentials(): void
    {
        config([
            'services.webcron.username' => 'infomaniak',
            'services.webcron.password' => 'secret',
        ]);

        $this->get('/webcron/queue')
            ->assertUnauthorized()
            ->assertHeader('WWW-Authenticate', 'Basic realm="Nere Tools WebCron"');
    }

    public function test_authenticated_webcron_checks_due_approval_reminders(): void
    {
        config([
            'services.webcron.username' => 'infomaniak',
            'services.webcron.password' => 'secret',
        ]);
        $reminders = \Mockery::mock(LeaveApprovalReminderService::class);
        $reminders->shouldReceive('sendDue')->once()->andReturn(0);
        app()->instance(LeaveApprovalReminderService::class, $reminders);

        $this->withBasicAuth('infomaniak', 'secret')
            ->get('/webcron/queue')
            ->assertOk();
    }

    public function test_reminder_failure_does_not_block_the_queue_worker(): void
    {
        config([
            'services.webcron.username' => 'infomaniak',
            'services.webcron.password' => 'secret',
        ]);
        $reminders = \Mockery::mock(LeaveApprovalReminderService::class);
        $reminders->shouldReceive('sendDue')->once()->andThrow(new \RuntimeException('Database unavailable'));
        app()->instance(LeaveApprovalReminderService::class, $reminders);

        $this->withBasicAuth('infomaniak', 'secret')
            ->get('/webcron/queue')
            ->assertOk();
    }

    public function test_authenticated_webcron_request_processes_queued_mail_jobs(): void
    {
        config([
            'queue.default' => 'database',
            'services.webcron.username' => 'infomaniak',
            'services.webcron.password' => 'secret',
            'services.graph_mail.enabled' => true,
            'services.graph_mail.tenant_id' => 'tenant-id',
            'services.graph_mail.client_id' => 'client-id',
            'services.graph_mail.client_secret' => 'client-secret',
            'services.graph_mail.from_address' => 'tools@nerecapital.com',
            'services.graph_mail.from_name' => 'Nere Tools',
            'services.graph_mail.timeout' => 15,
            'services.graph_mail.connect_timeout' => 10,
            'services.graph_mail.save_to_sent_items' => true,
            'services.graph_mail.token_cache_key' => 'tests.webcron_graph_mail_token',
        ]);

        Cache::forget('tests.webcron_graph_mail_token');
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'token-123',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/*/sendMail' => Http::response([], 202, ['request-id' => 'graph-123']),
        ]);

        $notification = NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => 'system.test',
            'to_recipients' => ['recipient@nerecapital.com'],
            'subject' => 'Test WebCron',
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        SendGraphMailJob::dispatch(
            ['recipient@nerecapital.com'],
            'Test WebCron',
            '<p>Test</p>',
            event: 'system.test',
            notificationLogId: $notification->id,
        );

        $this->assertDatabaseCount('jobs', 1);

        $this->withBasicAuth('infomaniak', 'secret')
            ->get('/webcron/queue')
            ->assertOk()
            ->assertSeeText('Queue processed.');

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseHas('notification_logs', [
            'id' => $notification->id,
            'status' => 'sent',
            'graph_request_id' => 'graph-123',
        ]);
    }
}
