<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_open_leave_dashboard(): void
    {
        [$user] = $this->userWithEmployee();

        $this->actingAs($user)
            ->get('/conges')
            ->assertOk()
            ->assertSee('Demandes de congé');
    }

    public function test_user_without_employee_is_redirected_from_leave_dashboard(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get('/conges')
            ->assertRedirect(route('dashboard'));
    }

    public function test_employee_cannot_view_another_employee_request(): void
    {
        [$owner, $ownerEmployee] = $this->userWithEmployee('owner@nere.test', 'Owner');
        [$other] = $this->userWithEmployee('other@nere.test', 'Other');
        $request = $this->leaveRequestFor($ownerEmployee, $owner);

        $this->actingAs($other)
            ->get(route('leaves.show', $request->uuid))
            ->assertForbidden();
    }

    public function test_validator_can_approve_request_and_generate_private_pdf(): void
    {
        Storage::fake('local');

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$validator, $validatorEmployee] = $this->userWithEmployee('validator@nere.test', 'Validator');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
        LeaveValidator::query()->create([
            'employee_id' => $validatorEmployee->id,
            'scope' => 'global',
            'is_active' => true,
        ]);

        $this->actingAs($validator)
            ->post(route('leaves.validations.approve', $leaveRequest->uuid), [
                'reviewer_comment' => 'OK',
            ])
            ->assertRedirect(route('leaves.validations.show', $leaveRequest->uuid));

        $leaveRequest->refresh();

        $this->assertSame('approved', $leaveRequest->status);
        $this->assertNotNull($leaveRequest->document);
        Storage::disk('local')->assertExists($leaveRequest->document->local_path);
    }

    public function test_validator_can_open_validation_queue(): void
    {
        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$validator, $validatorEmployee] = $this->userWithEmployee('validator@nere.test', 'Validator');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveValidator::query()->create([
            'employee_id' => $validatorEmployee->id,
            'scope' => 'global',
            'is_active' => true,
        ]);

        $this->actingAs($validator)
            ->get(route('leaves.validations.index'))
            ->assertOk()
            ->assertSee('Demandes en attente');

        $this->actingAs($validator)
            ->get(route('leaves.validations.show', $leaveRequest->uuid))
            ->assertOk()
            ->assertSee('Examiner la demande');
    }

    public function test_submission_emails_validator(): void
    {
        Event::fake([MessageSent::class]);

        [$requester] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [, $validatorEmployee] = $this->userWithEmployee('validator@nere.test', 'Validator');
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'annual-test']);
        LeaveValidator::query()->create([
            'employee_id' => $validatorEmployee->id,
            'scope' => 'global',
            'is_active' => true,
        ]);

        $this->actingAs($requester)
            ->post(route('leaves.store'), [
                'leave_type_id' => $type->id,
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-05',
            ])
            ->assertRedirect();

        Event::assertDispatched(MessageSent::class);
    }

    public function test_admin_can_open_leave_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.leaves.index'))
            ->assertOk()
            ->assertSee('Administration');
    }

    /**
     * @return array{0: User, 1: Employee}
     */
    private function userWithEmployee(string $email = 'user@nere.test', string $name = 'User'): array
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
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'annual-'.Str::random(6)]);

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
