<?php

namespace App\Livewire\Leaves;

use App\Enums\Leaves\LeaveRequestWizardStep;
use App\Livewire\Leaves\Forms\LeaveRequestWizardForm;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\Leaves\LeaveReplacementSearch;
use App\Services\Leaves\LeaveRequestDraftService;
use App\Services\Leaves\LeaveRequestPreviewService;
use App\Services\Leaves\LeaveRequestWorkflowService;
use App\Support\Leaves\LeaveRequestRules;
use DomainException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Throwable;

class RequestWizard extends Component
{
    use WithFileUploads;

    public LeaveRequestWizardForm $form;

    #[Url(as: 'etape', history: true)]
    public int $step = 1;

    #[Url(as: 'draft')]
    public ?string $draftUuid = null;

    public array $completedSteps = [];

    public string $direction = 'forward';

    public bool $confirmed = false;

    public ?string $replacementSearch = null;

    public bool $summaryOpen = false;

    public ?string $notice = null;

    public ?string $draftExpiresAt = null;

    #[Locked]
    public ?string $createdRequestUuid = null;

    public function mount(LeaveRequestDraftService $drafts): void
    {
        $this->authorizeAction();
        $this->step = LeaveRequestWizardStep::fromInt($this->step)->value;

        $draft = $this->draftUuid
            ? $drafts->restore(auth()->user(), $this->draftUuid)
            : $drafts->latestFor(auth()->user(), $this->employee());

        if ($draft) {
            $this->draftUuid = $draft->uuid;
            $this->step = LeaveRequestWizardStep::fromInt((int) $draft->step)->value;
            $this->form->fillFromDraft($draft->payload ?? []);
            $this->draftExpiresAt = $draft->expires_at?->toDateTimeString();
            $this->completedSteps = array_values(array_filter((array) ($draft->payload['completed_steps'] ?? []), fn ($step): bool => is_numeric($step) && $step < 5));
        }
    }

    public function selectType(int $typeId): void
    {
        $this->authorizeAction();
        $type = LeaveType::query()->where('is_active', true)->findOrFail($typeId);
        $this->form->leave_type_id = $type->id;
        $this->validateStep(1);
        $this->completeAndSave(1);
        $this->goToStep(2);
    }

    public function chooseType(int $typeId): void
    {
        $this->selectType($typeId);
    }

    public function next(): void
    {
        $this->authorizeAction();
        $this->validateStep($this->step);

        if ($this->step === 2 && $this->preview()['overlaps'] !== []) {
            throw ValidationException::withMessages(['form.start_date' => 'Une demande existe deja sur cette periode. Modifiez les dates pour continuer.']);
        }

        $this->completeAndSave($this->step);
        $this->goToStep(min(4, $this->step + 1));
    }

    public function previous(): void
    {
        $this->goToStep(max(1, $this->step - 1), 'back');
    }

    public function editStep(int $step): void
    {
        if (! in_array($step, $this->completedSteps, true) && $step !== $this->step) {
            return;
        }

        $this->goToStep($step, $step < $this->step ? 'back' : 'forward');
    }

    public function saveDraft(): void
    {
        $this->authorizeAction();
        $draft = app(LeaveRequestDraftService::class)->save(
            auth()->user(),
            $this->draftUuid,
            $this->form->draftPayload() + ['completed_steps' => $this->completedSteps],
            employee: $this->employee(),
            step: $this->step,
        );

        $this->draftUuid = $draft->uuid;
        $this->draftExpiresAt = $draft->expires_at?->toDateTimeString();
        $this->notice = 'Brouillon sauvegarde.';
    }

    public function discardDraft(): void
    {
        app(LeaveRequestDraftService::class)->discard(auth()->user(), $this->draftUuid);
        $this->draftUuid = null;
        $this->draftExpiresAt = null;
        $this->notice = 'Brouillon supprime.';
    }

    public function removeAttachment(int $index): void
    {
        unset($this->form->attachments[$index]);
        $this->form->attachments = array_values($this->form->attachments);
    }

    public function updatedFormStartDate(): void
    {
        $this->saveDraft();
    }

    public function updatedFormEndDate(): void
    {
        $this->saveDraft();
    }

    public function updatedFormReason(): void
    {
        $this->saveDraft();
    }

    public function updatedFormRequesterComment(): void
    {
        $this->saveDraft();
    }

    public function updatedFormRelationship(): void
    {
        $this->saveDraft();
    }

    public function updatedFormLocation(): void
    {
        $this->saveDraft();
    }

    public function updatedFormContact(): void
    {
        $this->saveDraft();
    }

    public function updatedFormSalaryImpact(): void
    {
        $this->saveDraft();
    }

    public function updatedFormReplacementNeeded(): void
    {
        $this->saveDraft();
    }

    public function updatedFormReplacementEmployeeId(): void
    {
        $this->saveDraft();
    }

    public function submit(LeaveRequestWorkflowService $workflow): void
    {
        $this->authorizeAction();
        $this->validateStep(4);

        if (! $this->confirmed) {
            throw ValidationException::withMessages(['confirmed' => 'Confirmez explicitement la soumission definitive.']);
        }

        $payload = $this->form->payload();
        $key = 'leave-request-submit:'.auth()->id().':'.hash('sha256', json_encode($payload));

        if ($uuid = Cache::get($key.':result')) {
            $this->createdRequestUuid = $uuid;
            $this->goToStep(5);

            return;
        }

        $lock = Cache::lock($key, 60);
        if (! $lock->get()) {
            throw ValidationException::withMessages(['confirmed' => 'Cette demande est deja en cours de soumission.']);
        }

        try {
            $request = $workflow->submitRequest($this->employee(), $payload, auth()->id());
            Cache::put($key.':result', $request->uuid, now()->addMinutes(10));
            app(LeaveRequestDraftService::class)->markSubmitted(auth()->user(), $this->draftUuid);
            $this->createdRequestUuid = $request->uuid;
            $this->draftUuid = null;
            $this->completedSteps = [1, 2, 3, 4];
            $this->goToStep(5);
            $this->notice = 'Demande soumise.';
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['confirmed' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Livewire leave request submission failed.', ['user_id' => auth()->id(), 'exception' => $exception]);
            throw ValidationException::withMessages(['confirmed' => "La demande n'a pas pu etre enregistree. Veuillez reessayer."]);
        } finally {
            $lock->release();
        }
    }

    public function createAnother(): void
    {
        $this->reset(['step', 'draftUuid', 'completedSteps', 'direction', 'confirmed', 'replacementSearch', 'summaryOpen', 'notice', 'draftExpiresAt', 'createdRequestUuid']);
        $this->form->reset();
        $this->step = 1;
        $this->direction = 'forward';
    }

    #[Computed]
    public function leaveTypes(): Collection
    {
        return LeaveType::query()->where('is_active', true)->with('rules')->orderByRaw("case when slug = 'annual_leave' then 0 else 1 end")->orderBy('name')->get();
    }

    #[Computed]
    public function selectedType(): ?LeaveType
    {
        return $this->form->leave_type_id ? LeaveType::query()->with('rules')->where('is_active', true)->find($this->form->leave_type_id) : null;
    }

    #[Computed]
    public function preview(): array
    {
        return app(LeaveRequestPreviewService::class)->preview($this->employee(), $this->selectedType(), $this->form->payload());
    }

    #[Computed]
    public function replacements(): Collection
    {
        return app(LeaveReplacementSearch::class)->search(auth()->user(), (string) $this->replacementSearch);
    }

    #[Computed]
    public function createdRequest(): ?LeaveRequest
    {
        return $this->createdRequestUuid
            ? LeaveRequest::query()->with(['employee', 'leaveType', 'currentApproval', 'approvals'])->where('uuid', $this->createdRequestUuid)->first()
            : null;
    }

    public function render()
    {
        return view('livewire.leaves.request-wizard', [
            'steps' => LeaveRequestWizardStep::options(),
            'currentStep' => LeaveRequestWizardStep::fromInt($this->step),
            'visibleFields' => $this->visibleFields(),
        ]);
    }

    protected function rules(): array
    {
        return $this->form->rulesForStep(max(1, min(4, $this->step)), $this->employee());
    }

    protected function messages(): array
    {
        return LeaveRequestRules::messages();
    }

    private function validateStep(int $step): void
    {
        $this->resetErrorBag();
        $rules = [];

        foreach ($this->form->rulesForStep($step, $this->employee()) as $key => $rule) {
            $rules[str_replace('form.', '', $key)] = $rule;
        }

        $validator = Validator::make($this->form->payload(), $rules, LeaveRequestRules::messages());

        if ($validator->fails()) {
            foreach ($validator->errors()->messages() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError('form.'.$field, $message);
                }
            }

            throw ValidationException::withMessages($this->getErrorBag()->toArray());
        }
    }

    private function completeAndSave(int $step): void
    {
        if (! in_array($step, $this->completedSteps, true)) {
            $this->completedSteps[] = $step;
            sort($this->completedSteps);
        }

        $this->saveDraft();
    }

    private function goToStep(int $step, string $direction = 'forward'): void
    {
        $this->direction = $direction;
        $this->step = LeaveRequestWizardStep::fromInt($step)->value;
        $this->dispatch('leave-wizard-step-changed');
    }

    private function employee(): Employee
    {
        $employee = auth()->user()?->employee;
        abort_unless($employee && $employee->is_active && $employee->leave_eligible, 403);

        return $employee;
    }

    private function authorizeAction(): void
    {
        abort_unless(auth()->check() && auth()->user()->canAccessTool('conges'), 403);
    }

    private function visibleFields(): array
    {
        $type = $this->selectedType();
        $slug = $type?->slug ?? '';
        $duration = (float) ($this->preview()['duration'] ?? 0);

        return [
            'reason' => $type && $slug !== 'annual_leave',
            'relationship' => in_array($slug, ['family_event', 'seriously_ill_child', 'sick_spouse_assistance'], true),
            'location' => $type && in_array($slug, ['training_representation', 'union_authorization', 'other_absence', 'special_leave'], true),
            'contact' => $type && ($duration >= 3 || in_array($slug, ['maternity', 'non_occupational_illness', 'occupational_injury'], true)),
            'salary_impact' => $type && ! $type->is_paid,
            'replacement' => $type && $duration >= 2,
            'attachments' => $type && ($type->requires_attachment || $slug !== 'annual_leave'),
            'comment' => true,
        ];
    }
}
