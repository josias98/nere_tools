@extends('layouts.app', [
    'title' => $document->file_name.' · Signature PDF',
    'breadcrumbs' => [['label' => 'Accueil', 'url' => route('dashboard')], ['label' => 'Signature PDF', 'url' => route('signatures.index')], ['label' => $document->signed_at ? 'Document signé' : 'Relire et signer']],
])

@section('content')
    <section class="nc-page sig-page">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Signature PDF</p>
                <h1 class="nc-title sig-filename">{{ $document->file_name }}</h1>
                <p class="nc-muted">{{ $document->page_count }} {{ $document->page_count > 1 ? 'pages' : 'page' }} · {{ number_format($document->original_size / 1024, 0, ',', ' ') }} Ko · Document privé</p>
            </div>
            <a class="nc-ghost" href="{{ route('signatures.index') }}"><i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i> Mes documents</a>
        </div>

        @if (session('success')) <div class="nc-alert success" role="status">{{ session('success') }}</div> @endif
        @if ($errors->any())
            <div class="nc-alert sig-error" role="alert"><strong>La signature n’a pas été effectuée</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if ($integrityError)
            <div class="nc-alert sig-error" role="alert"><strong>Contrôle d’intégrité impossible</strong><p>{{ $integrityError }}</p></div>
        @endif

        <div class="sig-workspace">
            <section class="nc-panel sig-preview" aria-labelledby="preview-title" data-pdf-preview data-pdf-url="{{ route('signatures.file', [$document->uuid, $document->signed_at && ! $integrityError ? 'signe' : 'original']) }}">
                <div class="sig-preview-heading">
                    <h2 class="nc-section-title" id="preview-title">{{ $integrityError ? 'Document original' : ($document->signed_at ? 'Document signé' : 'Document à relire') }}</h2>
                    <a class="nc-ghost" href="{{ route('signatures.file', [$document->uuid, 'original']) }}">Télécharger l’original</a>
                </div>
                <div class="sig-preview-controls" aria-label="Navigation du PDF">
                    <button type="button" class="nc-ghost" data-pdf-previous aria-label="Page précédente" disabled><i data-lucide="chevron-left" class="nc-icon" aria-hidden="true"></i></button>
                    <span data-pdf-page aria-live="polite">Chargement…</span>
                    <button type="button" class="nc-ghost" data-pdf-next aria-label="Page suivante" disabled><i data-lucide="chevron-right" class="nc-icon" aria-hidden="true"></i></button>
                </div>
                <div class="sig-canvas-wrap" data-pdf-surface aria-busy="true"><canvas data-pdf-canvas role="img" aria-label="Aperçu de la page du PDF"></canvas></div>
                <p class="sig-help nc-muted" data-pdf-status role="status">Préparation de l’aperçu…</p>
                <noscript><p>Activez JavaScript pour l’aperçu, ou téléchargez l’original pour le relire.</p></noscript>
            </section>

            <aside class="nc-panel sig-decision" aria-labelledby="decision-title">
                @if ($document->signed_at)
                    <span class="sig-status is-signed"><i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i> {{ $document->signature_mode === 'test' ? 'Signé avec un certificat de test' : 'PDF signé' }}</span>
                    <h2 class="nc-section-title" id="decision-title">Votre signature est enregistrée</h2>
                    @if (! $integrityError)<p class="sig-verified">Intégrité cryptographique vérifiée.</p>@endif
                    @if ($document->signature_mode === 'test')<p class="sig-notice">Ce certificat de développement n’est pas reconnu par une autorité de confiance.</p>@endif
                    <dl class="sig-facts">
                        <div><dt>Accord donné par</dt><dd>{{ $document->signer_name }}<small>{{ $document->signer_email }}</small></dd></div>
                        <div><dt>Date de signature</dt><dd>{{ $document->signed_at->format('d/m/Y à H:i') }} UTC<small>Heure du serveur</small></dd></div>
                        <div><dt>Motif</dt><dd>{{ $document->reason }}</dd></div>
                        <div><dt>Certificat utilisé</dt><dd>{{ $document->certificate_subject }}<small>{{ $document->certificate_issuer }}</small></dd></div>
                    </dl>
                    @if (! $integrityError)
                        <div class="sig-downloads">
                            <a class="nc-button" href="{{ route('signatures.file', [$document->uuid, 'signe']) }}"><i data-lucide="download" class="nc-icon" aria-hidden="true"></i> Télécharger le PDF signé</a>
                            <a class="nc-ghost" href="{{ route('signatures.evidence', $document->uuid) }}">Télécharger les éléments de preuve (JSON)</a>
                        </div>
                    @endif
                    <p class="nc-muted sig-help">Ouvrez le PDF dans un lecteur tel qu’Adobe Acrobat Reader et consultez son panneau de signatures pour contrôler le certificat et sa confiance.</p>
                @else
                    <span class="sig-status">À signer</span>
                    <h2 class="nc-section-title" id="decision-title">Confirmer ma signature</h2>
                    @if (! $certificate['ready'])
                        <div class="sig-notice" role="status"><strong>Certificat indisponible</strong><p>{{ $certificate['message'] }}</p></div>
                    @else
                        @if ($certificate['mode'] === 'test')<p class="sig-notice"><strong>Certificat de test</strong><br>Ce PDF sera signé pour essai, sans confiance publique.</p>@endif
                        <dl class="sig-facts">
                            <div><dt>Votre identité</dt><dd>{{ auth()->user()->name }}<small>{{ auth()->user()->email }}</small></dd></div>
                            <div><dt>Certificat utilisé</dt><dd>{{ $certificate['subject'] }}<small>Valide jusqu’au {{ \Carbon\Carbon::parse($certificate['valid_until'])->format('d/m/Y') }}</small></dd></div>
                        </dl>
                        <p class="nc-muted sig-help">Le certificat applique le sceau de Néré. Votre accord est enregistré avec votre compte de collaborateur.</p>
                        <form action="{{ route('signatures.sign', $document->uuid) }}" method="POST" data-signature-form>
                            @csrf
                            <input type="hidden" name="original_sha256" value="{{ $document->original_sha256 }}">
                            <input type="hidden" name="certificate_sha256" value="{{ $certificate['fingerprint'] }}">
                            <label class="sig-label" for="reason">Motif de la signature</label>
                            <textarea class="nc-input" id="reason" name="reason" rows="2" maxlength="500" required>{{ old('reason', 'Approbation du document') }}</textarea>
                            <label class="sig-consent" for="consent">
                                <input type="checkbox" id="consent" name="consent" value="1" required>
                                <span>J’ai relu ce PDF et j’autorise sa signature avec le certificat Néré en confirmant mon accord au nom de {{ auth()->user()->name }}.</span>
                            </label>
                            <button class="nc-button" type="submit" data-pending-label="Signature du PDF…" @disabled($integrityError)><i data-lucide="file-signature" class="nc-icon" aria-hidden="true"></i> Signer ce PDF</button>
                            <p class="nc-muted sig-help">La version signée sera conservée avec l’original. Une signature cryptographique ne s’efface pas du fichier téléchargé.</p>
                        </form>
                    @endif
                @endif

                <details class="sig-details">
                    <summary>Comprendre cette signature</summary>
                    <p>La signature protège les octets du PDF. Elle figure dans le panneau de signatures du lecteur, sans ajouter de paraphe sur les pages.</p>
                    <p>La confiance dépend du certificat et du lecteur. Cette version n’inclut pas d’horodatage tiers ni de validation de révocation du certificat.</p>
                    <p>Elle ne constitue pas une signature personnelle qualifiée.</p>
                    <dl class="sig-facts">
                        <div><dt>SHA-256 de l’original</dt><dd class="sig-hash">{{ $document->original_sha256 }}</dd></div>
                        @if ($document->signed_at)
                            <div><dt>SHA-256 du PDF signé</dt><dd class="sig-hash">{{ $document->signed_sha256 }}</dd></div>
                            <div><dt>SHA-256 du certificat</dt><dd class="sig-hash">{{ $document->certificate_sha256 }}</dd></div>
                        @elseif ($certificate['ready'])
                            <div><dt>SHA-256 du certificat</dt><dd class="sig-hash">{{ $certificate['fingerprint'] }}</dd></div>
                        @endif
                    </dl>
                </details>
            </aside>
        </div>
    </section>
@endsection
