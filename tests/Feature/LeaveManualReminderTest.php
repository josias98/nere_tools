<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveManualReminderTest extends TestCase
{
    use RefreshDatabase;

    private User $requester;

    private User $firstSignatory;

    private User $secondSignatory;

    private User $hr;

    private LeaveRequest $leave;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        config(['services.graph_mail.enabled' => true]);
        $this->travelTo('2026-10-15 10:00:00');
        $this->requester = $this->person('requester');
        $this->firstSignatory = $this->person('first-signatory');
        $this->secondSignatory = $this->person('second-signatory');
        $this->hr = $this->person('hr', User::ROLE_FINANCE);
        foreach ([$this->firstSignatory, $this->secondSignatory] as $signatory) {
            LeaveValidator::query()->create([
                'employee_id' => $signatory->employee->id, 'step_key' => 'supervisor',
                'scope' => 'global', 'is_active' => true, 'notify_by_email' => true,
            ]);
        }
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'annual-test']);
        $this->leave = LeaveRequest::query()->create([
            'uuid' => Str::uuid(), 'employee_id' => $this->requester->employee->id,
            'leave_type_id' => $type->id, 'start_date' => '2026-11-02', 'end_date' => '2026-11-03',
            'requested_days' => 2, 'status' => 'pending_supervisor',
            'submitted_at' => now()->subDays(3), 'created_by_user_id' => $this->requester->id,
        ]);
        $this->leave->approvals()->createMany([
            ['step_order' => 1, 'step_key' => 'supervisor', 'step_label' => 'Supérieur hiérarchique', 'status' => 'pending'],
            ['step_order' => 2, 'step_key' => 'hr', 'step_label' => 'RH', 'status' => 'pending'],
            ['step_order' => 3, 'step_key' => 'dg', 'step_label' => 'Direction', 'status' => 'pending'],
        ]);
    }

    public function test_requester_can_choose_one_current_signatory_to_remind(): void
    {
        $this->actingAs($this->requester)->get(route('leaves.show', $this->leave->uuid))
            ->assertOk()->assertSee('Relancer par email')
            ->assertSee($this->firstSignatory->email)->assertSee($this->secondSignatory->email)
            ->assertDontSee($this->hr->email);

        $this->post(route('leaves.remind', $this->leave->uuid), $this->payload())
            ->assertRedirect(route('leaves.show', $this->leave->uuid).'#relance')
            ->assertSessionHas('success');

        Queue::assertPushed(SendGraphMailJob::class, fn (SendGraphMailJob $job): bool => $job->to === [$this->firstSignatory->email]
            && $job->event === 'leave.reminder_pending_supervisor'
            && str_starts_with($job->subject, 'Relance - ')
            && str_contains($job->html, 'Cette demande attend toujours votre validation.')
            && str_contains($job->html, route('leaves.validations.show', $this->leave->uuid)));
        $this->assertSame('pending_supervisor', $this->leave->fresh()->status);
        $this->assertSame(['pending', 'pending', 'pending'], $this->leave->approvals()->pluck('status')->all());
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->requester->id, 'action' => 'leave.reminder_pending_supervisor',
            'auditable_id' => $this->leave->id,
        ]);
    }

    public function test_an_unrelated_employee_cannot_send_a_reminder(): void
    {
        $this->actingAs($this->person('outsider'))
            ->post(route('leaves.remind', $this->leave->uuid), $this->payload())
            ->assertForbidden();
        Queue::assertNothingPushed();
    }

    public function test_a_future_step_or_arbitrary_recipient_is_rejected(): void
    {
        $this->actingAs($this->requester);
        foreach ([$this->hr->email, 'external@example.test'] as $email) {
            $this->post(route('leaves.remind', $this->leave->uuid), $this->payload($email))
                ->assertSessionHasErrors('reminder');
        }
        Queue::assertNothingPushed();
    }

    public function test_a_stale_page_cannot_remind_an_approval_that_has_finished(): void
    {
        $payload = $this->payload($this->hr->email);
        $this->leave->currentApproval->update(['status' => 'approved']);
        $this->leave->update(['status' => 'pending_hr']);

        $this->actingAs($this->requester)->post(route('leaves.remind', $this->leave->uuid), $payload)
            ->assertSessionHasErrors('reminder');
        Queue::assertNothingPushed();
    }

    public function test_terminal_requests_cannot_be_reminded(): void
    {
        $this->actingAs($this->requester);
        foreach (['approved', 'rejected', 'cancelled'] as $status) {
            $this->leave->update(['status' => $status]);
            $this->post(route('leaves.remind', $this->leave->uuid), $this->payload())
                ->assertSessionHasErrors('reminder');
            $this->get(route('leaves.show', $this->leave->uuid))->assertDontSee('Relancer par email');
        }
        Queue::assertNothingPushed();
    }

    public function test_the_one_hour_limit_is_shared_and_scoped_to_each_signatory(): void
    {
        $this->actingAs($this->requester)->post(route('leaves.remind', $this->leave->uuid), $this->payload())
            ->assertSessionHas('success');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)->post(route('leaves.remind', $this->leave->uuid), $this->payload())
            ->assertSessionHasErrors('reminder');
        Queue::assertPushed(SendGraphMailJob::class, 1);

        $this->post(route('leaves.remind', $this->leave->uuid), $this->payload($this->secondSignatory->email))
            ->assertSessionHas('success');
        $this->travel(61)->minutes();
        $this->post(route('leaves.remind', $this->leave->uuid), $this->payload())
            ->assertSessionHas('success');
        Queue::assertPushed(SendGraphMailJob::class, 3);
    }

    public function test_disabled_mail_is_explained_and_does_not_claim_success(): void
    {
        config(['services.graph_mail.enabled' => false]);
        $this->actingAs($this->requester)->get(route('leaves.show', $this->leave->uuid))
            ->assertSee('L’envoi des emails est désactivé.')->assertDontSee('Relancer par email');
        $this->post(route('leaves.remind', $this->leave->uuid), $this->payload())
            ->assertSessionHasErrors('reminder');
        Queue::assertNothingPushed();
    }

    private function payload(?string $email = null): array
    {
        return ['approval_id' => $this->leave->currentApproval->id, 'recipient' => $email ?? $this->firstSignatory->email];
    }

    private function person(string $name, string $role = User::ROLE_USER): User
    {
        $user = User::factory()->create(['name' => $name, 'email' => $name.'@nere.test', 'role' => $role]);
        Employee::query()->create([
            'first_name' => $name, 'last_name' => 'Test', 'display_name' => $name.' Test', 'email' => $user->email,
            'entity' => 'NERE', 'location' => 'Ouagadougou', 'job_title' => 'Test', 'analytic_code' => 'TEST',
            'is_active' => true, 'leave_eligible' => true,
        ]);

        return $user;
    }
}
