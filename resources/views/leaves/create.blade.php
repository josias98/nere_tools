@php
    $adminMode = $adminMode ?? false;
    $pageTitle = $adminMode ? 'Créer une demande pour un collaborateur - Néré Tools' : 'Nouvelle demande - Néré Tools';
    $breadcrumbs = $adminMode
        ? [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Administration', 'url' => route('admin.index')],
            ['label' => 'Congés', 'url' => route('admin.leaves.index', ['section' => 'requests'])],
            ['label' => 'Créer une demande'],
        ]
        : [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Congés', 'url' => route('leaves.index')],
            ['label' => 'Nouvelle demande'],
        ];
@endphp
@extends('layouts.app', [
    'title' => $pageTitle,
    'breadcrumbs' => $breadcrumbs,
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">{{ $adminMode ? 'Créer une demande pour un collaborateur' : 'Nouvelle demande de congés' }}</h1>
            </div>
        </div>

        @include('leaves.partials.flash')

        @if ($renewalOf)
            <div class="nc-panel"><strong>Renouvellement</strong><p>Cette demande renouvelle {{ $renewalOf->leaveType?->name }} ({{ $renewalOf->periodLabel() }}).</p></div>
        @endif

        @php
            $leaveRuleData = $leaveTypes->mapWithKeys(fn ($type) => [(string) $type->id => [
                'defaults' => [
                    'unit' => $type->unit->value,
                    'counts_against_balance' => $type->counts_against_balance,
                    'requires_attachment' => $type->requires_attachment,
                ],
                'rules' => $type->rules->where('is_active', true)->map(fn ($rule) => [
                    'effective_from' => $rule->effective_from->toDateString(),
                    'effective_until' => $rule->effective_until?->toDateString(),
                    'configuration' => $rule->configuration,
                ])->values(),
            ]]);
        @endphp
        <script type="application/json" id="leave-rule-data">@json($leaveRuleData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
        @if ($adminMode)
            <script type="application/json" id="leave-employee-balance-data">@json($employeeBalances->map(fn ($employeeBalance) => ['projected_balance' => $employeeBalance['projected_balance']]), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)</script>
        @endif

        <form action="{{ $adminMode ? route('admin.leaves.requests.store') : route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="nc-panel leave-form leave-wizard" data-leave-form data-projected-balance="{{ $balance['projected_balance'] }}" data-has-errors="{{ $errors->any() ? '1' : '0' }}">
            @csrf
            @if ($renewalOf)<input type="hidden" name="renewal_of_request_id" value="{{ $renewalOf->id }}">@endif

            <div class="leave-wizard-progress" aria-label="Progression de la demande"><span data-leave-progress-bar></span></div>
            <ol class="leave-wizard-path" data-leave-path aria-label="Étapes de la demande"></ol>
            <div class="leave-wizard-stage" data-leave-stage>
                <section class="leave-wizard-view leave-wizard-choice" data-leave-step="choice" aria-labelledby="leave_choice_title">
                    <span class="leave-wizard-kicker">Votre absence</span>
                    <h2 id="leave_choice_title">De quoi s’agit-il&nbsp;?</h2>
                    @if ($leaveTypes->isEmpty())
                        <p class="nc-alert is-warning" role="alert">Aucun type de congé n’est disponible pour le moment.</p>
                    @endif
                    <div class="leave-wizard-grid">
                        @if ($leaveTypes->contains('slug', 'annual_leave'))
                            <button type="button" class="leave-wizard-option" data-leave-category="annual"><span><strong>Mes congés</strong><small>Congé administratif</small></span><span aria-hidden="true">→</span></button>
                        @endif
                        @if ($leaveTypes->contains(fn ($type) => in_array($type->slug, ['non_occupational_illness', 'occupational_injury'], true)))
                            <button type="button" class="leave-wizard-option" data-leave-category="health"><span><strong>Ma santé</strong><small>Maladie ou accident du travail</small></span><span aria-hidden="true">→</span></button>
                        @endif
                        @if ($leaveTypes->contains(fn ($type) => in_array($type->slug, ['maternity', 'internal_paternity', 'family_event'], true)))
                            <button type="button" class="leave-wizard-option" data-leave-category="family"><span><strong>Ma famille</strong><small>Naissance ou décès</small></span><span aria-hidden="true">→</span></button>
                        @endif
                        @if ($leaveTypes->contains('slug', 'other_absence'))
                            <button type="button" class="leave-wizard-option" data-leave-category="other"><span><strong>Autre absence</strong><small>Motif à préciser</small></span><span aria-hidden="true">→</span></button>
                        @endif
                        @if ($leaveTypes->whereNotIn('slug', \App\Models\LeaveType::CATALOG_SLUGS)->isNotEmpty())
                            <button type="button" class="leave-wizard-option" data-leave-category="catalog"><span><strong>Autre type</strong><small>Choisir dans la liste</small></span><span aria-hidden="true">→</span></button>
                        @endif
                    </div>
                </section>
                <section class="leave-wizard-view leave-wizard-clarify" data-leave-step="clarify" aria-labelledby="leave_clarify_title">
                    <span class="leave-wizard-kicker">Une précision</span>
                    <h2 id="leave_clarify_title" data-leave-clarify-title>Quelle situation&nbsp;?</h2>
                    <div class="leave-wizard-list">
                        @foreach ([
                            ['slug' => 'non_occupational_illness', 'category' => 'health', 'label' => 'Maladie'],
                            ['slug' => 'occupational_injury', 'category' => 'health', 'label' => 'Accident du travail'],
                            ['slug' => 'maternity', 'category' => 'family', 'label' => 'Maternité'],
                            ['slug' => 'internal_paternity', 'category' => 'family', 'label' => 'Paternité'],
                            ['slug' => 'family_event', 'category' => 'family', 'label' => 'Décès d’un proche'],
                        ] as $choice)
                            @if ($leaveTypes->contains('slug', $choice['slug']))
                                <button type="button" class="leave-wizard-option" data-leave-kind="{{ $choice['slug'] }}" data-leave-branch="{{ $choice['category'] }}"><span><strong>{{ $choice['label'] }}</strong></span><span aria-hidden="true">→</span></button>
                            @endif
                        @endforeach
                    </div>
                    <div class="leave-wizard-actions"><button type="button" class="nc-ghost" data-leave-back="choice">← Revenir aux choix</button></div>
                </section>
                <section class="leave-wizard-view leave-wizard-details" data-leave-step="details" aria-labelledby="leave_details_title">
                    <span class="leave-wizard-kicker">Votre absence</span>
                    <h2 id="leave_details_title">Quand souhaitez-vous vous absenter&nbsp;?</h2>
                    <p class="leave-wizard-picked" data-leave-picked></p>
            <div class="leave-split">
                <div class="leave-form-sections">
                    @if ($adminMode)
                        <fieldset class="leave-fieldset">
                            <legend>Collaborateur bénéficiaire</legend>
                            @if ($employees->isEmpty())
                                <p class="nc-alert is-warning" role="alert">Aucun collaborateur actif et éligible aux congés n’est disponible. Vérifiez les profils RH dans l’administration.</p>
                            @endif
                            <label class="nc-field" for="leave_employee_id">
                                <span>Collaborateur <small>Requis</small></span>
                                <select name="employee_id" id="leave_employee_id" required aria-required="true" @disabled($employees->isEmpty()) @error('employee_id') aria-invalid="true" aria-describedby="leave_employee_id_error" @enderror>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected(old('employee_id', $employees->first()?->id) == $employee->id)>{{ $employee->name() }}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <small id="leave_employee_id_error" class="nc-error">{{ $message }}</small> @enderror
                            </label>
                        </fieldset>
                    @endif
                        <fieldset class="leave-fieldset leave-type-fallback">
                            <legend>Type de congé</legend>

                        <label class="nc-field" for="leave_type_id">
                            <span>Type de congé <small>Requis</small></span>
                            <select name="leave_type_id" id="leave_type_id" required aria-required="true" @error('leave_type_id') aria-invalid="true" aria-describedby="leave_type_id_error" @enderror>
                                <option value="">Choisir un type de congé</option>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" data-slug="{{ $type->slug }}" data-unit="{{ $type->unit->value }}" data-unit-label="{{ $type->unit->label() }}" data-balance-impact="{{ $type->counts_against_balance ? 1 : 0 }}" data-attachment="{{ $type->requires_attachment ? 1 : 0 }}" @selected(old('leave_type_id', $renewalOf?->leave_type_id) == $type->id)>{{ $type->name }}</option>
                            @endforeach
                            </select>
                            @error('leave_type_id') <small id="leave_type_id_error" class="nc-error">{{ $message }}</small> @enderror
                        </label>
                    </fieldset>

                        <fieldset class="leave-fieldset leave-wizard-date-fields">
                            <legend class="leave-wizard-visually-hidden">Période</legend>
                        <div class="leave-date-grid">
                            <div class="leave-date-field">
                                <label class="nc-field" for="leave_start_date">
                                    <span>Du <small>Requis</small></span>
                                    <input type="date" name="start_date" id="leave_start_date" value="{{ old('start_date') }}" required aria-required="true" @error('start_date') aria-invalid="true" aria-describedby="start_date_error" @enderror>
                                    @error('start_date') <small id="start_date_error" class="nc-error">{{ $message }}</small> @enderror
                                </label>
                                <button type="button" class="leave-date-trigger" id="leave_start_trigger" data-leave-date-trigger="start" aria-label="Choisir la date de début" aria-controls="leave_date_calendar" aria-expanded="false" hidden><span data-leave-date-value>Choisir une date</span><i data-lucide="calendar-days" aria-hidden="true"></i></button>
                            </div>

                            <div class="leave-date-field">
                                <label class="nc-field" for="leave_end_date">
                                    <span>Au <small>Requis</small></span>
                                    <input type="date" name="end_date" id="leave_end_date" value="{{ old('end_date') }}" required aria-required="true" @error('end_date') aria-invalid="true" aria-describedby="end_date_error" @enderror>
                                    @error('end_date') <small id="end_date_error" class="nc-error">{{ $message }}</small> @enderror
                                </label>
                                <button type="button" class="leave-date-trigger" id="leave_end_trigger" data-leave-date-trigger="end" aria-label="Choisir la date de fin" aria-controls="leave_date_calendar" aria-expanded="false" hidden><span data-leave-date-value>Choisir une date</span><i data-lucide="calendar-days" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="leave-date-calendar" id="leave_date_calendar" data-leave-date-calendar hidden>
                            <div class="leave-date-calendar-top"><span data-leave-date-prompt>Choisissez le début</span><button type="button" class="leave-date-calendar-close" data-leave-calendar-close aria-label="Fermer le calendrier">×</button></div>
                            <div class="leave-date-calendar-nav"><button type="button" data-leave-calendar-prev aria-label="Mois précédent">←</button><strong data-leave-calendar-month aria-live="polite"></strong><button type="button" data-leave-calendar-next aria-label="Mois suivant">→</button></div>
                            <div class="leave-date-calendar-weekdays" aria-hidden="true"><span>Lu</span><span>Ma</span><span>Me</span><span>Je</span><span>Ve</span><span>Sa</span><span>Di</span></div>
                            <div class="leave-date-calendar-days" data-leave-calendar-days role="group" aria-label="Jours du mois"></div>
                            <div class="leave-date-calendar-footer"><button type="button" data-leave-calendar-today>Aujourd’hui</button><button type="button" data-leave-calendar-clear>Effacer les dates</button></div>
                        </div>
                        <div class="leave-date-grid" data-hour-fields hidden>
                            <label class="nc-field" for="leave_start_time">
                                <span>Heure de début <small>Requis pour une demande horaire</small></span>
                                <input type="time" name="start_time" id="leave_start_time" value="{{ old('start_time') }}" @error('start_time') aria-invalid="true" aria-describedby="start_time_error" @enderror>
                                @error('start_time') <small id="start_time_error" class="nc-error">{{ $message }}</small> @enderror
                            </label>
                            <label class="nc-field" for="leave_end_time">
                                <span>Heure de fin <small>Requis pour une demande horaire</small></span>
                                <input type="time" name="end_time" id="leave_end_time" value="{{ old('end_time') }}" @error('end_time') aria-invalid="true" aria-describedby="end_time_error" @enderror>
                                @error('end_time') <small id="end_time_error" class="nc-error">{{ $message }}</small> @enderror
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="leave-fieldset leave-wizard-context">
                        <legend class="leave-wizard-visually-hidden">Précisions</legend>
                        <label class="nc-field" data-leave-context="reason" for="leave_reason"><span>Motif <small>Requis</small></span><input id="leave_reason" name="reason" value="{{ old('reason') }}" @error('reason') aria-invalid="true" aria-describedby="leave_reason_error" @enderror>@error('reason') <small id="leave_reason_error" class="nc-error">{{ $message }}</small> @enderror</label>
                        <label class="nc-field" data-leave-context="relationship" for="leave_relationship"><span>Lien de parenté <small>Requis</small></span><input id="leave_relationship" name="relationship" value="{{ old('relationship') }}" @error('relationship') aria-invalid="true" aria-describedby="leave_relationship_error" @enderror>@error('relationship') <small id="leave_relationship_error" class="nc-error">{{ $message }}</small> @enderror</label>
                        <div data-leave-upload-slot>
                            <label class="nc-field leave-wizard-upload" for="leave_attachments"><span>Justificatif <small id="leave_attachment_requirement">Selon le type</small></span><input id="leave_attachments" type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png" aria-describedby="leave_attachment_help @error('attachments') leave_attachments_error @enderror" @error('attachments') aria-invalid="true" @enderror><small id="leave_attachment_help">PDF ou image · 10 Mo maximum par fichier.</small>@error('attachments')<small id="leave_attachments_error" class="nc-error">{{ $message }}</small>@enderror</label>
                        </div>
                        <details class="leave-wizard-more" data-leave-more>
                            <summary>Informations complémentaires <span>Facultatif</span></summary>
                            <div class="leave-wizard-more-fields">
                                <div class="leave-date-grid">
                                    <label class="nc-field"><span>Lieu</span><input name="location" value="{{ old('location') }}"></label>
                                    <label class="nc-field"><span>Contact pendant l’absence</span><input name="contact" value="{{ old('contact') }}"></label>
                                    <label class="nc-field"><span>Incidence sur le traitement</span><input name="salary_impact" value="{{ old('salary_impact') }}"></label>
                                </div>
                                <label class="nc-mini-check"><input type="checkbox" name="replacement_needed" value="1" @checked(old('replacement_needed'))> Un remplacement est nécessaire</label>
                                <label class="nc-field" for="requester_comment"><span>Commentaire</span><textarea name="requester_comment" id="requester_comment" rows="3" placeholder="Une précision utile pour les validateurs" @error('requester_comment') aria-invalid="true" aria-describedby="requester_comment_error" @enderror>{{ old('requester_comment') }}</textarea>@error('requester_comment') <small id="requester_comment_error" class="nc-error">{{ $message }}</small> @enderror</label>
                                <div data-leave-optional-upload></div>
                            </div>
                        </details>
                    </fieldset>
                </div>

                <aside class="leave-summary" aria-labelledby="leave_summary_title">
                    <span id="leave_summary_title">Résumé avant envoi</span>
                    <div class="leave-summary-value">
                        <strong id="leave_day_count">-</strong>
                        <small id="leave_unit_label">unité calculée</small>
                    </div>
                    <dl class="leave-summary-facts">
                        <div><dt>Solde projeté actuel</dt><dd id="leave_projected_balance" role="status" aria-live="polite">{{ number_format($balance['projected_balance'], 2) }} jours</dd></div>
                        <div><dt>Solde après demande</dt><dd id="leave_remaining_balance">-</dd></div>
                        <div><dt>Reprise estimée</dt><dd id="leave_return_date">-</dd></div>
                        <div><dt>Justificatif</dt><dd id="leave_attachment_hint">Selon le type</dd></div>
                    </dl>
                    <p id="leave_balance_hint" class="nc-muted" role="status" aria-live="polite">Sélectionnez les dates pour vérifier le solde.</p>
                </aside>
            </div>

                    <p class="leave-wizard-error" data-leave-error role="alert" hidden></p>
                    <div class="leave-wizard-actions"><button type="button" class="nc-ghost" data-leave-back="choice">← Retour</button><button type="button" class="nc-button" data-leave-next="review">Vérifier <span aria-hidden="true">→</span></button></div>
                </section>
                <section class="leave-wizard-view leave-wizard-review" data-leave-step="review" aria-labelledby="leave_review_title">
                    <span class="leave-wizard-kicker">Dernière étape</span>
                    <h2 id="leave_review_title">Vérifiez votre demande</h2>
                    <dl class="leave-wizard-review-list">
                        <div><dt>Type</dt><dd data-review-type>–</dd></div>
                        <div><dt>Période</dt><dd data-review-period>–</dd></div>
                        <div><dt>Durée</dt><dd data-review-duration>–</dd></div>
                        <div data-review-extra hidden><dt data-review-extra-label>Précision</dt><dd data-review-extra-value>–</dd></div>
                        <div data-review-attachment hidden><dt>Justificatif</dt><dd data-review-attachment-value>–</dd></div>
                    </dl>
                    <div class="leave-wizard-actions"><button type="button" class="nc-ghost" data-leave-back="details">← Modifier</button><button type="submit" class="nc-button" @disabled($adminMode && $employees->isEmpty())>{{ $adminMode ? 'Créer la demande' : 'Envoyer ma demande' }} <span aria-hidden="true">→</span></button></div>
                </section>
            </div>

            <div class="nc-actions leave-form-actions leave-wizard-fallback-actions">
                <a href="{{ $adminMode ? route('admin.leaves.index', ['section' => 'requests']) : route('leaves.index') }}" class="nc-ghost">
                    <i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i>
                    Annuler
                </a>
                <button type="submit" class="nc-button" @disabled($adminMode && $employees->isEmpty())>
                    <i data-lucide="send" class="nc-icon" aria-hidden="true"></i>
                    {{ $adminMode ? 'Créer la demande' : 'Envoyer la demande' }}
                </button>
            </div>
        </form>
    </section>
@endsection
