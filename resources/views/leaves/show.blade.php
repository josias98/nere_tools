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
        </section>
    </section>
@endsection
