@extends('layouts.app', [
    'title' => 'Signature PDF · Néré Tools',
    'lang' => 'fr',
    'breadcrumbs' => [['label' => 'Accueil', 'url' => route('dashboard')], ['label' => 'Signature PDF']],
])

@section('content')
    <section class="nc-page sig-page" data-signature-wizard data-current-step="1">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Signature PDF</h1>
            </div>
            <a class="nc-ghost" href="#mes-documents" aria-label="Mes documents"><i data-lucide="history" class="nc-icon" aria-hidden="true"></i><span class="sig-page-link-label">Mes documents</span></a>
        </div>
        @include('signatures.partials.notifications')
        @if (! $certificate['ready'])
            <div class="nc-alert sig-notice" role="status">
                <strong>Signature indisponible pour le moment</strong>
                <p>{{ $certificate['message'] }} Vous pouvez préparer un document en attendant.</p>
            </div>
        @elseif ($certificate['mode'] === 'test')
            <div class="nc-alert sig-notice" role="status">
                <i data-lucide="shield-alert" class="nc-icon" aria-hidden="true"></i><span><strong>Environnement de test.</strong> Certificat sans confiance publique.</span>
            </div>
        @endif

        <div class="sig-wizard-shell">
            @include('signatures.partials.wizard-nav', ['step' => 1])
            <div class="sig-wizard-body">
                <div class="sig-progress" role="progressbar" aria-label="Progression de la signature" aria-valuemin="1" aria-valuemax="4" aria-valuenow="1"><span data-signature-progress style="width:25%"></span></div>
                <section class="sig-stage" data-signature-step="1" aria-labelledby="import-title">
                    <h2 id="import-title" tabindex="-1">Ajouter un PDF</h2>
            <form action="{{ route('signatures.store', [], false) }}" method="POST" enctype="multipart/form-data" data-signature-form="import" data-login-url="{{ route('login', [], false) }}">
                @csrf
                <div class="sig-dropzone" data-signature-dropzone>
                    <i data-lucide="file-up" class="sig-upload-icon" aria-hidden="true"></i>
                    <strong>Glissez votre PDF ici</strong>
                    <label class="sig-file-picker" for="pdf"><i data-lucide="folder-open" class="nc-icon" aria-hidden="true"></i><span data-file-picker-label>Choisir un PDF</span><input type="file" name="pdf" id="pdf" accept="application/pdf,.pdf" required aria-describedby="pdf-help pdf-error" @error('pdf') aria-invalid="true" @enderror></label>
                    <small>PDF · 10 Mo maximum · 100 pages</small>
                </div>
                <div class="sig-selected-file" data-signature-selected hidden><span class="sig-selected-icon"><i data-lucide="file-text" class="nc-icon" aria-hidden="true"></i></span><div><strong data-selected-name></strong><small data-selected-size></small></div><button class="nc-ghost sig-icon-button" type="button" data-signature-clear aria-label="Retirer ce fichier"><i data-lucide="x" class="nc-icon" aria-hidden="true"></i></button></div>
                <p class="sig-field-error" id="pdf-error" data-field-error="pdf" @if (! $errors->has('pdf')) hidden @endif>{{ $errors->first('pdf') }}</p>
                <p class="nc-muted sig-help" id="pdf-help">Sans mot de passe, formulaire ni signature existante.</p>
                <p class="sig-form-error" data-form-error @if (! $errors->any()) hidden @endif>{{ $errors->first() }}</p>
                <div class="sig-busy" data-signature-busy hidden role="status"><span class="sig-spinner" aria-hidden="true"></span><span>Import et contrôle du PDF…</span></div>
                <footer class="sig-stage-actions"><button class="nc-button" type="submit" data-pending-label="Import en cours…">Relire le PDF <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></button></footer>
            </form>
                </section>
            </div>
        </div>

        <section class="sig-history" id="mes-documents" aria-labelledby="history-title">
            <h2 class="nc-section-title" id="history-title">Mes documents</h2>
            @if ($documents->isEmpty())
                <div class="nc-panel sig-empty">
                    <p class="nc-muted">Aucun document pour le moment.</p>
                </div>
            @else
                <div class="sig-table-wrap">
                    <table class="nc-table sig-table">
                        <thead><tr><th scope="col">Document</th><th scope="col">État</th><th scope="col">Date d’import</th><th scope="col"><span class="sr-only">Action</span></th></tr></thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <th scope="row"><a href="{{ route('signatures.show', $document->uuid, false) }}">{{ $document->file_name }}</a><small>{{ $document->page_count }} {{ $document->page_count > 1 ? 'pages' : 'page' }}</small></th>
                                    <td><span class="sig-status {{ $document->signed_at ? 'is-signed' : '' }}">{{ $document->signed_at ? ($document->signature_mode === 'test' ? 'Signé · test' : 'Signé') : 'À signer' }}</span></td>
                                    <td>{{ $document->created_at->format('d/m/Y') }}</td>
                                    <td><a class="nc-ghost sig-icon-button" href="{{ route('signatures.show', $document->uuid, false) }}" aria-label="Ouvrir {{ $document->file_name }}"><i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $documents->links() }}
            @endif
        </section>
    </section>
@endsection
