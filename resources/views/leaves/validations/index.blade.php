@extends('layouts.app', [
    'title' => 'Validations congés - Nere Tools',
    'breadcrumbs' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Congés', 'url' => route('leaves.index')],
        ['label' => 'Validations'],
    ],
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Validation</p>
                <h1 class="nc-title">Demandes en attente</h1>
                <p class="nc-lead">Traitez les demandes soumises dans votre périmètre.</p>
            </div>
        </div>

        @include('leaves.partials.flash')


        @if ($requests->isEmpty())
                <p class="nc-empty">Aucune demande en attente.</p>
            @else
                <div class="nc-table-wrap">
                    <table class="nc-table">
                        <thead>
                            <tr>
                                <th>Demandeur</th>
                                <th>Département</th>
                                <th>Période</th>
                                <th>Jours</th>
                                <th>Statut</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr>
                                    <td>{{ $request->employee?->name() }}</td>
                                    <td>{{ $request->employee?->department?->name ?? '-' }}</td>
                                    <td>{{ $request->start_date->format('d/m/Y') }} au {{ $request->end_date->format('d/m/Y') }}</td>
                                    <td>{{ number_format($request->requested_days, 2) }}</td>
                                    <td><span class="leave-status is-{{ $request->status }}">{{ $request->status }}</span></td>
                                    <td><a href="{{ route('leaves.validations.show', $request->uuid) }}" class="nc-link">Examiner</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $requests->links() }}
            @endif
    </section>
@endsection
