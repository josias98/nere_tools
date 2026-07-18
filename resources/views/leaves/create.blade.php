@extends('layouts.app', [
    'title' => 'Nouvelle demande - Néré Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Congés', 'url' => route('leaves.index')],
        ['label' => 'Nouvelle demande'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Nouvelle demande de congés</h1>
                <p class="nc-lead">Choisissez une période. Le nombre de jours calendaires est calculé automatiquement.</p>
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

        <form action="{{ route('leaves.store') }}" method="POST" enctype="multipart/form-data" class="nc-panel leave-form" data-leave-form data-projected-balance="{{ $balance['projected_balance'] }}">
            @csrf
            @if ($renewalOf)<input type="hidden" name="renewal_of_request_id" value="{{ $renewalOf->id }}">@endif

            <div class="leave-split">
                <div class="leave-form-sections">
                    <fieldset class="leave-fieldset">
                        <legend>1. Quel type de congé souhaitez-vous prendre ?</legend>
                        <p class="leave-fieldset-help my-2">Les champs marqués « requis » doivent être renseignés.</p>

                        <label class="nc-field" for="leave_type_id">
                            <span>Type de congé <small>Requis</small></span>
                            <select name="leave_type_id" id="leave_type_id" required aria-required="true" @error('leave_type_id') aria-invalid="true" aria-describedby="leave_type_id_error" @enderror>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" data-slug="{{ $type->slug }}" data-unit="{{ $type->unit->value }}" data-unit-label="{{ $type->unit->label() }}" data-balance-impact="{{ $type->counts_against_balance ? 1 : 0 }}" data-attachment="{{ $type->requires_attachment ? 1 : 0 }}" @selected(old('leave_type_id', $renewalOf?->leave_type_id) == $type->id)>{{ $type->name }}</option>
                            @endforeach
                            </select>
                            @error('leave_type_id') <small id="leave_type_id_error" class="nc-error">{{ $message }}</small> @enderror
                        </label>
                    </fieldset>

                    <fieldset class="leave-fieldset">
                        <legend>2. Choisissez la période</legend>
                        <p class="leave-fieldset-help">Les dates de début et de fin sont incluses dans le calcul.</p>
                        <div class="leave-date-grid">
                            <label class="nc-field" for="leave_start_date">
                                <span>Date de début <small>Requis</small></span>
                                <input type="date" name="start_date" id="leave_start_date" value="{{ old('start_date') }}" required aria-required="true" @error('start_date') aria-invalid="true" aria-describedby="start_date_error" @enderror>
                                @error('start_date') <small id="start_date_error" class="nc-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="nc-field" for="leave_end_date">
                                <span>Date de fin <small>Requis</small></span>
                                <input type="date" name="end_date" id="leave_end_date" value="{{ old('end_date') }}" required aria-required="true" @error('end_date') aria-invalid="true" aria-describedby="end_date_error" @enderror>
                                @error('end_date') <small id="end_date_error" class="nc-error">{{ $message }}</small> @enderror
                            </label>
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

                    <fieldset class="leave-fieldset">
                        <legend>3. Ajoutez un contexte si nécessaire</legend>
                        <div class="leave-date-grid">
                            <label class="nc-field" data-leave-context="reason"><span>Motif <small data-leave-context-required hidden>Requis</small></span><input name="reason" value="{{ old('reason') }}"></label>
                            <label class="nc-field" data-leave-context="relationship"><span>Lien de parenté <small data-leave-context-required hidden>Requis</small></span><input name="relationship" value="{{ old('relationship') }}"></label>
                            <label class="nc-field"><span>Lieu</span><input name="location" value="{{ old('location') }}"></label>
                            <label class="nc-field"><span>Contact pendant l’absence</span><input name="contact" value="{{ old('contact') }}"></label>
                            <label class="nc-field"><span>Incidence sur le traitement</span><input name="salary_impact" value="{{ old('salary_impact') }}"></label>
                            <label class="nc-field"><span>Justificatifs privés <small id="leave_attachment_requirement">Selon le type</small></span><input id="leave_attachments" type="file" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png" aria-describedby="leave_attachment_help @error('attachments') leave_attachments_error @enderror" @error('attachments') aria-invalid="true" @enderror><small id="leave_attachment_help">PDF ou image, 10 Mo maximum.</small>@error('attachments')<small id="leave_attachments_error" class="nc-error">{{ $message }}</small>@enderror</label>
                        </div>
                        <label class="nc-mini-check"><input type="checkbox" name="replacement_needed" value="1" @checked(old('replacement_needed'))> Un remplacement est nécessaire</label>
                        <label class="nc-field" for="requester_comment">
                            <span>Commentaire <small>Facultatif</small></span>
                            <textarea name="requester_comment" id="requester_comment" rows="4" placeholder="Information utile pour les validateurs" @error('requester_comment') aria-invalid="true" aria-describedby="requester_comment_error" @enderror>{{ old('requester_comment') }}</textarea>
                            @error('requester_comment') <small id="requester_comment_error" class="nc-error">{{ $message }}</small> @enderror
                        </label>
                    </fieldset>
                </div>

                <aside class="leave-summary" aria-labelledby="leave_summary_title">
                    <span id="leave_summary_title">Résumé avant envoi</span>
                    <div class="leave-summary-value">
                        <strong id="leave_day_count">-</strong>
                        <small id="leave_unit_label">unité calculée</small>
                    </div>
                    <dl class="leave-summary-facts">
                        <div><dt>Solde projeté actuel</dt><dd>{{ number_format($balance['projected_balance'], 2) }} jours</dd></div>
                        <div><dt>Solde après demande</dt><dd id="leave_remaining_balance">-</dd></div>
                        <div><dt>Reprise estimée</dt><dd id="leave_return_date">-</dd></div>
                        <div><dt>Justificatif</dt><dd id="leave_attachment_hint">Selon le type</dd></div>
                    </dl>
                    <p id="leave_balance_hint" class="nc-muted" role="status" aria-live="polite">Sélectionnez les dates pour vérifier le solde.</p>
                </aside>
            </div>

            <div class="nc-actions leave-form-actions">
                <a href="{{ route('leaves.index') }}" class="nc-ghost">
                    <i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i>
                    Annuler
                </a>
                <button type="submit" class="nc-button">
                    <i data-lucide="send" class="nc-icon" aria-hidden="true"></i>
                    Envoyer la demande
                </button>
            </div>
        </form>
    </section>
@endsection
