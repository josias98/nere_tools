@extends('layouts.app', [
    'title' => 'Historique - Feuilles de temps',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => route('dashboard')],
        ['label' => 'Feuilles de temps', 'url' => route('timesheets.index')],
        ['label' => 'Historique'],
    ],
])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Historique</p>
                <h1 class="nc-title">Generations passees</h1>
                <p class="nc-lead">Retrouvez ici les lots deja generes. Les fichiers restent stockes de maniere privee et sont servis via l'application.</p>
            </div>
        </div>

        <section class="nc-panel">
            <table class="nc-table">
                <thead>
                    <tr><th>Date</th><th>Periode</th><th>PDF</th><th>Genere par</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach ($generations as $generation)
                        <tr>
                            <td>{{ $generation->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $generation->period_label }}</td>
                            <td>{{ $generation->pdf_count }}</td>
                            <td>{{ $generation->user->name }}</td>
                            <td>
                                <a class="nc-ghost" href="{{ route('timesheets.result', $generation) }}">
                                    <i data-lucide="eye" class="nc-icon" aria-hidden="true"></i>
                                    Voir
                                </a>
                                <a class="nc-ghost" href="{{ route('timesheets.download.zip', $generation) }}">
                                    <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                                    ZIP
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $generations->links() }}
        </section>
    </section>
@endsection
