@extends('layouts.app', [
    'title' => 'Detail demande - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Conges', 'url' => route('leaves.index')],
        ['label' => 'Detail'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Conges</p>
                <h1 class="nc-title">Detail de la demande</h1>
                <p class="nc-lead">{{ $leaveRequest->employee?->name() }} - {{ $leaveRequest->periodLabel() }}</p>
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
                <div><dt>Decision</dt><dd>{{ $leaveRequest->reviewed_at?->format('d/m/Y H:i') ?? 'En attente' }}</dd></div>
                <div><dt>Commentaire demandeur</dt><dd>{{ $leaveRequest->requester_comment ?: '-' }}</dd></div>
                <div><dt>Commentaire validateur</dt><dd>{{ $leaveRequest->reviewer_comment ?: '-' }}</dd></div>
                <div><dt>Etape courante</dt><dd>{{ $leaveRequest->currentApproval?->step_label ?? '-' }}</dd></div>
                @if ($leaveRequest->document)
                    <div><dt>Reference document</dt><dd>{{ $leaveRequest->document->document_reference ?: '-' }}</dd></div>
                    <div><dt>Statut documentaire</dt><dd>{{ $leaveRequest->document->status }}</dd></div>
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

            @if ($leaveRequest->document)
                <div class="leave-document-admin">
                    <p>Verification publique : <a class="nc-link" href="{{ $leaveRequest->document->verification_url }}">{{ $leaveRequest->document->verification_url }}</a></p>

                    @if (auth()->user()?->canAccessAdmin())
                        @if ($leaveRequest->document->status === \App\Models\LeaveDocument::STATUS_ACTIVE)
                            <form method="POST" action="{{ route('admin.leaves.documents.revoke', $leaveRequest->document) }}">
                                @csrf
                                <label class="nc-field">
                                    <span>Motif de revocation</span>
                                    <textarea name="reason" rows="2" placeholder="Motif optionnel"></textarea>
                                </label>
                                <button class="nc-ghost danger" type="submit">
                                    <i data-lucide="shield-off" class="nc-icon" aria-hidden="true"></i>
                                    Revoquer le document
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
