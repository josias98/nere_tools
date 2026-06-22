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
                <div class="nc-form-grid">
                    <label class="nc-field">
                        <span>Type de congé</span>
                        <select name="leave_type_id" required>
                            @foreach ($leaveTypes as $type)
                                <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('leave_type_id') <small class="nc-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="nc-field">
                        <span>Date de début</span>
                        <input type="date" name="start_date" id="leave_start_date" value="{{ old('start_date') }}" required>
                        @error('start_date') <small class="nc-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="nc-field">
                        <span>Date de fin</span>
                        <input type="date" name="end_date" id="leave_end_date" value="{{ old('end_date') }}" required>
                        @error('end_date') <small class="nc-error">{{ $message }}</small> @enderror
                    </label>

                    <label class="nc-field">
                        <span>Commentaire</span>
                        <textarea name="requester_comment" rows="5">{{ old('requester_comment') }}</textarea>
                        @error('requester_comment') <small class="nc-error">{{ $message }}</small> @enderror
                    </label>
                </div>

                <aside class="leave-summary">
                    <span>Jours demandés</span>
                    <strong id="leave_day_count">-</strong>
                    <small>Solde projeté actuel : {{ number_format($balance['projected_balance'], 2) }} jours</small>
                    <p id="leave_balance_hint" class="nc-muted">Sélectionnez les dates pour vérifier le solde.</p>
                </aside>
            </div>

            <div class="nc-actions">
                <a href="{{ route('leaves.index') }}" class="nc-ghost">Annuler</a>
                <button type="submit" class="nc-button">Soumettre</button>
            </div>
        </form>
    </section>
@endsection
