@extends('layouts.app', [
    'title' => $document->file_name.' · Signature PDF',
    'lang' => 'fr',
    'breadcrumbs' => [['label' => 'Accueil', 'url' => route('dashboard')], ['label' => 'Signature PDF', 'url' => route('signatures.index')], ['label' => $document->signed_at ? 'Document signé' : 'Signer un document']],
])

@section('content')
    @php($initialStep = $document->signed_at ? 4 : ($errors->any() ? 3 : 2))
    <section class="nc-page sig-page" data-signature-wizard data-current-step="{{ $initialStep }}">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Néré Tools · Signature PDF</p>
                <h1 class="nc-title">{{ $document->signed_at ? 'Votre document signé' : 'Signer un document' }}</h1>
                <p class="nc-lead">{{ $document->signed_at ? 'Retrouvez votre PDF et les informations de sa signature.' : 'Relisez le PDF, puis confirmez personnellement votre accord.' }}</p>
            </div>
            <a class="nc-ghost" href="{{ route('signatures.index') }}"><i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i> Mes documents</a>
        </div>
        @include('signatures.partials.notifications')

        @if ($integrityError)
            <div class="sig-system-error" role="alert"><i data-lucide="shield-alert" class="nc-icon" aria-hidden="true"></i><div><strong>Ce document demande une vérification</strong><p>{{ $integrityError }}</p><a href="{{ route('signatures.index') }}">Revenir à mes documents</a></div></div>
        @endif

        <div class="sig-wizard-shell">
            @include('signatures.partials.wizard-nav', ['step' => $initialStep])
            <div class="sig-wizard-body">
                <div class="sig-progress" role="progressbar" aria-label="Progression de la signature" aria-valuemin="1" aria-valuemax="4" aria-valuenow="{{ $initialStep }}"><span data-signature-progress style="width:{{ $initialStep * 25 }}%"></span></div>
                <div class="sig-document-strip"><span class="sig-selected-icon"><i data-lucide="file-text" class="nc-icon" aria-hidden="true"></i></span><div><strong>{{ $document->file_name }}</strong><small>{{ $document->page_count }} {{ $document->page_count > 1 ? 'pages' : 'page' }} · {{ number_format($document->original_size / 1024, 0, ',', ' ') }} Ko · Document privé</small></div></div>

                @if (! $document->signed_at)
                    <section class="sig-stage" data-signature-step="2" aria-labelledby="review-title">
                        <p class="sig-stage-kicker" data-signature-counter>Étape 2 sur 4</p>
                        <h2 id="review-title" tabindex="-1">Tout est correct dans ce PDF ?</h2>
                        <p class="sig-stage-intro">Vérifiez le contenu et les pages. Vous donnerez votre accord à l’étape suivante.</p>
                        <div class="sig-reader" data-pdf-preview data-pdf-url="{{ route('signatures.file', [$document->uuid, 'original']) }}">
                            <div class="sig-preview-controls" aria-label="Navigation du PDF">
                                <button type="button" class="nc-ghost sig-icon-button" data-pdf-previous aria-label="Page précédente" disabled><i data-lucide="chevron-left" class="nc-icon" aria-hidden="true"></i></button>
                                <span data-pdf-page aria-live="polite">Chargement…</span>
                                <button type="button" class="nc-ghost sig-icon-button" data-pdf-next aria-label="Page suivante" disabled><i data-lucide="chevron-right" class="nc-icon" aria-hidden="true"></i></button>
                                <a class="sig-reader-download" href="{{ route('signatures.file', [$document->uuid, 'original']) }}"><i data-lucide="download" class="nc-icon" aria-hidden="true"></i> L’original</a>
                            </div>
                            <div class="sig-canvas-wrap" data-pdf-surface aria-busy="true"><canvas data-pdf-canvas role="img" aria-label="Aperçu de la page du PDF"></canvas></div>
                            <p class="sig-help nc-muted" data-pdf-status role="status">Préparation de l’aperçu…</p>
                            <noscript><p>Téléchargez l’original pour le relire dans votre lecteur PDF.</p></noscript>
                        </div>
                        <footer class="sig-stage-actions">
                            <a class="nc-ghost" href="{{ route('signatures.index') }}">Changer de PDF</a>
                            <button class="nc-button" type="button" data-signature-next hidden @disabled($integrityError)>J’ai relu le PDF <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></button>
                        </footer>
                    </section>

                    <section class="sig-stage" data-signature-step="3" aria-labelledby="decision-title">
                        <p class="sig-stage-kicker" data-signature-counter>Étape 3 sur 4</p>
                        <h2 id="decision-title" tabindex="-1">Confirmer ma signature</h2>
                        <p class="sig-stage-intro">Votre accord sera associé à ce document précis et au compte ci-dessous.</p>
                        @if (! $certificate['ready'])
                            <div class="sig-system-error" role="status"><i data-lucide="key-round" class="nc-icon" aria-hidden="true"></i><div><strong>Certificat indisponible</strong><p>{{ $certificate['message'] }} Votre PDF reste dans vos documents.</p><button class="nc-ghost" type="button" data-signature-reload>Vérifier à nouveau</button></div></div>
                        @else
                            @if ($certificate['mode'] === 'test')<div class="sig-notice"><strong>Certificat de test</strong><p>Ce PDF sera signé pour essai, sans confiance publique.</p></div>@endif
                            <div class="sig-signatory"><span class="sig-person-mark" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><small>Votre identité</small><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div><i data-lucide="user-check" class="nc-icon" aria-hidden="true"></i></div>
                            <form action="{{ route('signatures.sign', $document->uuid) }}" method="POST" data-signature-form="sign" data-login-url="{{ route('login') }}">
                                @csrf
                                <input type="hidden" name="original_sha256" value="{{ $document->original_sha256 }}">
                                <input type="hidden" name="certificate_sha256" value="{{ $certificate['fingerprint'] }}">
                                <label class="sig-label" for="reason">Motif de votre signature</label>
                                <div class="sig-reason-options" role="group" aria-label="Motifs courants">
                                    <button type="button" data-signature-reason="Bon pour accord" aria-pressed="false">Bon pour accord</button>
                                    <button type="button" data-signature-reason="Prise de connaissance" aria-pressed="false">Prise de connaissance</button>
                                </div>
                                <textarea class="nc-input" id="reason" name="reason" rows="2" maxlength="500" required aria-describedby="reason-help reason-error" @error('reason') aria-invalid="true" @enderror>{{ old('reason', 'Approbation du document') }}</textarea>
                                <small class="nc-muted" id="reason-help">Choisissez un motif courant ou écrivez le vôtre.</small>
                                <p class="sig-field-error" id="reason-error" data-field-error="reason" @if (! $errors->has('reason')) hidden @endif>{{ $errors->first('reason') }}</p>
                                <label class="sig-consent" for="consent"><input type="checkbox" id="consent" name="consent" value="1" required aria-describedby="consent-error" @error('consent') aria-invalid="true" @enderror><span>J’ai relu ce PDF et j’autorise sa signature avec le certificat Néré en confirmant mon accord au nom de {{ auth()->user()->name }}.</span></label>
                                <p class="sig-field-error" id="consent-error" data-field-error="consent" @if (! $errors->has('consent')) hidden @endif>{{ $errors->first('consent') }}</p>
                                <p class="sig-form-error" data-form-error @if (! $errors->any()) hidden @endif>{{ $errors->first() }}</p>
                                <div class="sig-busy" data-signature-busy hidden role="status"><span class="sig-spinner" aria-hidden="true"></span><span>Signature et vérification du PDF… Gardez cette page ouverte.</span></div>
                                <footer class="sig-stage-actions"><button class="nc-ghost" type="button" data-signature-back hidden>Relire le PDF</button><button class="nc-button" type="submit" data-pending-label="Signature en cours…" @disabled($integrityError)><i data-lucide="file-signature" class="nc-icon" aria-hidden="true"></i> Signer ce PDF</button></footer>
                            </form>
                            <details class="sig-certificate-details"><summary><i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i> Certificat utilisé</summary><p>Le certificat applique le sceau de Néré. Votre accord est enregistré avec votre compte.</p><dl class="sig-facts"><div><dt>Titulaire</dt><dd>{{ $certificate['subject'] }}</dd></div><div><dt>Émetteur</dt><dd>{{ $certificate['issuer'] }}</dd></div><div><dt>Valide jusqu’au</dt><dd>{{ \Carbon\Carbon::parse($certificate['valid_until'])->format('d/m/Y') }}</dd></div></dl></details>
                        @endif
                        @if (! $certificate['ready'])<footer class="sig-stage-actions"><button class="nc-ghost" type="button" data-signature-back hidden>Relire le PDF</button></footer>@endif
                    </section>
                @else
                    <section class="sig-stage sig-delivery" data-signature-step="4" aria-labelledby="delivery-title">
                        <p class="sig-stage-kicker">Étape 4 sur 4</p>
                        @if (! $integrityError)
                            <span class="sig-delivery-mark" aria-hidden="true"><i data-lucide="check" class="nc-icon"></i></span>
                            <h2 id="delivery-title" tabindex="-1">Votre PDF est prêt.</h2>
                            <p class="sig-stage-intro">Votre signature est enregistrée. Téléchargez le document pour le conserver ou le partager.</p>
                            <p class="sig-verified"><i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i> Intégrité cryptographique vérifiée.</p>
                        @else
                            <h2 id="delivery-title" tabindex="-1">Le PDF doit être vérifié.</h2>
                            <p class="sig-stage-intro">Le téléchargement est suspendu pour protéger votre document. Contactez un administrateur.</p>
                        @endif
                        @if ($document->signature_mode === 'test')<div class="sig-notice"><strong>Signé avec un certificat de test</strong><p>Ce certificat de développement n’est pas reconnu par une autorité de confiance.</p></div>@endif
                        <dl class="sig-delivery-facts"><div><dt>Accord donné par</dt><dd>{{ $document->signer_name }}<small>{{ $document->signer_email }}</small></dd></div><div><dt>Signé le</dt><dd>{{ $document->signed_at->format('d/m/Y à H:i') }} UTC<small>Heure du serveur</small></dd></div><div><dt>Motif</dt><dd>{{ $document->reason }}</dd></div><div><dt>Certificat utilisé</dt><dd>{{ $document->certificate_subject }}</dd></div></dl>
                        @if (! $integrityError)<div class="sig-downloads"><a class="nc-button" href="{{ route('signatures.file', [$document->uuid, 'signe']) }}"><i data-lucide="download" class="nc-icon" aria-hidden="true"></i> Télécharger le PDF signé</a><a class="sig-text-link" href="{{ route('signatures.evidence', $document->uuid) }}">Les éléments de preuve (JSON)</a></div>@endif
                        <p class="nc-muted sig-help">Dans votre lecteur PDF, ouvrez le panneau Signatures pour consulter le certificat et sa confiance.</p>
                        <footer class="sig-stage-actions"><a class="nc-ghost" href="{{ route('signatures.file', [$document->uuid, 'original']) }}">L’original</a><a class="nc-ghost" href="{{ route('signatures.index') }}">Signer un autre PDF <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></a></footer>
                    </section>
                @endif
            </div>
        </div>
        <details class="sig-details"><summary>Comprendre cette signature</summary><p>La signature protège les octets du PDF. Elle figure dans le panneau de signatures du lecteur, sans ajouter de paraphe sur les pages. Elle ne constitue pas une signature personnelle qualifiée et n’inclut pas d’horodatage tiers.</p><dl class="sig-facts"><div><dt>SHA-256 de l’original</dt><dd class="sig-hash">{{ $document->original_sha256 }}</dd></div>@if ($document->signed_at)<div><dt>SHA-256 du PDF signé</dt><dd class="sig-hash">{{ $document->signed_sha256 }}</dd></div><div><dt>SHA-256 du certificat</dt><dd class="sig-hash">{{ $document->certificate_sha256 }}</dd></div>@elseif ($certificate['ready'])<div><dt>SHA-256 du certificat</dt><dd class="sig-hash">{{ $certificate['fingerprint'] }}</dd></div>@endif</dl></details>
    </section>
@endsection
