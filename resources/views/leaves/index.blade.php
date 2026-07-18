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
{{--                <p class="nc-kicker">Conges</p>--}}
                <h1 class="nc-title">Demandes de congé</h1>
                <p class="nc-lead">Soumettez et suivez vos demandes de congés auprès de l'administration.</p>
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
                <span>Projeté</span>
                <strong>{{ number_format($balance['projected_balance'], 2) }}</strong>
                <small>jours</small>
            </article>
        </div>

        <section class="nc-panel">
            <div class="nc-panel-heading">
                <div>
                    <h2>Vos dernières demandes</h2>
                    <p>Consultez la liste et l'état de vos dernières demandes de congés.</p>
                </div>
                <div class="nc-actions">
                    <a href="{{ route('leaves.create') }}" class="nc-button">
                        <i data-lucide="plus" class="nc-icon" aria-hidden="true"></i>
                        Nouvelle demande
                    </a>
                    <a href="{{ route('leaves.history') }}" class="nc-ghost">
                        <i data-lucide="history" class="nc-icon" aria-hidden="true"></i>
                        Historique
                    </a>
                    @if ($canValidateLeaves)
                        <a href="{{ route('leaves.validations.index') }}" class="nc-ghost">
                            <i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i>
                            Validations
                        </a>
                    @endif
                </div>
            </div>

            @if ($recentRequests->isEmpty())
                <p class="nc-empty">Vous n'avez encore aucune demande.</p>
            @else
                <div class="nc-table-wrap">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Période</th>
                                <th>Type</th>
                                <th>Jours</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $request)
                                <tr>
                                    <td>{{ $request->periodLabel() }}</td>
                                    <td>{{ $request->leaveType?->name ?? 'Conge' }}</td>
                                    <td>{{ $request->durationLabel() }}</td>
                                    <td><span class="leave-status is-{{ $request->status }}">{{ $request->statusLabel() }}</span></td>
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
