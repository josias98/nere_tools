@extends('layouts.app', [
    'title' => 'Validation demande - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Congés', 'url' => route('leaves.index')],
        ['label' => 'Validations', 'url' => route('leaves.validations.index')],
        ['label' => 'Détails'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Examiner la demande de congés</h1>
                <p class="nc-lead">{{ $leaveRequest->employee?->name() }} - {{ $leaveRequest->employee?->department?->name ?? 'Département non renseigné' }}</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <div class="leave-review-grid">
            <section class="nc-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>{{ $leaveRequest->leaveType?->name ?? 'Congé' }}</h2>
                        <p>{{ $leaveRequest->periodLabel() }}</p>
                    </div>
                    <span class="leave-status is-{{ $leaveRequest->status }}">{{ $leaveRequest->statusLabel() }}</span>
                </div>

                <dl class="leave-detail-grid">
                    <div><dt>Durée demandée</dt><dd>{{ $leaveRequest->durationLabel() }}</dd></div>
                    <div><dt>Solde disponible</dt><dd>{{ number_format($balance['available_balance'], 2) }}</dd></div>
                    <div><dt>En attente</dt><dd>{{ number_format($balance['pending_days'], 2) }}</dd></div>
                    <div><dt>Solde projeté</dt><dd>{{ number_format($balance['projected_balance'], 2) }}</dd></div>
                    <div><dt>Commentaire</dt><dd>{{ $leaveRequest->requester_comment ?: '-' }}</dd></div>
                    <div><dt>Étape courante</dt><dd>{{ $leaveRequest->currentApproval?->step_label ?? '-' }}</dd></div>
                </dl>

                <div class="leave-timeline">
                    @foreach ($leaveRequest->approvals as $approval)
                        <div class="leave-timeline-item">
                            <span class="leave-status is-{{ $approval->status }}">{{ $approval->statusLabel() }}</span>
                            <strong>{{ $approval->step_order }}. {{ $approval->step_label }}</strong>
                            <span>{{ $approval->validatorUser?->name ?? 'En attente' }}{{ $approval->decided_at ? ' - '.$approval->decided_at->format('d/m/Y H:i') : '' }}</span>
                            @if ($approval->comment)
                                <p>{{ $approval->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <aside class="nc-panel leave-decision">
                <h2>Décision</h2>
                @if (in_array($leaveRequest->status, ['pending_supervisor', 'pending_hr', 'pending_dg'], true))
                    <form method="POST" action="{{ route('leaves.validations.approve', $leaveRequest->uuid) }}">
                        @csrf
                        <label class="nc-field">
                            <span>Commentaire optionnel</span>
                            <textarea name="reviewer_comment" rows="3"></textarea>
                        </label>
                        <button class="nc-button" type="submit">
                            <i data-lucide="check-circle-2" class="nc-icon" aria-hidden="true"></i>
                            Approuver
                        </button>
                    </form>

                    <form method="POST" action="{{ route('leaves.validations.reject', $leaveRequest->uuid) }}">
                        @csrf
                        <label class="nc-field">
                            <span>Motif du rejet</span>
                            <textarea name="reviewer_comment" rows="3" required></textarea>
                        </label>
                        <button class="nc-ghost danger" type="submit">
                            <i data-lucide="x-circle" class="nc-icon" aria-hidden="true"></i>
                            Rejeter
                        </button>
                    </form>
                @else
                    <p>Décision déjà9 enregistrée.</p>
                @endif
            </aside>
        </div>
    </section>
@endsection
