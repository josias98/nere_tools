@extends('layouts.app', ['title' => 'Historique - Feuilles de temps'])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Historique</p>
                <h1 class="nc-title">Generations</h1>
                <p class="nc-lead">Les fichiers restent stockes en prive et servis par Laravel.</p>
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
                                <a class="nc-ghost" href="{{ route('timesheets.result', $generation) }}">Voir</a>
                                <a class="nc-ghost" href="{{ route('timesheets.download.zip', $generation) }}">ZIP</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $generations->links() }}
        </section>
    </section>
@endsection
