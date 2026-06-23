@extends('layouts.app', [
    'title' => 'Demandes de congé - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => route('dashboard')],
        ['label' => 'Conges'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Conges</p>
                <h1 class="nc-title">Demandes de congé</h1>
                <p class="nc-lead">Consultez votre solde, envoyez une demande et suivez son avancement en toute simplicite.</p>
            </div>
        </div>

        @include('leaves.partials.flash')

        <div class="leave-metrics">
            <article class="nc-panel leave-metric">
                <span>Disponible</span>
                <strong>{{ number_format($balance['available_balance'], 2) }}</strong>
                <small>jours</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>En attente</span>
                <strong>{{ number_format($balance['pending_days'], 2) }}</strong>
                <small>jours</small>
            </article>
            <article class="nc-panel leave-metric">
                <span>Projete</span>
                <strong>{{ number_format($balance['projected_balance'], 2) }}</strong>
                <small>jours</small>
            </article>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Vos dernieres demandes</h2>
                    <p>Le calcul se fait en jours calendaires, week-ends inclus.</p>
                </div>
                <div class="nc-actions">
                    @if ($canValidateLeaves)
                        <a href="{{ route('leaves.validations.index') }}" class="nc-ghost">
                            <i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i>
                            Validations
                        </a>
                    @endif
                    <a href="{{ route('leaves.history') }}" class="nc-ghost">
                        <i data-lucide="history" class="nc-icon" aria-hidden="true"></i>
                        Historique
                    </a>
                    <a href="{{ route('leaves.create') }}" class="nc-button">
                        <i data-lucide="plus" class="nc-icon" aria-hidden="true"></i>
                        Nouvelle demande
                    </a>
                </div>
            </div>

            @if ($recentRequests->isEmpty())
                <p class="nc-empty">Vous n'avez encore aucune demande.</p>
            @else
                <div class="nc-table-wrap">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Periode</th>
                                <th>Type</th>
                                <th>Jours</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $request)
                                <tr>
                                    <td>{{ $request->start_date->format('d/m/Y') }} au {{ $request->end_date->format('d/m/Y') }}</td>
                                    <td>{{ $request->leaveType?->name ?? 'Conge' }}</td>
                                    <td>{{ number_format($request->requested_days, 2) }}</td>
                                    <td><span class="leave-status is-{{ $request->status }}">{{ $request->status }}</span></td>
                                    <td>
                                        <a href="{{ route('leaves.show', $request->uuid) }}" class="nc-link">
                                            <i data-lucide="eye" class="nc-icon" aria-hidden="true"></i>
                                            Voir le detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </section>
@endsection
