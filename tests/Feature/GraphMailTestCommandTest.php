<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\NotificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GraphMailTestCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.graph_mail.enabled' => true,
            'services.graph_mail.tenant_id' => 'tenant-id',
            'services.graph_mail.client_id' => 'client-id',
            'services.graph_mail.client_secret' => 'client-secret',
            'services.graph_mail.from_address' => 'tools@nerecapital.com',
            'services.graph_mail.from_name' => 'Nere Tools',
            'services.graph_mail.timeout' => 15,
            'services.graph_mail.connect_timeout' => 10,
            'services.graph_mail.save_to_sent_items' => true,
            'services.graph_mail.token_cache_key' => 'tests.graph_mail_token',
            'services.graph_mail.test_recipient' => 'tests@nerecapital.com',
        ]);

        Cache::forget('tests.graph_mail_token');
    }

    public function test_it_queues_a_test_email(): void
    {
        Queue::fake();

        $this->artisan('graph-mail:test')
            ->expectsOutput("Notification de test mise en file d'attente pour tests@nerecapital.com.")
            ->assertSuccessful();

        Queue::assertPushed(SendGraphMailJob::class);
        $this->assertDatabaseHas('notification_logs', [
            'event' => 'system.test',
            'status' => 'queued',
            'subject' => 'Test de notification Nere Tools',
        ]);
    }

    public function test_it_can_send_sync(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'token-123',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/*/sendMail' => Http::response([], 202, ['request-id' => 'graph-123']),
        ]);

        $this->artisan('graph-mail:test sync@nerecapital.com --sync')
            ->expectsOutput('Email de test envoye a sync@nerecapital.com.')
            ->assertSuccessful();

        $notification = NotificationLog::query()->latest('id')->first();

        $this->assertNotNull($notification);
        $this->assertSame('sent', $notification->status);
        $this->assertSame('graph-123', $notification->graph_request_id);
    }
}
