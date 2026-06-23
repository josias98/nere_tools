<?php

namespace Tests\Unit;

use App\Services\Microsoft\GraphMailTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GraphMailTokenServiceTest extends TestCase
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
            'services.graph_mail.timeout' => 15,
            'services.graph_mail.connect_timeout' => 10,
            'services.graph_mail.token_cache_key' => 'tests.graph_mail_token',
        ]);

        Cache::forget('tests.graph_mail_token');
    }

    public function test_it_requests_a_token_with_client_credentials(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'token-123',
                'expires_in' => 3600,
            ]),
        ]);

        $token = app(GraphMailTokenService::class)->accessToken();

        $this->assertSame('token-123', $token);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://login.microsoftonline.com/tenant-id/oauth2/v2.0/token'
                && $request['client_id'] === 'client-id'
                && $request['client_secret'] === 'client-secret'
                && $request['scope'] === 'https://graph.microsoft.com/.default'
                && $request['grant_type'] === 'client_credentials';
        });
    }

    public function test_it_caches_the_token(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'token-123',
                'expires_in' => 3600,
            ]),
        ]);

        $service = app(GraphMailTokenService::class);

        $this->assertSame('token-123', $service->accessToken());
        $this->assertSame('token-123', $service->accessToken());

        Http::assertSentCount(1);
    }
}
