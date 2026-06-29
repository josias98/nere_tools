<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminLeaveNotificationLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_notification_logs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => 'leave.pending_supervisor',
            'to_recipients' => ['validator@nere.test'],
            'subject' => 'Demande de conge a valider - Superviseur',
            'status' => 'failed',
            'error_message' => 'Graph said no.',
            'queued_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.leaves.notifications.index'))
            ->assertOk()
            ->assertSee('Notifications conges')
            ->assertSee('Graph said no.');
    }

    public function test_admin_can_retry_a_failed_leave_notification(): void
    {
        Queue::fake();
        config(['services.graph_mail.enabled' => true]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $employee = Employee::query()->create([
            'first_name' => 'Nere',
            'last_name' => 'User',
            'display_name' => 'Nere User',
            'email' => 'requester@nere.test',
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => 'TEST',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);

        $leaveType = LeaveType::query()->create([
            'name' => 'Conge annuel',
            'slug' => 'annual-'.Str::random(6),
        ]);

        $leaveRequest = LeaveRequest::query()->create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-05',
            'requested_days' => 5,
            'status' => 'pending_supervisor',
            'submitted_at' => now(),
            'created_by_user_id' => $admin->id,
        ]);

        $validator = Employee::query()->create([
            'first_name' => 'Val',
            'last_name' => 'Idator',
            'display_name' => 'Val Idator',
            'email' => 'validator@nere.test',
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Validator',
            'analytic_code' => 'VAL',
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);

        LeaveValidator::query()->create([
            'employee_id' => $validator->id,
            'step_key' => 'supervisor',
            'scope' => 'global',
            'is_active' => true,
            'notify_by_email' => true,
        ]);

        $leaveRequest->approvals()->createMany([
            ['step_order' => 1, 'step_key' => 'supervisor', 'step_label' => 'Superviseur', 'status' => 'pending'],
            ['step_order' => 2, 'step_key' => 'hr', 'step_label' => 'RH / Admin-Finance', 'status' => 'pending'],
            ['step_order' => 3, 'step_key' => 'dg', 'step_label' => 'DG / Direction', 'status' => 'pending'],
        ]);

        $notification = NotificationLog::query()->create([
            'channel' => 'office365_graph',
            'provider' => 'microsoft_graph',
            'event' => 'leave.pending_supervisor',
            'related_type' => LeaveRequest::class,
            'related_id' => $leaveRequest->id,
            'to_recipients' => ['validator@nere.test'],
            'subject' => 'Demande de conge a valider - Superviseur',
            'status' => 'failed',
            'error_message' => 'Graph said no.',
            'queued_at' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.leaves.notifications.retry', $notification))
            ->assertRedirect()
            ->assertSessionHas('success', 'Notification relancee.');

        Queue::assertPushed(SendGraphMailJob::class);
        $this->assertDatabaseCount('notification_logs', 2);
    }
}
