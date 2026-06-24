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
                <p class="nc-lead">{{ $leaveRequest->employee?->name() }} - {{ $leaveRequest->start_date->format('d/m/Y') }} au {{ $leaveRequest->end_date->format('d/m/Y') }}</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>{{ $leaveRequest->leaveType?->name ?? 'Conge' }}</h2>
                    <p>{{ number_format($leaveRequest->requested_days, 2) }} jours calendaires</p>
                </div>
                <span class="leave-status is-{{ $leaveRequest->status }}">{{ $leaveRequest->status }}</span>
            </div>

            <dl class="leave-detail-grid">
                <div><dt>Soumise le</dt><dd>{{ $leaveRequest->submitted_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                <div><dt>Debut</dt><dd>{{ $leaveRequest->start_date->format('d/m/Y') }}</dd></div>
                <div><dt>Fin</dt><dd>{{ $leaveRequest->end_date->format('d/m/Y') }}</dd></div>
                <div><dt>Decision</dt><dd>{{ $leaveRequest->reviewed_at?->format('d/m/Y H:i') ?? 'En attente' }}</dd></div>
                <div><dt>Commentaire demandeur</dt><dd>{{ $leaveRequest->requester_comment ?: '-' }}</dd></div>
                <div><dt>Commentaire validateur</dt><dd>{{ $leaveRequest->reviewer_comment ?: '-' }}</dd></div>
                @if ($leaveRequest->document)
                    <div><dt>Reference document</dt><dd>{{ $leaveRequest->document->document_reference ?: '-' }}</dd></div>
                    <div><dt>Statut documentaire</dt><dd>{{ $leaveRequest->document->status }}</dd></div>
                @endif
            </dl>

            <div class="nc-actions">
                <a href="{{ route('leaves.index') }}" class="nc-ghost">
                    <i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i>
                    Retour
                </a>
                @if ($leaveRequest->document)
                    <a href="{{ route('leaves.documents.download', $leaveRequest->document) }}" class="nc-button">
                        <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                        Telecharger le PDF
                    </a>
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
