<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveSequentialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_follow_the_approval_chain_only(): void
    {
        Queue::fake();
        config(['services.graph_mail.enabled' => true]);
        Storage::fake('local');

        [$requester] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$supervisor, , $hr, $dg] = $this->team();
        $type = LeaveType::query()->create(['name' => 'Conge annuel', 'slug' => 'annual-test']);
        LeaveBalance::query()->create(['employee_id' => $requester->employee->id, 'reference_date' => '2026-01-01', 'initial_remaining_days' => 30]);

        $this->actingAs($requester)->post(route('leaves.store'), [
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
        ])->assertRedirect();

        $this->assertNotification('leave.pending_supervisor', ['supervisor@nere.test']);

        $leaveRequest = LeaveRequest::query()->firstOrFail();
        $this->actingAs($supervisor)->post(route('leaves.validations.approve', $leaveRequest->uuid))->assertRedirect();
        $this->assertSame('pending_hr', $leaveRequest->fresh()->status);
        $this->assertNotification('leave.pending_hr', ['hr@nere.test']);

        $this->actingAs($hr)->post(route('leaves.validations.approve', $leaveRequest->uuid))->assertRedirect();
        $this->assertNotification('leave.pending_dg', ['dg@nere.test']);

        $this->actingAs($dg)->post(route('leaves.validations.approve', $leaveRequest->uuid))->assertRedirect();
        $this->assertNotification('leave.approved', ['requester@nere.test', 'supervisor@nere.test', 'hr@nere.test', 'dg@nere.test']);
        $this->assertSame('approved', $leaveRequest->fresh()->status);
        $this->assertNotNull($leaveRequest->fresh()->document);
    }

    public function test_only_current_step_validator_can_act_and_see_the_request(): void
    {
        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$supervisor, , $hr] = $this->team();
        $leaveRequest = $this->requestWithApprovals($employee, $requester);

        $this->actingAs($hr)->get(route('leaves.validations.show', $leaveRequest->uuid))->assertForbidden();
        $this->actingAs($supervisor)->get(route('leaves.validations.show', $leaveRequest->uuid))->assertOk();
        $this->actingAs($hr)->post(route('leaves.validations.approve', $leaveRequest->uuid))->assertForbidden();

        $this->actingAs($supervisor)->post(route('leaves.validations.approve', $leaveRequest->uuid))->assertRedirect();
        $this->actingAs($hr)->get(route('leaves.validations.show', $leaveRequest->uuid))->assertOk();
    }

    public function test_hr_rejection_notifies_requester_and_previous_approvers_only(): void
    {
        Queue::fake();
        config(['services.graph_mail.enabled' => true]);

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$supervisor, , $hr] = $this->team();
        $leaveRequest = $this->requestWithApprovals($employee, $requester);

        $this->actingAs($supervisor)->post(route('leaves.validations.approve', $leaveRequest->uuid));
        $this->actingAs($hr)->post(route('leaves.validations.reject', $leaveRequest->uuid), [
            'reviewer_comment' => 'Solde a verifier',
        ])->assertRedirect();

        Queue::assertPushed(SendGraphMailJob::class, fn (SendGraphMailJob $job): bool => $job->event === 'leave.rejected'
            && collect($job->to)->sort()->values()->all() === ['requester@nere.test', 'supervisor@nere.test']
            && ! in_array('dg@nere.test', $job->to, true));
    }

    public function test_admin_dashboard_shows_audit_data_without_mutating_history(): void
    {
        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$supervisor, $supervisorEmployee] = $this->team();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
        $leaveRequest = $this->requestWithApprovals($employee, $requester);

        $this->actingAs($supervisor)->post(route('leaves.validations.approve', $leaveRequest->uuid));
        $historical = $leaveRequest->approvals()->pluck('validator_user_id', 'step_key')->all();

        $replacement = Employee::query()->create([
            'first_name' => 'New',
            'last_name' => 'Boss',
            'display_name' => 'New Boss',
            'email' => 'new-boss@nere.test',
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Boss',
            'analytic_code' => 'BOSS',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);
        LeaveValidator::query()->where('employee_id', $supervisorEmployee->id)->update(['employee_id' => $replacement->id]);

        NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => 'leave.pending_hr',
            'related_type' => LeaveRequest::class,
            'related_id' => $leaveRequest->id,
            'to_recipients' => ['hr@nere.test'],
            'subject' => 'Demande de conge a valider - RH / Admin-Finance',
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.leaves.index', ['status' => 'pending_hr']))
            ->assertOk()
            ->assertSee('Suivi des demandes')
            ->assertSee('Requester Test')
            ->assertSee('RH / Admin-Finance');

        $this->assertSame($historical, $leaveRequest->fresh()->approvals()->pluck('validator_user_id', 'step_key')->all());
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function userWithEmployee(string $email, string $name): array
    {
        $user = User::factory()->create(['name' => $name, 'email' => $email, 'role' => User::ROLE_USER, 'is_active' => true]);
        $employee = Employee::query()->create([
            'first_name' => $name,
            'last_name' => 'Test',
            'display_name' => $name.' Test',
            'email' => $email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => strtoupper(Str::substr($name, 0, 4)),
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);

        return [$user, $employee];
    }

    /**
     * @return array{0: User, 1: Employee, 2: User, 3: User}
     */
    private function team(): array
    {
        [$supervisor, $supervisorEmployee] = $this->userWithEmployee('supervisor@nere.test', 'Supervisor');
        [$hr] = $this->userWithEmployee('hr@nere.test', 'HR');
        [$dg] = $this->userWithEmployee('dg@nere.test', 'DG');
        $hr->forceFill(['role' => User::ROLE_FINANCE])->save();
        $dg->forceFill(['role' => User::ROLE_DIRECTION])->save();

        LeaveValidator::query()->create([
            'employee_id' => $supervisorEmployee->id,
            'step_key' => 'supervisor',
            'scope' => 'global',
            'is_active' => true,
            'notify_by_email' => true,
        ]);

        return [$supervisor, $supervisorEmployee, $hr, $dg];
    }

    private function requestWithApprovals(Employee $employee, User $user): LeaveRequest
    {
        $type = LeaveType::query()->create(['name' => 'Conge annuel', 'slug' => 'annual-'.Str::random(6)]);
        LeaveBalance::query()->create(['employee_id' => $employee->id, 'reference_date' => '2026-01-01', 'initial_remaining_days' => 30]);
        $request = LeaveRequest::query()->create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
            'requested_days' => 5,
            'status' => 'pending_supervisor',
            'submitted_at' => now(),
            'created_by_user_id' => $user->id,
        ]);
        $request->approvals()->createMany([
            ['step_order' => 1, 'step_key' => 'supervisor', 'step_label' => 'Superviseur', 'status' => 'pending'],
            ['step_order' => 2, 'step_key' => 'hr', 'step_label' => 'RH / Admin-Finance', 'status' => 'pending'],
            ['step_order' => 3, 'step_key' => 'dg', 'step_label' => 'DG / Direction', 'status' => 'pending'],
        ]);

        return $request;
    }

    /**
     * @param array<int, string> $recipients
     */
    private function assertNotification(string $event, array $recipients): void
    {
        $notification = NotificationLog::query()->where('event', $event)->latest('id')->first();

        $this->assertNotNull($notification, 'Missing notification '.$event);
        $this->assertSame($recipients, $notification->to_recipients);
    }
}
