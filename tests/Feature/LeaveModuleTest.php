<?php

namespace Tests\Feature;

use App\Jobs\SendGraphMailJob;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\User;
use App\Services\Leaves\LeavePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
        $this->approveAll($leaveRequest);

        $leaveRequest->refresh();

        $this->assertSame('approved', $leaveRequest->status);
        $this->assertNotNull($leaveRequest->document);
        Storage::disk('local')->assertExists($leaveRequest->document->file_path);
    }

    public function test_rejected_request_does_not_generate_pdf(): void
    {
        Storage::fake('local');

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        [$supervisor] = $this->approvalTeam();

        $this->actingAs($supervisor)
            ->post(route('leaves.validations.reject', $leaveRequest->uuid), [
                'reviewer_comment' => 'Refus motive',
            ])
            ->assertRedirect(route('leaves.validations.index'));

        $leaveRequest->refresh();

        $this->assertSame('rejected', $leaveRequest->status);
        $this->assertNull($leaveRequest->document);
    }

    public function test_pdf_generation_is_idempotent_for_approved_request(): void
    {
        Storage::fake('local');

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
        [, , $validator] = $this->approveAll($leaveRequest);

        $leaveRequest->refresh();
        $firstDocument = $leaveRequest->document;

        $secondDocument = app(LeavePdfService::class)->generate($leaveRequest->fresh(), $validator);

        $this->assertSame($firstDocument->id, $secondDocument->id);
        $this->assertSame(1, $leaveRequest->fresh()->documents()->count());
    }

    public function test_generated_document_records_reference_token_and_hash(): void
    {
        Storage::fake('local');

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
        $this->approveAll($leaveRequest);

        $document = $leaveRequest->fresh()->document;

        $this->assertNotNull($document);
        $this->assertMatchesRegularExpression('/^NC-CONGES-2026-\d{5}$/', $document->document_reference);
        $this->assertNotEmpty($document->verification_token);
        $this->assertSame(route('leaves.verify.show', $document->verification_token), $document->verification_url);
        $this->assertSame(64, strlen($document->sha256_hash));
    }

    public function test_generated_pdf_uses_short_verification_hint_without_showing_raw_token_path(): void
    {
        Storage::fake('local');

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
        $this->approveAll($leaveRequest);

        $document = $leaveRequest->fresh()->document;
        $pdf = Storage::disk('local')->get($document->file_path);

        $this->assertStringContainsString('ATTESTATION DE CONGES', $pdf);
        $this->assertStringNotContainsString('/conges/verify/', $pdf);
    }

    public function test_unauthorized_user_cannot_download_leave_pdf(): void
    {
        Storage::fake('local');

        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$other] = $this->userWithEmployee('other@nere.test', 'Other');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
        $this->approveAll($leaveRequest);

        $document = $leaveRequest->fresh()->document;

        $this->actingAs($other)
            ->get(route('leaves.documents.download', $document))
            ->assertForbidden();
    }

    public function test_validator_can_open_validation_queue(): void
    {
        [$requester, $employee] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [$validator, $validatorEmployee] = $this->userWithEmployee('validator@nere.test', 'Validator');
        $leaveRequest = $this->leaveRequestFor($employee, $requester);

        LeaveValidator::query()->create([
            'employee_id' => $validatorEmployee->id,
            'step_key' => 'supervisor',
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
        Queue::fake();
        config(['services.graph_mail.enabled' => true]);

        [$requester] = $this->userWithEmployee('requester@nere.test', 'Requester');
        [, $validatorEmployee] = $this->userWithEmployee('validator@nere.test', 'Validator');
        $type = LeaveType::query()->create(['name' => 'Conge annuel', 'slug' => 'annual-test']);
        LeaveValidator::query()->create([
            'employee_id' => $validatorEmployee->id,
            'step_key' => 'supervisor',
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

        Queue::assertPushed(SendGraphMailJob::class, fn (SendGraphMailJob $job) => $job->to === ['validator@nere.test']);
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
        $type = LeaveType::query()->create(['name' => 'Conge annuel', 'slug' => 'annual-'.Str::random(6)]);

        $request = LeaveRequest::query()->create([
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

        $request->approvals()->createMany([
            ['step_order' => 1, 'step_key' => 'supervisor', 'step_label' => 'Superviseur', 'status' => 'pending'],
            ['step_order' => 2, 'step_key' => 'hr', 'step_label' => 'RH / Admin-Finance', 'status' => 'pending'],
            ['step_order' => 3, 'step_key' => 'dg', 'step_label' => 'DG / Direction', 'status' => 'pending'],
        ]);

        $request->forceFill(['status' => 'pending_supervisor'])->save();

        return $request;
    }

    /**
     * @return array{0: User, 1: User, 2: User}
     */
    private function approvalTeam(): array
    {
        [$supervisor, $supervisorEmployee] = $this->userWithEmployee('supervisor-'.Str::random(6).'@nere.test', 'Supervisor');
        [$hr] = $this->userWithEmployee('hr-'.Str::random(6).'@nere.test', 'HR');
        [$dg] = $this->userWithEmployee('dg-'.Str::random(6).'@nere.test', 'DG');
        $hr->forceFill(['role' => User::ROLE_FINANCE])->save();
        $dg->forceFill(['role' => User::ROLE_DIRECTION])->save();

        LeaveValidator::query()->create([
            'employee_id' => $supervisorEmployee->id,
            'step_key' => 'supervisor',
            'scope' => 'global',
            'is_active' => true,
            'notify_by_email' => true,
        ]);

        return [$supervisor, $hr, $dg];
    }

    /**
     * @return array{0: User, 1: User, 2: User}
     */
    private function approveAll(LeaveRequest $leaveRequest): array
    {
        [$supervisor, $hr, $dg] = $this->approvalTeam();

        foreach ([[$supervisor, 'supervisor'], [$hr, 'hr'], [$dg, 'dg']] as [$user, $step]) {
            $this->actingAs($user)
                ->post(route('leaves.validations.approve', $leaveRequest->uuid), [
                    'reviewer_comment' => 'OK '.$step,
                ])
                ->assertRedirect(route('leaves.validations.index'));
        }

        return [$supervisor, $hr, $dg];
    }
}
