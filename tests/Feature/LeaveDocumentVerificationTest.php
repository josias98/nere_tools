<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveDocument;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\User;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeavePdfService;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeaveDocumentVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_page_displays_active_document(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();

        $this->get(route('leaves.verify.show', $document->verification_token))
            ->assertOk()
            ->assertSee('Document authentique')
            ->assertSee($document->document_reference);
    }

    public function test_unknown_token_shows_safe_message(): void
    {
        $this->get(route('leaves.verify.show', Str::random(64)))
            ->assertOk()
            ->assertSee("Aucun document correspondant n'a ete trouve dans Nere Tools.");
    }

    public function test_upload_of_known_pdf_returns_valid_active(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();
        $file = UploadedFile::fake()->createWithContent(
            'document.pdf',
            Storage::disk('local')->get($document->file_path)
        );

        $this->post(route('leaves.verify.upload'), ['file' => $file])
            ->assertOk()
            ->assertSee('Correspondance exacte')
            ->assertSee($document->document_reference);
    }

    public function test_upload_of_unknown_pdf_returns_unknown(): void
    {
        $file = UploadedFile::fake()->createWithContent('unknown.pdf', '%PDF-1.4 unknown');

        $this->post(route('leaves.verify.upload'), ['file' => $file])
            ->assertOk()
            ->assertSee('Correspondance inconnue');
    }

    public function test_upload_of_non_pdf_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('notes.txt', 1, 'text/plain');

        $this->post(route('leaves.verify.upload'), ['file' => $file])
            ->assertStatus(422)
            ->assertSee('Fichier invalide');
    }

    public function test_document_reference_and_token_are_unique(): void
    {
        Storage::fake('local');

        $first = $this->approvedDocument('requester-1@nere.test', '2026-08-01', '2026-08-05');
        $second = $this->approvedDocument('requester-2@nere.test', '2026-09-01', '2026-09-03');

        $this->assertNotSame($first->document_reference, $second->document_reference);
        $this->assertNotSame($first->verification_token, $second->verification_token);
    }

    public function test_document_generation_uses_a_year_lock(): void
    {
        Storage::fake('local');
        $approvalLock = \Mockery::mock(Lock::class);
        $approvalLock->shouldReceive('block')
            ->times(3)
            ->with(10, \Mockery::type(\Closure::class))
            ->andReturnUsing(fn (int $seconds, \Closure $callback) => $callback());
        $lock = \Mockery::mock(Lock::class);
        $lock->shouldReceive('block')
            ->once()
            ->with(10, \Mockery::type(\Closure::class))
            ->andReturnUsing(fn (int $seconds, \Closure $callback) => $callback());
        Cache::shouldReceive('lock')
            ->once()
            ->with('leave-document-reference:2026', 120)
            ->andReturn($lock);
        Cache::shouldReceive('lock')
            ->times(3)
            ->with(\Mockery::pattern('/^leave-approval-employee:/'), 120)
            ->andReturn($approvalLock);

        $this->approvedDocument();
    }

    public function test_admin_can_revoke_document(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.leaves.documents.revoke', $document), [
                'reason' => 'Document remplace par decision RH.',
            ])
            ->assertRedirect();

        $this->assertSame(LeaveDocument::STATUS_REVOKED, $document->fresh()->status);
    }

    public function test_revocation_requires_a_reason(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->from(route('leaves.show', $document->leaveRequest->uuid))
            ->followingRedirects()
            ->post(route('admin.leaves.documents.revoke', $document), ['document_action' => 'revoke'])
            ->assertOk()
            ->assertSee('Indiquez le motif de la révocation.')
            ->assertSee('aria-invalid="true"', false);

        $this->assertSame(LeaveDocument::STATUS_ACTIVE, $document->fresh()->status);
    }

    public function test_admin_can_cancel_a_revocation(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $identity = $document->only(['file_path', 'document_reference', 'verification_token', 'sha256_hash', 'signed_at']);
        $pdf = Storage::disk('local')->get($document->file_path);
        $balance = app(LeaveBalanceService::class)->getBalance($document->leaveRequest->employee);

        $this->actingAs($admin)
            ->post(route('admin.leaves.documents.revoke', $document), ['reason' => 'Révocation erronée.'])
            ->assertRedirect(route('leaves.show', $document->leaveRequest->uuid).'#attestation');

        $this->get(route('leaves.verify.show', $document->verification_token))
            ->assertSee('Document revoque');

        $this->actingAs($admin)
            ->get(route('leaves.show', $document->leaveRequest->uuid))
            ->assertOk()
            ->assertSee('Annuler la révocation')
            ->assertSee('Révocation erronée.')
            ->assertDontSee('Confirmer la révocation');

        $this->actingAs($admin)
            ->post(route('admin.leaves.documents.restore', $document))
            ->assertRedirect();

        $document->refresh();
        $this->assertSame(LeaveDocument::STATUS_ACTIVE, $document->status);
        $this->assertNull($document->status_reason);
        $this->assertEquals($identity, $document->only(array_keys($identity)));
        $this->assertSame($pdf, Storage::disk('local')->get($document->file_path));
        $this->assertSame('approved', $document->leaveRequest->fresh()->status);
        $this->assertSame($balance, app(LeaveBalanceService::class)->getBalance($document->leaveRequest->employee));
        $this->assertSame(1, $document->leaveRequest->documents()->count());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'leave_document.revocation_cancelled',
            'auditable_type' => LeaveDocument::class,
            'auditable_id' => $document->id,
        ]);
        $this->get(route('leaves.verify.show', $document->verification_token))
            ->assertSee('Document authentique');
    }

    public function test_employee_cannot_revoke_or_restore_their_own_attestation(): void
    {
        Storage::fake('local');
        $document = $this->approvedDocument();
        $requester = User::query()->where('email', $document->leaveRequest->employee->email)->firstOrFail();

        $this->actingAs($requester)
            ->post(route('admin.leaves.documents.revoke', $document), ['reason' => 'Test'])
            ->assertForbidden();
        $document->forceFill(['status' => LeaveDocument::STATUS_REVOKED])->save();
        $this->get(route('leaves.show', $document->leaveRequest->uuid))
            ->assertOk()
            ->assertSee('Cette attestation n’est plus valide.')
            ->assertDontSee('Annuler la révocation');
        $this->post(route('admin.leaves.documents.restore', $document))->assertForbidden();
    }

    public function test_a_stale_document_cannot_revoke_a_replaced_version(): void
    {
        Storage::fake('local');
        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $replacement = app(LeavePdfService::class)->regenerate($document->leaveRequest, $admin);

        try {
            app(LeavePdfService::class)->revoke($document, $admin, 'Depuis un ancien écran');
            $this->fail('A stale document must not change a replaced version.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('n’est plus active', $exception->getMessage());
        }

        $this->assertSame(LeaveDocument::STATUS_REPLACED, $document->fresh()->status);
        $this->assertSame(LeaveDocument::STATUS_ACTIVE, $replacement->fresh()->status);
    }

    public function test_only_the_latest_revoked_attestation_can_be_restored(): void
    {
        Storage::fake('local');
        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $service = app(LeavePdfService::class);
        $service->revoke($document, $admin, 'Erreur');
        $replacement = $service->regenerate($document->leaveRequest, $admin);
        $service->revoke($replacement, $admin, 'Erreur sur la dernière version');

        $this->actingAs($admin)->post(route('admin.leaves.documents.restore', $document))
            ->assertSessionHasErrors('document');
        $this->assertSame(LeaveDocument::STATUS_REVOKED, $document->fresh()->status);

        $this->post(route('admin.leaves.documents.restore', $replacement))->assertSessionHasNoErrors();
        $this->assertSame(LeaveDocument::STATUS_ACTIVE, $replacement->fresh()->status);
    }

    public function test_an_attestation_cannot_be_restored_for_a_cancelled_request(): void
    {
        Storage::fake('local');
        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $document->forceFill(['status' => LeaveDocument::STATUS_REVOKED])->save();
        $document->leaveRequest->forceFill(['status' => 'cancelled'])->save();

        $this->actingAs($admin)->post(route('admin.leaves.documents.restore', $document))
            ->assertSessionHasErrors('document');
        $this->assertSame(LeaveDocument::STATUS_REVOKED, $document->fresh()->status);
    }

    public function test_revocation_cannot_be_cancelled_when_another_document_is_active(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $document->forceFill(['status' => LeaveDocument::STATUS_REVOKED])->save();
        LeaveDocument::query()->create([
            'leave_request_id' => $document->leave_request_id,
            'document_reference' => 'NERE-CONGES-2026-999999',
            'file_name' => 'replacement.pdf',
            'status' => LeaveDocument::STATUS_ACTIVE,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.leaves.documents.restore', $document))
            ->assertSessionHasErrors('document');

        $this->assertSame(LeaveDocument::STATUS_REVOKED, $document->fresh()->status);
    }

    public function test_admin_can_regenerate_document(): void
    {
        Storage::fake('local');

        $document = $this->approvedDocument();
        $request = $document->leaveRequest;
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('admin.leaves.documents.regenerate', $request->uuid), [
                'reason' => 'Mise a jour manuelle du visa.',
            ])
            ->assertRedirect();

        $request->refresh();
        $documents = $request->documents()->get();

        $this->assertCount(2, $documents);
        $this->assertSame(LeaveDocument::STATUS_REPLACED, $document->fresh()->status);
        $this->assertNotSame($document->document_reference, $request->document->document_reference);
        $this->assertSame(LeaveDocument::STATUS_ACTIVE, $request->document->status);
    }

    private function approvedDocument(
        string $email = 'requester@nere.test',
        string $startDate = '2026-08-01',
        string $endDate = '2026-08-05',
    ): LeaveDocument {
        [$requester, $employee] = $this->userWithEmployee($email, Str::before($email, '@'));
        $leaveRequest = $this->leaveRequestFor($employee, $requester, $startDate, $endDate);

        LeaveBalance::query()->create([
            'employee_id' => $employee->id,
            'reference_date' => '2026-01-01',
            'initial_remaining_days' => 30,
        ]);
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
        ]);

        foreach ([$supervisor, $hr, $dg] as $validator) {
            $this->actingAs($validator)->post(route('leaves.validations.approve', $leaveRequest->uuid), [
                'reviewer_comment' => 'OK',
            ])->assertRedirect();
        }

        return $leaveRequest->fresh()->document;
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
            'first_name' => Str::headline($name),
            'last_name' => 'Test',
            'display_name' => Str::headline($name).' Test',
            'email' => $email,
            'entity' => 'NERE',
            'location' => 'Ouagadougou',
            'job_title' => 'Test',
            'analytic_code' => strtoupper(Str::substr(Str::slug($name, ''), 0, 6) ?: 'TEST'),
            'is_active' => true,
            'leave_eligible' => true,
            'hire_date' => '2026-01-01',
        ]);

        return [$user, $employee];
    }

    private function leaveRequestFor(Employee $employee, User $user, string $startDate, string $endDate): LeaveRequest
    {
        $type = LeaveType::query()->create(['name' => 'Conge annuel', 'slug' => 'annual-'.Str::random(6)]);

        $request = LeaveRequest::query()->create([
            'uuid' => Str::uuid(),
            'employee_id' => $employee->id,
            'leave_type_id' => $type->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
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
}
