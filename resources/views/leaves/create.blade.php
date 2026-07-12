@extends('layouts.app', [
    'title' => 'Nouvelle demande - Nere Tools',
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
                <p class="nc-kicker">Congés</p>
                <h1 class="nc-title">Nouvelle demande</h1>
                <p class="nc-lead">Choisissez une période. Le nombre de jours calendaires est calculé automatiquement.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <form action="{{ route('leaves.store') }}" method="POST" class="nc-panel leave-form" data-leave-form data-projected-balance="{{ $balance['projected_balance'] }}">
            @csrf

            <div class="leave-split">
                <div class="leave-form-sections">
                    <fieldset class="leave-fieldset">
                        <legend>1. Quel congé souhaitez-vous prendre ?</legend>
                        <p class="leave-fieldset-help">Les champs marqués « requis » doivent être renseignés.</p>

                        <label class="nc-field" for="leave_type_id">
                            <span>Type de congé <small>Requis</small></span>
                            <select name="leave_type_id" id="leave_type_id" required aria-required="true" @error('leave_type_id') aria-invalid="true" aria-describedby="leave_type_id_error" @enderror>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
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
                    </fieldset>

                    <fieldset class="leave-fieldset">
                        <legend>3. Ajoutez un contexte si nécessaire</legend>
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
                        <small>jours demandés</small>
                    </div>
                    <dl class="leave-summary-facts">
                        <div><dt>Solde projeté actuel</dt><dd>{{ number_format($balance['projected_balance'], 2) }} jours</dd></div>
                        <div><dt>Solde après demande</dt><dd id="leave_remaining_balance">-</dd></div>
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
