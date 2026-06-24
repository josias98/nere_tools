<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveDocument;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
        $validatorKey = Str::random(6);
        [$validator, $validatorEmployee] = $this->userWithEmployee("validator-{$validatorKey}@nere.test", 'Validator '.$validatorKey);
        $leaveRequest = $this->leaveRequestFor($employee, $requester, $startDate, $endDate);

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

        $this->actingAs($validator)->post(route('leaves.validations.approve', $leaveRequest->uuid), [
            'reviewer_comment' => 'OK',
        ])->assertRedirect();

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

        return LeaveRequest::query()->create([
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
    }
}
