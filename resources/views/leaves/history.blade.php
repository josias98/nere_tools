@extends('layouts.app', [
    'title' => 'Historique conges - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Conges', 'url' => route('leaves.index')],
        ['label' => 'Historique'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Conges</p>
                <h1 class="nc-title">Historique</h1>
                <p class="nc-lead">Toutes vos demandes, filtrables par annee et statut.</p>
            </div>
        </div>

        <section class="nc-panel">
            <form class="leave-filter" method="GET">
                <label class="nc-field">
                    <span>Annee</span>
                    <input type="number" name="year" value="{{ request('year') }}" min="2020" max="2100">
                </label>
                <label class="nc-field">
                    <span>Statut</span>
                    <select name="status">
                        <option value="">Tous</option>
                        @foreach (\App\Models\LeaveRequest::statusLabels() as $status => $label)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <button class="nc-ghost" type="submit">
                    <i data-lucide="filter" class="nc-icon" aria-hidden="true"></i>
                    Filtrer
                </button>
            </form>

            @if ($requests->isEmpty())
                <p class="nc-empty">Aucune demande trouvee.</p>
            @else
                <div class="nc-table-wrap">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Periode</th>
                                <th>Jours</th>
                                <th>Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr>
                                    <td>{{ $request->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $request->leaveType?->name ?? 'Conge' }}</td>
                                    <td>{{ $request->start_date->format('d/m/Y') }} au {{ $request->end_date->format('d/m/Y') }}</td>
                                    <td>{{ number_format($request->requested_days, 2) }}</td>
                                    <td><span class="leave-status is-{{ $request->status }}">{{ $request->statusLabel() }}</span></td>
                                    <td>
                                        <a href="{{ route('leaves.show', $request->uuid) }}" class="nc-link">
                                            <i data-lucide="eye" class="nc-icon" aria-hidden="true"></i>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $requests->links() }}
            @endif
        </section>
    </section>
@endsection
