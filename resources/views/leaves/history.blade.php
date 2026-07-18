@extends('layouts.app', [
    'title' => 'Historique congés - Néré Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Congés', 'url' => route('leaves.index')],
        ['label' => 'Historique'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Historique des demandes</h1>
                <p class="nc-lead">Consultez toutes vos demandes présentes et passées.</p>
            </div>
        </div>

        <section class="nc-panel">
            <form class="leave-filter" method="GET">
                <label class="nc-field">
                    <span>Année</span>
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
                                <th>Période</th>
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
                                    <td>{{ $request->periodLabel() }}</td>
                                    <td>{{ $request->durationLabel() }}</td>
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
