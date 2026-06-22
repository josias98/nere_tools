@extends('layouts.app', [
    'title' => 'Demandes de congé - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Congés'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Congés</p>
                <h1 class="nc-title">Demandes de congé</h1>
                <p class="nc-lead">Consultez votre solde, soumettez une demande et suivez son traitement.</p>
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
                    <h2>Dernières demandes</h2>
                    <p>Le décompte se fait en jours calendaires, week-ends inclus.</p>
                </div>
                <div class="nc-actions">
                    <a href="{{ route('leaves.history') }}" class="nc-ghost">Historique</a>
                    <a href="{{ route('leaves.create') }}" class="nc-button">Nouvelle demande</a>
                </div>
            </div>

            @if ($recentRequests->isEmpty())
                <p class="nc-empty">Aucune demande pour le moment.</p>
            @else
                <div class="nc-table-wrap">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Période</th>
                                <th>Type</th>
                                <th>Jours</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentRequests as $request)
                                <tr>
                                    <td>{{ $request->start_date->format('d/m/Y') }} au {{ $request->end_date->format('d/m/Y') }}</td>
                                    <td>{{ $request->leaveType?->name ?? 'Congé' }}</td>
                                    <td>{{ number_format($request->requested_days, 2) }}</td>
                                    <td><span class="leave-status is-{{ $request->status }}">{{ $request->status }}</span></td>
                                    <td><a href="{{ route('leaves.show', $request->uuid) }}">Détail</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </section>
@endsection
