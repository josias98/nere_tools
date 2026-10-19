@extends('layouts.app', [
    'title' => 'Historique PDF - Néré Tools',
    'breadcrumbs' => [
        ['label' => 'Accueil', 'url' => route('dashboard')],
        ['label' => 'Outils PDF', 'url' => route('pdfcraft.index')],
        ['label' => 'Historique'],
    ],
])

@section('content')
    <section class="nc-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Outils PDF</p>
                <h1 class="nc-title">Mes documents</h1>
                <p class="nc-lead">Fichiers sources et résultats conservés dans votre espace privé pendant 90 jours.</p>
            </div>
            <a class="nc-button" href="{{ route('pdfcraft.index') }}">Ouvrir les outils PDF</a>
        </div>

        <section class="nc-panel">
            @if ($files->isEmpty())
                <p class="nc-empty">Aucun document enregistré pour le moment.</p>
            @else
                <div class="nc-table-wrap">
                    <table class="nc-table">
                        <thead>
                            <tr><th>Date</th><th>Outil</th><th>Fichier</th><th>Type</th><th>Expiration</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($files as $file)
                                <tr>
                                    <td>{{ $file->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ str_replace('-', ' ', $file->tool_slug) }}</td>
                                    <td>{{ $file->original_name }}</td>
                                    <td>{{ $file->kind === 'source' ? 'Source' : 'Résultat' }}</td>
                                    <td>{{ $file->expires_at->format('d/m/Y') }}</td>
                                    <td><a class="nc-ghost" href="{{ route('pdfcraft.files.download', $file) }}">Télécharger</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $files->links() }}
            @endif
        </section>
    </section>
@endsection
