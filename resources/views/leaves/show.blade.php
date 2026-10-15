@extends('layouts.app', [
    'title' => 'Detail demande - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Conges', 'url' => route('leaves.index')],
        ['label' => 'Detail'],
    ],
])

@section('content')
    @php($managedDocument = $leaveRequest->document ?? $leaveRequest->documents->first())
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Détails de votre demande.</h1>
                <p class="nc-lead">{{ $leaveRequest->employee?->name() }} - {{ $leaveRequest->periodLabel() }}</p>
                @if ($leaveRequest->createdBy && $leaveRequest->createdBy->employee?->id !== $leaveRequest->employee_id)
                    <p class="nc-alert is-info">Demande saisie par {{ $leaveRequest->createdBy->name }} pour {{ $leaveRequest->employee?->name() }}</p>
                @endif
            </div>
        </div>

        @include('leaves.partials.flash', ['errorTitle' => 'L’action n’a pas pu être effectuée.'])

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>{{ $leaveRequest->leaveType?->name ?? 'Conge' }}</h2>
                    <p>{{ $leaveRequest->durationLabel() }}</p>
                </div>
                <span class="leave-status is-{{ $leaveRequest->status }}">{{ $leaveRequest->statusLabel() }}</span>
            </div>

            <dl class="leave-detail-grid">
                <div><dt>Soumise le</dt><dd>{{ $leaveRequest->submitted_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt>Debut</dt><dd>{{ $leaveRequest->startLabel() }}</dd></div>
                <div><dt>Fin</dt><dd>{{ $leaveRequest->endLabel() }}</dd></div>
                <div><dt>Décision</dt><dd>{{ $leaveRequest->reviewed_at?->format('d/m/Y H:i') ?? 'En attente' }}</dd></div>
                <div><dt>Commentaire demandeur</dt><dd>{{ $leaveRequest->requester_comment ?: '-' }}</dd></div>
                <div><dt>Commentaire validateur</dt><dd>{{ $leaveRequest->reviewer_comment ?: '-' }}</dd></div>
                <div><dt>Etape Actuelle de la demande</dt><dd>{{ $leaveRequest->currentApproval?->step_label ?? '-' }}</dd></div>
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

            <div class="nc-actions">
                <a href="{{ route('leaves.index') }}" class="nc-ghost">
                    <i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i>
                    Retour
                </a>
                @if ($leaveRequest->status === 'approved' && auth()->user()?->employee?->id === $leaveRequest->employee_id && ($leaveRequest->rule_snapshot['type']['maximum_renewals'] ?? $leaveRequest->leaveType?->maximum_renewals ?? 0) > 0)
                    <a href="{{ route('leaves.create', ['renewal_of' => $leaveRequest->id]) }}" class="nc-button is-secondary">Renouveler</a>
                @endif
            </div>

        </section>

        @if ($managedDocument)
            @include('leaves.partials.certificate', ['document' => $managedDocument])
        @endif
    </section>
@endsection
