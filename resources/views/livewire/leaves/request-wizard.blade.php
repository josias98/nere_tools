<section class="nc-page leave-wizard" x-data="leaveRequestWizard" data-leave-request-wizard wire:key="leave-request-wizard">
    <header class="nc-title-row">
        <div>
            <p class="nc-kicker">Conges</p>
            <h1 class="nc-title" id="leave-wizard-title" tabindex="-1">{{ $currentStep->label() }}</h1>
            <p class="nc-lead">{{ $currentStep->description() }} Sélectionnez les dates pour vérifier le solde.</p>
        </div>
    </header>

    @include('leaves.partials.flash')

    <div aria-live="polite" aria-atomic="true">
        @if ($notice)
            <div class="nc-alert is-success leave-save-state" role="status">{{ $notice }}</div>
        @endif
    </div>

    <x-nc.error-summary :errors="$errors" />

    <div class="leave-wizard-shell">
        <x-nc.stepper :steps="$steps" :current="$step" :completed="$completedSteps" />

        <main class="leave-wizard-stage" wire:loading.class="is-loading" aria-busy="false">
            <div class="leave-local-loading" wire:loading.flex>
                <span class="leave-spinner" aria-hidden="true"></span>
                <span>Calcul en cours...</span>
            </div>

            <div @class(['leave-step-panel', 'is-back' => $direction === 'back']) wire:transition.opacity.duration.180ms>
                @if ($step === 1)
                    <x-nc.recommendation kind="practice" title="Commencez par le motif reel" origin="Types actifs Nere Tools">
                        Le conge annuel est affiche en premier car c'est le choix le plus frequent, mais aucune option n'est selectionnee a votre place.
                    </x-nc.recommendation>

                    <h2>Quel congé souhaitez-vous prendre ?</h2>
                    <p class="leave-step-help">Les cartes sont des boutons de selection. Chaque type reste verifie cote serveur.</p>

                    <div class="leave-type-grid">
                        @foreach ($this->leaveTypes as $type)
                            <button
                                type="button"
                                wire:click="selectType({{ $type->id }})"
                                @class(['leave-type-card', 'is-selected' => $form->leave_type_id === $type->id, 'is-recommended' => $type->slug === 'annual_leave'])
                                aria-pressed="{{ $form->leave_type_id === $type->id ? 'true' : 'false' }}"
                            >
                                @if ($type->slug === 'annual_leave')
                                    <small>Choix le plus probable</small>
                                @endif
                                <strong>{{ $type->name }}</strong>
                                <span>{{ $type->unit->label() }} - {{ $type->is_paid ? 'paye' : 'non paye' }}</span>
                                <span>{{ $type->counts_against_balance ? 'Impacte le solde' : 'Quota separe' }}</span>
                                <span>{{ $type->requires_attachment ? 'Justificatif requis' : 'Justificatif selon contexte' }}</span>
                                <em>{{ $type->legal_reference ?: 'Reference interne Nere Capital' }}</em>
                            </button>
                        @endforeach
                    </div>
                    @error('form.leave_type_id') <p class="nc-error">{{ $message }}</p> @enderror
                @elseif ($step === 2)
                    <x-nc.recommendation kind="internal" title="Verifiez la periode avant de continuer" origin="Solde, jours feries et demandes existantes">
                        La duree, la reprise, les jours non comptabilises et les chevauchements sont recalcules par le serveur.
                    </x-nc.recommendation>

                    <h2>Periode de la demande</h2>
                    <p class="leave-step-help">Les dates de debut et de fin sont incluses dans le calcul.</p>

                    <div class="leave-date-grid">
                        <label class="nc-field" for="leave_start_date">
                            <span>Date de debut <small>Requis</small></span>
                            <input id="leave_start_date" type="date" wire:model.change="form.start_date" required @error('form.start_date') aria-invalid="true" aria-describedby="start_date_error" @enderror>
                            @error('form.start_date') <small id="start_date_error" class="nc-error">{{ $message }}</small> @enderror
                        </label>
                        <label class="nc-field" for="leave_end_date">
                            <span>Date de fin <small>Requis</small></span>
                            <input id="leave_end_date" type="date" wire:model.change="form.end_date" required @error('form.end_date') aria-invalid="true" aria-describedby="end_date_error" @enderror>
                            @error('form.end_date') <small id="end_date_error" class="nc-error">{{ $message }}</small> @enderror
                        </label>
                    </div>

                    @if ($this->preview['warnings'])
                        <div class="nc-alert leave-warning-list" role="status">
                            <strong>Points a verifier</strong>
                            <ul>
                                @foreach ($this->preview['warnings'] as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($this->preview['non_counted_days'])
                        <div class="leave-warning-list">
                            <strong>Jours non comptabilises</strong>
                            <ul>
                                @foreach ($this->preview['non_counted_days'] as $day)
                                    <li>{{ $day }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($this->preview['overlaps'])
                        <div class="nc-alert leave-warning-list" role="alert">
                            <strong>Chevauchement detecte</strong>
                            <ul>
                                @foreach ($this->preview['overlaps'] as $overlap)
                                    <li>{{ $overlap['start_date'] }} - {{ $overlap['end_date'] }} : {{ $overlap['status'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @elseif ($step === 3)
                    <x-nc.recommendation kind="practice" title="Ne renseignez que l'utile" origin="Type choisi et duree calculee">
                        Les champs non necessaires sont masques pour eviter la ressaisie et les commentaires RH inutiles.
                    </x-nc.recommendation>

                    <h2>Informations complementaires</h2>
                    <p class="leave-step-help">Les champs facultatifs sont indiques. Les justificatifs restent stockes en prive.</p>

                    <div class="leave-date-grid">
                        @if ($visibleFields['reason'])
                            <label class="nc-field"><span>Motif <small>Selon le type</small></span><input wire:model.blur="form.reason">@error('form.reason') <small class="nc-error">{{ $message }}</small> @enderror</label>
                        @endif
                        @if ($visibleFields['relationship'])
                            <label class="nc-field"><span>Lien de parente <small>Selon le type</small></span><input wire:model.blur="form.relationship">@error('form.relationship') <small class="nc-error">{{ $message }}</small> @enderror</label>
                        @endif
                        @if ($visibleFields['location'])
                            <label class="nc-field"><span>Lieu <small>Facultatif</small></span><input wire:model.blur="form.location">@error('form.location') <small class="nc-error">{{ $message }}</small> @enderror</label>
                        @endif
                        @if ($visibleFields['contact'])
                            <label class="nc-field"><span>Contact pendant l'absence <small>Facultatif</small></span><input wire:model.blur="form.contact">@error('form.contact') <small class="nc-error">{{ $message }}</small> @enderror</label>
                        @endif
                        @if ($visibleFields['salary_impact'])
                            <label class="nc-field"><span>Incidence sur le traitement <small>Facultatif</small></span><input wire:model.blur="form.salary_impact">@error('form.salary_impact') <small class="nc-error">{{ $message }}</small> @enderror</label>
                        @endif
                    </div>

                    @if ($visibleFields['replacement'])
                        <div class="leave-replacement">
                            <label class="nc-mini-check"><input type="checkbox" wire:model.live="form.replacement_needed"> Un remplacement est necessaire</label>
                            @if ($form->replacement_needed)
                                <label class="nc-field" for="replacement_search">
                                    <span>Rechercher un remplacant actif <small>Facultatif</small></span>
                                    <input id="replacement_search" type="search" wire:model.live.debounce.350ms="replacementSearch" autocomplete="off">
                                </label>
                                <div class="leave-combobox" role="listbox" aria-label="Salaries actifs">
                                    @forelse ($this->replacements as $employee)
                                        <button type="button" wire:click="$set('form.replacement_employee_id', {{ $employee->id }})" @class(['is-selected' => $form->replacement_employee_id === $employee->id])>
                                            {{ $employee->name() }} <span>{{ $employee->job_title }}</span>
                                        </button>
                                    @empty
                                        <p class="nc-muted">Aucun salarie actif trouve.</p>
                                    @endforelse
                                </div>
                                @error('form.replacement_employee_id') <small class="nc-error">{{ $message }}</small> @enderror
                            @endif
                        </div>
                    @endif

                    @if ($visibleFields['attachments'])
                        <label class="leave-dropzone" for="leave_attachments" x-on:dragover.prevent="$el.classList.add('is-dragging')" x-on:dragleave.prevent="$el.classList.remove('is-dragging')" x-on:drop="$el.classList.remove('is-dragging')">
                            <i data-lucide="upload-cloud" aria-hidden="true"></i>
                            <strong>Pieces justificatives privees</strong>
                            <span>{{ $this->selectedType?->requires_attachment ? 'Obligatoire pour ce type.' : 'Ajoutez un document si cela clarifie la demande.' }} PDF ou image, 10 Mo maximum.</span>
                            <input id="leave_attachments" type="file" wire:model="form.attachments" multiple accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                        <div class="leave-upload-progress" x-show="uploading" x-cloak>
                            <progress max="100" x-bind:value="progress"></progress>
                            <span x-text="`${progress}%`"></span>
                        </div>
                        @error('form.attachments') <p class="nc-error">{{ $message }}</p> @enderror
                        @error('form.attachments.*') <p class="nc-error">{{ $message }}</p> @enderror
                        @if ($form->attachments)
                            <ul class="leave-file-list">
                                @foreach ($form->attachments as $index => $file)
                                    <li>
                                        <span>{{ $file->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="removeAttachment({{ $index }})">Retirer</button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @endif

                    <label class="nc-field" for="requester_comment">
                        <span>Commentaire <small>Facultatif</small></span>
                        <textarea id="requester_comment" rows="4" wire:model.blur="form.requester_comment" placeholder="Information utile pour les validateurs"></textarea>
                        @error('form.requester_comment') <small class="nc-error">{{ $message }}</small> @enderror
                    </label>
                @elseif ($step === 4)
                    <x-nc.recommendation kind="legal" title="La soumission declenche le workflow" origin="Workflow Superviseur, RH, DG">
                        Apres confirmation, la demande est verrouillee pour validation et les validateurs concernes sont notifies.
                    </x-nc.recommendation>

                    <h2>Verification avant envoi</h2>
                    <p class="leave-step-help">Relisez chaque section. Modifier ramene a l'etape concernee sans effacer les autres reponses.</p>

                    <div class="leave-review-sections">
                        <section>
                            <button type="button" wire:click="editStep(1)">Modifier</button>
                            <h3>Demandeur et type</h3>
                            <p>{{ auth()->user()->employee->name() }} - {{ $this->selectedType?->name }}</p>
                        </section>
                        <section>
                            <button type="button" wire:click="editStep(2)">Modifier</button>
                            <h3>Periode</h3>
                            <p>{{ $form->start_date }} - {{ $form->end_date }} - {{ $this->preview['duration'] }} {{ $this->preview['unit'] }}</p>
                            <p>Reprise estimee : {{ $this->preview['return_date'] ?: '-' }}</p>
                        </section>
                        <section>
                            <button type="button" wire:click="editStep(3)">Modifier</button>
                            <h3>Informations et justificatifs</h3>
                            <p>{{ $form->reason ?: $form->requester_comment ?: 'Aucune information complementaire.' }}</p>
                            <p>{{ count($form->attachments) }} piece(s) jointe(s).</p>
                        </section>
                        <section>
                            <h3>Circuit de validation</h3>
                            <p>Superviseur, puis RH / Admin-Finance, puis DG / Direction.</p>
                        </section>
                    </div>

                    <label class="nc-mini-check leave-confirm-check">
                        <input type="checkbox" wire:model.live="confirmed">
                        Je confirme que les informations sont exactes et que je souhaite soumettre definitivement cette demande.
                    </label>
                    @error('confirmed') <p class="nc-error">{{ $message }}</p> @enderror
                @else
                    @php($created = $this->createdRequest)
                    <div class="leave-confirmation">
                        <x-nc.recommendation kind="internal" title="Demande creee" origin="Nere Tools">
                            La prochaine action appartient au premier validateur du workflow.
                        </x-nc.recommendation>
                        <h2>Demande soumise avec succes</h2>
                        <dl class="leave-detail-grid">
                            <div><dt>Reference</dt><dd>{{ $created?->uuid }}</dd></div>
                            <div><dt>Statut</dt><dd>{{ $created?->statusLabel() }}</dd></div>
                            <div><dt>Prochaine etape</dt><dd>{{ $created?->currentApproval?->step_label ?: 'Workflow termine' }}</dd></div>
                            <div><dt>Type</dt><dd>{{ $created?->leaveType?->name }}</dd></div>
                        </dl>
                        <div class="nc-actions">
                            @if ($created)
                                <a class="nc-button" href="{{ route('leaves.show', $created->uuid) }}">Voir le detail</a>
                            @endif
                            <a class="nc-ghost" href="{{ route('leaves.index') }}">Retour aux conges</a>
                            <button type="button" class="nc-ghost" wire:click="createAnother">Creer une autre demande</button>
                        </div>
                    </div>
                @endif
            </div>
        </main>

        @if ($step < 5)
            <aside class="leave-context-summary" x-bind:class="{ 'is-open': summaryOpen }">
                <button type="button" class="leave-summary-toggle" x-on:click="summaryOpen = !summaryOpen">Resume contextuel</button>
                <div class="leave-context-summary-body">
                    <span>Resume avant envoi</span>
                    <strong>{{ $this->preview['duration'] ?? '-' }}</strong>
                    <small>{{ $this->preview['unit'] ?? 'unite calculee' }}</small>
                    <dl class="leave-summary-facts">
                        <div><dt>Solde avant</dt><dd>{{ $this->preview['balance_before'] !== null ? number_format($this->preview['balance_before'], 2) : '-' }}</dd></div>
                        <div><dt>Solde apres</dt><dd>{{ $this->preview['balance_after'] !== null ? number_format($this->preview['balance_after'], 2) : '-' }}</dd></div>
                        <div><dt>Reprise</dt><dd>{{ $this->preview['return_date'] ?: '-' }}</dd></div>
                        <div><dt>Jours exclus</dt><dd>{{ count($this->preview['non_counted_days']) }}</dd></div>
                    </dl>
                    @if ($draftUuid)
                        <p class="nc-muted">Brouillon temporaire actif jusqu'au {{ $draftExpiresAt }}.</p>
                        <button type="button" class="nc-ghost" wire:click="discardDraft">Supprimer le brouillon</button>
                    @endif
                </div>
            </aside>
        @endif
    </div>

    @if ($step > 1 && $step < 5)
        <footer class="leave-wizard-actions" aria-label="Navigation dans la demande">
            <button class="nc-ghost" type="button" wire:click="previous">Precedent</button>
            @if ($step === 4)
                <button class="nc-button" type="button" wire:click="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>Soumettre la demande</span>
                    <span wire:loading>Soumission en cours...</span>
                </button>
            @else
                <button class="nc-button" type="button" wire:click="next" wire:loading.attr="disabled">Continuer</button>
            @endif
        </footer>
    @endif
</section>
