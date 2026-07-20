<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Models\User;
use App\Services\Leaves\LeaveBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeaveRequestCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_hr_can_create_a_request_for_an_employee_without_email(): void
    {
        [$hr, $hrEmployee] = $this->userWithEmployee('hr@nere.test', 'Responsable RH', User::ROLE_ADMIN);
        $beneficiary = $this->employee('Fabien', null);
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'admin-annual']);
        LeaveBalance::query()->create([
            'employee_id' => $beneficiary->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 20,
        ]);

        $this->actingAs($hr)->post(route('admin.leaves.requests.store'), [
            'employee_id' => $beneficiary->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-05',
        ])->assertRedirect();

        $leaveRequest = LeaveRequest::query()->sole();
        $this->assertSame($beneficiary->id, $leaveRequest->employee_id);
        $this->assertSame($hr->id, $leaveRequest->created_by_user_id);
        $this->assertNull($beneficiary->fresh()->email);
        $this->assertSame(3.0, app(LeaveBalanceService::class)->getBalance($beneficiary)['pending_days']);
        $this->assertSame(0.0, app(LeaveBalanceService::class)->getBalance($hrEmployee)['pending_days']);

        $context = 'Demande saisie par Responsable RH pour Fabien Test';
        $this->get(route('leaves.show', $leaveRequest->uuid))->assertOk()->assertSee($context);
        $this->get(route('admin.leaves.index', ['section' => 'requests']))
            ->assertOk()
            ->assertSee($context)
            ->assertSee('Créer une demande pour un collaborateur');
    }

    public function test_unauthorized_user_cannot_create_a_request_for_another_employee(): void
    {
        [$user] = $this->userWithEmployee('user@nere.test', 'Utilisateur');
        $beneficiary = $this->employee('Fabien', null);
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'forbidden-admin-annual']);

        $this->actingAs($user)->post(route('admin.leaves.requests.store'), [
            'employee_id' => $beneficiary->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-03',
            'end_date' => '2026-08-05',
        ])->assertForbidden();

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_admin_form_only_offers_active_eligible_employees_and_shows_their_balance(): void
    {
        [$hr] = $this->userWithEmployee('hr-form@nere.test', 'RH Formulaire', User::ROLE_ADMIN);
        $eligible = $this->employee('Fabien', null);
        $this->employee('Inactive', null, ['is_active' => false]);
        $this->employee('Non Eligible', null, ['leave_eligible' => false]);
        LeaveBalance::query()->create([
            'employee_id' => $eligible->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 17,
        ]);

        $expectedBalance = app(LeaveBalanceService::class)->getBalance($eligible)['projected_balance'];

        $this->actingAs($hr)
            ->get(route('admin.leaves.requests.create'))
            ->assertOk()
            ->assertSee('Fabien Test')
            ->assertDontSee('Inactive Test')
            ->assertDontSee('Non Eligible Test')
            ->assertSee(number_format($expectedBalance, 2).' jours')
            ->assertSee('aria-live="polite"', false);
    }

    public function test_admin_form_explains_when_no_employee_is_eligible(): void
    {
        $hr = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($hr)
            ->get(route('admin.leaves.requests.create'))
            ->assertOk()
            ->assertSee('Aucun collaborateur actif et éligible aux congés')
            ->assertSee('disabled', false);
    }

    public function test_admin_can_submit_a_leave_type_reserved_to_administration(): void
    {
        [$hr] = $this->userWithEmployee('hr-admin-type@nere.test', 'RH Administration', User::ROLE_ADMIN);
        $beneficiary = $this->employee('Fabien', null);
        $type = LeaveType::query()->create([
            'name' => 'Absence administrative',
            'slug' => 'admin-only-type',
            'requester_scope' => 'admin',
        ]);

        $this->actingAs($hr)->post(route('admin.leaves.requests.store'), [
            'employee_id' => $beneficiary->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-08',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame($beneficiary->id, LeaveRequest::query()->sole()->employee_id);
    }

    public function test_configured_hr_supervisor_is_audited_and_request_moves_directly_to_hr(): void
    {
        [$hr, $hrEmployee] = $this->userWithEmployee('hr-supervisor@nere.test', 'Responsable RH', User::ROLE_ADMIN);
        $beneficiary = $this->employee('Fabien', null);
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'auto-supervisor-annual']);
        LeaveBalance::query()->create([
            'employee_id' => $beneficiary->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 20,
        ]);
        LeaveValidator::query()->create([
            'employee_id' => $hrEmployee->id,
            'step_key' => 'supervisor',
            'scope' => 'employee',
            'target_employee_id' => $beneficiary->id,
            'is_active' => true,
            'notify_by_email' => true,
        ]);

        $this->actingAs($hr)->post(route('admin.leaves.requests.store'), [
            'employee_id' => $beneficiary->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-10',
            'end_date' => '2026-08-12',
        ])->assertRedirect();

        $leaveRequest = LeaveRequest::query()->sole();
        $supervisorApproval = $leaveRequest->approvals()->where('step_key', 'supervisor')->sole();
        $this->assertSame('pending_hr', $leaveRequest->status);
        $this->assertSame('approved', $supervisorApproval->status);
        $this->assertSame('Supérieur hiérarchique', $supervisorApproval->step_label);
        $this->assertSame($hr->id, $supervisorApproval->validator_user_id);
        $this->assertSame($hrEmployee->id, $supervisorApproval->validator_employee_id);
        $this->assertSame('Validation hiérarchique automatique — demande introduite par le responsable RH', $supervisorApproval->comment);
        $this->assertNotNull($supervisorApproval->decided_at);
        $this->assertTrue(AuditLog::query()
            ->where('auditable_id', $leaveRequest->id)
            ->where('action', 'leave.step_approved')
            ->where('user_id', $hr->id)
            ->exists());
        $this->assertFalse(NotificationLog::query()->where('event', 'leave.pending_supervisor')->exists());
        $this->assertSame(
            ['hr-supervisor@nere.test'],
            NotificationLog::query()->where('event', 'leave.pending_hr')->sole()->to_recipients,
        );

        $this->actingAs($hr)
            ->post(route('leaves.validations.approve', $leaveRequest->uuid))
            ->assertRedirect(route('leaves.validations.index'));
        $this->assertSame('pending_dg', $leaveRequest->fresh()->status);
    }

    public function test_request_keeps_the_manual_supervisor_step_when_hr_is_not_configured_as_responsible(): void
    {
        [$hr] = $this->userWithEmployee('hr-not-supervisor@nere.test', 'RH Saisie', User::ROLE_ADMIN);
        [, $supervisorEmployee] = $this->userWithEmployee('supervisor@nere.test', 'Responsable');
        $beneficiary = $this->employee('Fabien', null);
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'manual-supervisor-annual']);
        LeaveBalance::query()->create([
            'employee_id' => $beneficiary->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 20,
        ]);
        LeaveValidator::query()->create([
            'employee_id' => $supervisorEmployee->id,
            'step_key' => 'supervisor',
            'scope' => 'employee',
            'target_employee_id' => $beneficiary->id,
            'is_active' => true,
            'notify_by_email' => true,
        ]);

        $this->actingAs($hr)->post(route('admin.leaves.requests.store'), [
            'employee_id' => $beneficiary->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-08-17',
            'end_date' => '2026-08-19',
        ])->assertRedirect();

        $leaveRequest = LeaveRequest::query()->sole();
        $this->assertSame('pending_supervisor', $leaveRequest->status);
        $this->assertSame('pending', $leaveRequest->approvals()->where('step_key', 'supervisor')->value('status'));
        $this->assertDatabaseHas('notification_logs', [
            'related_id' => $leaveRequest->id,
            'event' => 'leave.pending_supervisor',
        ]);
    }

    public function test_submission_uses_the_beneficiary_eligibility_rules(): void
    {
        [$hr] = $this->userWithEmployee('hr-eligibility@nere.test', 'RH Eligible', User::ROLE_ADMIN);
        $beneficiary = $this->employee('Fabien', null, ['location' => 'Bobo-Dioulasso']);
        $type = LeaveType::query()->create([
            'name' => 'Congé localisé',
            'slug' => 'beneficiary-eligibility',
            'eligibility_rules' => ['allowed_locations' => ['Ouagadougou']],
        ]);

        $this->actingAs($hr)
            ->from(route('admin.leaves.requests.create'))
            ->post(route('admin.leaves.requests.store'), [
                'employee_id' => $beneficiary->id,
                'leave_type_id' => $type->id,
                'start_date' => '2026-08-24',
                'end_date' => '2026-08-25',
            ])
            ->assertRedirect(route('admin.leaves.requests.create'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('leave_requests', 0);
    }

    public function test_self_service_ignores_an_employee_id_for_another_collaborator(): void
    {
        [$user, $employee] = $this->userWithEmployee('self-service@nere.test', 'Libre Service');
        $otherEmployee = $this->employee('Fabien', null);
        $type = LeaveType::query()->create(['name' => 'Congé annuel', 'slug' => 'self-service-protected']);

        $this->actingAs($user)->post(route('leaves.store'), [
            'employee_id' => $otherEmployee->id,
            'leave_type_id' => $type->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
        ])->assertRedirect();

        $this->assertSame($employee->id, LeaveRequest::query()->sole()->employee_id);
    }

    /** @return array{0: User, 1: Employee} */
    private function userWithEmployee(string $email, string $name, string $role = User::ROLE_USER): array
    {
        $user = User::factory()->create([
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'is_active' => true,
        ]);

        return [$user, $this->employee($name, $email)];
    }

    private function employee(string $name, ?string $email, array $overrides = []): Employee
    {
        return Employee::query()->create(array_merge([
            'first_name' => $name,
            'last_name' => 'Test',
            'display_name' => $name.' Test',
            'email' => $email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => strtoupper(substr($name, 0, 4)),
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2025-01-01',
        ], $overrides));
    }
}
