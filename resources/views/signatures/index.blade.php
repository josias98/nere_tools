@extends('layouts.app', [
    'title' => 'Signature PDF · Néré Tools',
    'breadcrumbs' => [['label' => 'Accueil', 'url' => route('dashboard')], ['label' => 'Signature PDF']],
])

@section('content')
    <section class="nc-page sig-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Mes documents</p>
                <h1 class="nc-title">Signature PDF</h1>
                <p class="nc-lead">Relisez votre document, confirmez votre accord et téléchargez le PDF signé.</p>
            </div>
        </div>

        @if (! $certificate['ready'])
            <div class="nc-alert sig-notice" role="status">
                <strong>Signature indisponible pour le moment</strong>
                <p>{{ $certificate['message'] }} Vous pouvez préparer un document en attendant.</p>
            </div>
        @elseif ($certificate['mode'] === 'test')
            <div class="nc-alert sig-notice" role="status">
                <strong>Environnement de test</strong>
                <p>Les PDF seront signés avec un certificat de développement, sans confiance publique.</p>
            </div>
        @endif

        <div class="nc-panel sig-import">
            <div>
                <h2 class="nc-section-title">Préparer un PDF</h2>
                <p class="nc-muted">Un document à signer par vous-même. Son original et sa version signée restent privés.</p>
                <ol class="sig-steps" aria-label="Étapes de signature">
                    <li aria-current="step"><span>1</span> Importer</li>
                    <li><span>2</span> Relire et signer</li>
                    <li><span>3</span> Télécharger</li>
                </ol>
            </div>
            <form action="{{ route('signatures.store') }}" method="POST" enctype="multipart/form-data" data-signature-form>
                @csrf
                <label class="sig-label" for="pdf">Document PDF</label>
                <input class="nc-input sig-file" type="file" name="pdf" id="pdf" accept="application/pdf,.pdf" required aria-describedby="pdf-help @error('pdf') pdf-error @enderror" @error('pdf') aria-invalid="true" @enderror>
                <p class="nc-muted sig-help" id="pdf-help">10 Mo et 100 pages maximum. PDF simple, sans signature existante, mot de passe ni formulaire.</p>
                @error('pdf') <p class="sig-error" id="pdf-error" role="alert">{{ $message }}</p> @enderror
                <button class="nc-button" type="submit" data-pending-label="Import du PDF…">
                    <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i> Relire ce PDF
                </button>
            </form>
        </div>

        <section class="sig-history" aria-labelledby="history-title">
            <h2 class="nc-section-title" id="history-title">Mes documents</h2>
            @if ($documents->isEmpty())
                <div class="nc-panel sig-empty">
                    <i data-lucide="file-signature" class="nc-icon" aria-hidden="true"></i>
                    <h3>Votre premier PDF signé commence ici</h3>
                    <p class="nc-muted">Importez un document pour le relire avant de donner votre accord.</p>
                </div>
            @else
                <div class="sig-table-wrap">
                    <table class="nc-table sig-table">
                        <thead><tr><th scope="col">Document</th><th scope="col">État</th><th scope="col">Date d’import</th><th scope="col"><span class="sr-only">Action</span></th></tr></thead>
                        <tbody>
                            @foreach ($documents as $document)
                                <tr>
                                    <th scope="row"><a href="{{ route('signatures.show', $document->uuid) }}">{{ $document->file_name }}</a><small>{{ $document->page_count }} {{ $document->page_count > 1 ? 'pages' : 'page' }}</small></th>
                                    <td><span class="sig-status {{ $document->signed_at ? 'is-signed' : '' }}">{{ $document->signed_at ? ($document->signature_mode === 'test' ? 'Signé · test' : 'Signé') : 'À signer' }}</span></td>
                                    <td>{{ $document->created_at->format('d/m/Y') }}</td>
                                    <td><a class="nc-ghost" href="{{ route('signatures.show', $document->uuid) }}">{{ $document->signed_at ? 'Consulter' : 'Relire et signer' }}<span class="sr-only"> {{ $document->file_name }}</span><i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></a></td>
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
