<?php

namespace Tests\Unit;

use App\Services\Microsoft\GraphMailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GraphMailServiceTest extends TestCase
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
        ]);

        Cache::forget('tests.graph_mail_token');
    }

    public function test_it_posts_to_the_graph_send_mail_endpoint(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'token-123',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/*/sendMail' => Http::response([], 202, ['request-id' => 'graph-123']),
        ]);

        $requestId = app(GraphMailService::class)->send(
            to: ['validator@nerecapital.com'],
            subject: 'Nouvelle demande',
            html: '<p>Bonjour</p>',
            event: 'leave.submitted',
            relatedType: 'leave_request',
            relatedId: 12,
        );

        $this->assertSame('graph-123', $requestId);

        Http::assertSent(function ($request): bool {
            if ($request->url() !== 'https://graph.microsoft.com/v1.0/users/tools@nerecapital.com/sendMail') {
                return false;
            }

            $data = $request->data();

            return $data['message']['subject'] === 'Nouvelle demande'
                && $data['message']['body']['content'] === '<p>Bonjour</p>'
                && $data['message']['toRecipients'][0]['emailAddress']['address'] === 'validator@nerecapital.com'
                && $data['saveToSentItems'] === true;
        });
    }
}
