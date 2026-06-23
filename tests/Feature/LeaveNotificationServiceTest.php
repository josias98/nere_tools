<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Leaves\LeaveNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveNotificationServiceTest extends TestCase
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

    public function test_submission_queues_a_job_for_active_validators(): void
    {
        Queue::fake();

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [, $validatorEmployee] = $this->userWithEmployee('validator@nere.test', 'Validator');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveValidator::query()->create([
            'employee_id' => $validatorEmployee->id,
            'scope' => 'global',
            'is_active' => true,
            'notify_by_email' => true,
        ]);

        app(LeaveNotificationService::class)->requestSubmitted($leaveRequest);

        Queue::assertPushed(SendGraphMailJob::class, function (SendGraphMailJob $job): bool {
            return $job->to === ['validator@nere.test']
                && $job->event === 'leave.submitted';
        });

        $notification = NotificationLog::query()->latest('id')->first();

        $this->assertNotNull($notification);
        $this->assertSame('queued', $notification->status);
        $this->assertSame(['validator@nere.test'], $notification->to_recipients);
        $this->assertNull($notification->error_message);
    }

    public function test_graph_failure_marks_the_notification_as_failed(): void
    {
        Http::fake([
            'login.microsoftonline.com/*/oauth2/v2.0/token' => Http::response([
                'access_token' => 'token-123',
                'expires_in' => 3600,
            ]),
            'graph.microsoft.com/v1.0/users/*/sendMail' => Http::response(['error' => 'boom'], 500),
        ]);

        $notification = NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => 'leave.submitted',
            'to_recipients' => ['validator@nere.test'],
            'subject' => 'Nouvelle demande de conge a valider',
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        try {
            dispatch_sync(new SendGraphMailJob(
                to: ['validator@nere.test'],
                subject: 'Nouvelle demande de conge a valider',
                html: '<p>Bonjour</p>',
                event: 'leave.submitted',
                notificationLogId: $notification->id,
            ));

            $this->fail('The job should have failed.');
        } catch (\Throwable) {
            $notification->refresh();
        }

        $this->assertSame('failed', $notification->status);
        $this->assertNotNull($notification->failed_at);
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function userWithEmployee(string $email, string $name): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => User::ROLE_USER,
        ]);

        $employee = Employee::query()->create([
            'first_name' => $name,
            'last_name' => 'Test',
            'display_name' => $name.' Test',
            'email' => $email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => 'TEST',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);

        return [$user, $employee];
    }

    private function leaveRequestFor(Employee $employee, User $user): LeaveRequest
    {
        $type = LeaveType::query()->create(['name' => 'Conge annuel', 'slug' => 'annual-'.Str::random(6)]);

        return LeaveRequest::query()->create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
            'requested_days' => 5,
            'status' => 'submitted',
            'submitted_at' => now(),
            'created_by_user_id' => $user->id,
        ]);
    }
}
