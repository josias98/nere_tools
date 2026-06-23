@extends('layouts.app', [
    'title' => 'Resultat - Feuilles de temps',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => route('dashboard')],
        ['label' => 'Feuilles de temps', 'url' => route('timesheets.index')],
        ['label' => 'Resultat'],
    ],
])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Generation terminee</p>
                <h1 class="nc-title">{{ $generation->period_label }}</h1>
                <p class="nc-lead">{{ $generation->pdf_count }} PDF ont ete generes par {{ $generation->user->name }}. Chaque document contient une page par mois sur la periode selectionnee.</p>
            </div>
        </div>

        <div class="nc-actions">
            <a class="nc-button is-secondary" href="{{ route('timesheets.download.zip', $generation) }}">
                <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                Telecharger le ZIP
            </a>
            <a class="nc-ghost" href="{{ route('timesheets.index') }}">
                <i data-lucide="refresh-cw" class="nc-icon" aria-hidden="true"></i>
                Lancer une nouvelle generation
            </a>
        </div>

        <section class="nc-panel" style="margin-top: 20px">
            <h2>Documents generes</h2>
            <table class="nc-table">
                <thead>
                    <tr><th>Collaborateur</th><th>Periode</th><th>Fichier</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($generation->files as $file)
                        <tr>
                            <td>{{ $file->employee->name() }}</td>
                            <td>{{ $generation->period_start->format('m/Y') }} - {{ $generation->period_end->format('m/Y') }}</td>
                            <td>{{ $file->file_name }}</td>
                            <td>
                                <a class="nc-ghost" href="{{ route('timesheets.download.file', $file) }}">
                                    <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                                    Telecharger
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </section>
@endsection
