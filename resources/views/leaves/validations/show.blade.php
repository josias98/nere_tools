@extends('layouts.app', ['title' => 'Validation demande - Nere Tools'])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Validation</p>
                <h1 class="nc-title">Examiner la demande</h1>
                <p class="nc-lead">{{ $leaveRequest->employee?->name() }} - {{ $leaveRequest->employee?->department?->name ?? 'Département non renseigné' }}</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <div class="leave-review-grid">
            <section class="nc-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>{{ $leaveRequest->leaveType?->name ?? 'Congé' }}</h2>
                        <p>{{ $leaveRequest->start_date->format('d/m/Y') }} au {{ $leaveRequest->end_date->format('d/m/Y') }}</p>
                    </div>
                    <span class="leave-status is-{{ $leaveRequest->status }}">{{ $leaveRequest->status }}</span>
                </div>

                <dl class="leave-detail-grid">
                    <div><dt>Jours demandés</dt><dd>{{ number_format($leaveRequest->requested_days, 2) }}</dd></div>
                    <div><dt>Solde disponible</dt><dd>{{ number_format($balance['available_balance'], 2) }}</dd></div>
                    <div><dt>En attente</dt><dd>{{ number_format($balance['pending_days'], 2) }}</dd></div>
                    <div><dt>Solde projeté</dt><dd>{{ number_format($balance['projected_balance'], 2) }}</dd></div>
                    <div><dt>Commentaire</dt><dd>{{ $leaveRequest->requester_comment ?: '-' }}</dd></div>
                </dl>
            </section>

            <aside class="nc-panel leave-decision">
                <h2>Décision</h2>
                @if (in_array($leaveRequest->status, ['submitted', 'under_review'], true))
                    <form method="POST" action="{{ route('leaves.validations.approve', $leaveRequest->uuid) }}">
                        @csrf
                        <label class="nc-field">
                            <span>Commentaire optionnel</span>
                            <textarea name="reviewer_comment" rows="3"></textarea>
                        </label>
                        <button class="nc-button" type="submit">Approuver</button>
                    </form>

                    <form method="POST" action="{{ route('leaves.validations.reject', $leaveRequest->uuid) }}">
                        @csrf
                        <label class="nc-field">
                            <span>Motif du rejet</span>
                            <textarea name="reviewer_comment" rows="3" required></textarea>
                        </label>
                        <button class="nc-ghost danger" type="submit">Rejeter</button>
                    </form>
                @else
                    <p>Décision déjà enregistrée.</p>
                @endif
            </aside>
        </div>
    </section>
@endsection
