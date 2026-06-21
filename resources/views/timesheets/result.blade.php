@extends('layouts.app', ['title' => 'Resultat - Feuilles de temps'])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Generation terminee</p>
                <h1 class="nc-title">{{ $generation->period_label }}</h1>
                <p class="nc-lead">{{ $generation->pdf_count }} PDF pour {{ $generation->employee_count }} collaborateur(s), genere par {{ $generation->user->name }}.</p>
            </div>
        </div>

        <div class="nc-actions">
            <a class="nc-button is-secondary" href="{{ route('timesheets.download.zip', $generation) }}">Telecharger le ZIP</a>
            <a class="nc-ghost" href="{{ route('timesheets.index') }}">Nouvelle generation</a>
        </div>

        <section class="nc-panel" style="margin-top: 20px">
            <h2>Fichiers individuels</h2>
            <table class="nc-table">
                <thead>
                    <tr><th>Collaborateur</th><th>Mois</th><th>Fichier</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($generation->files as $file)
                        <tr>
                            <td>{{ $file->employee->name() }}</td>
                            <td>{{ str_pad((string) $file->month, 2, '0', STR_PAD_LEFT) }}/{{ $file->year }}</td>
                            <td>{{ $file->file_name }}</td>
                            <td><a class="nc-ghost" href="{{ route('timesheets.download.file', $file) }}">Telecharger</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
    </section>
@endsection
