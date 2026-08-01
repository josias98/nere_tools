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

        @include('leaves.partials.flash')

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
                @if ($managedDocument)
                    <div><dt>Reference document</dt><dd>{{ $managedDocument->document_reference ?: '-' }}</dd></div>
                    <div><dt>Statut documentaire</dt><dd>{{ $managedDocument->status }}</dd></div>
                @endif
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
                @if ($leaveRequest->status === 'approved' && $leaveRequest->document)
                    <a href="{{ route('leaves.documents.download', $leaveRequest->document) }}" class="nc-button">
                        <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                        Telecharger le PDF
                    </a>
                @endif
                @if ($leaveRequest->status === 'approved' && auth()->user()?->employee?->id === $leaveRequest->employee_id && ($leaveRequest->rule_snapshot['type']['maximum_renewals'] ?? $leaveRequest->leaveType?->maximum_renewals ?? 0) > 0)
                    <a href="{{ route('leaves.create', ['renewal_of' => $leaveRequest->id]) }}" class="nc-button is-secondary">Renouveler</a>
                @endif
            </div>

            @if ($managedDocument)
                <div class="leave-document-admin">
                    <p>Verification publique : <a class="nc-link" href="{{ $managedDocument->verification_url }}">{{ $managedDocument->verification_url }}</a></p>

                    @if (auth()->user()?->canAccessAdmin())
                        @if ($managedDocument->status === \App\Models\LeaveDocument::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.leaves.documents.revoke', $managedDocument) }}">
                                @csrf
                                <label class="nc-field">
                                    <span>Motif de revocation</span>
                                    <textarea name="reason" rows="2" placeholder="Expliquez la révocation" required></textarea>
                                </label>
                                <button class="nc-ghost danger" type="submit">
                                    <i data-lucide="shield-off" class="nc-icon" aria-hidden="true"></i>
                                    Revoquer le document
                                </button>
                            </form>
                        @elseif ($managedDocument->status === \App\Models\LeaveDocument::STATUS_REVOKED)
                            <p>Motif de révocation : {{ $managedDocument->status_reason }}</p>
                            <form method="POST" action="{{ route('admin.leaves.documents.restore', $managedDocument) }}">
                                @csrf
                                <button class="nc-button is-secondary" type="submit">
                                    <i data-lucide="undo-2" class="nc-icon" aria-hidden="true"></i>
                                    Annuler la révocation
                                </button>
                            </form>
                        @endif

                        @if ($leaveRequest->status === 'approved')
                            <form method="POST" action="{{ route('admin.leaves.documents.regenerate', $leaveRequest->uuid) }}">
                                @csrf
                                <label class="nc-field">
                                    <span>Motif de regeneration</span>
                                    <textarea name="reason" rows="2" placeholder="Expliquez la regeneration si necessaire"></textarea>
                                </label>
                                <button class="nc-button is-secondary" type="submit">
                                    <i data-lucide="refresh-cw" class="nc-icon" aria-hidden="true"></i>
                                    Regenerer le document
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            @endif
        </section>
    </section>
@endsection
