@extends('layouts.app', [
    'title' => $document->file_name.' · Signature PDF',
    'lang' => 'fr',
    'breadcrumbs' => [['label' => 'Accueil', 'url' => route('dashboard')], ['label' => 'Signature PDF', 'url' => route('signatures.index', [], false)], ['label' => $document->signed_at ? 'Document signé' : 'Signer un document']],
])

@section('content')
    @php($initialStep = $document->signed_at ? 4 : ($errors->any() ? 3 : 2))
    <section class="nc-page sig-page" data-signature-wizard data-current-step="{{ $initialStep }}">
        <div class="nc-title-row">
            <div>
                <h1 class="nc-title">Signature PDF</h1>
            </div>
            <a class="nc-ghost" href="{{ route('signatures.index', [], false) }}" aria-label="Mes documents"><i data-lucide="arrow-left" class="nc-icon" aria-hidden="true"></i><span class="sig-page-link-label">Mes documents</span></a>
        </div>
        @include('signatures.partials.notifications')

        @if ($integrityError)
            <div class="sig-system-error" role="alert"><i data-lucide="shield-alert" class="nc-icon" aria-hidden="true"></i><div><strong>Ce document demande une vérification</strong><p>{{ $integrityError }}</p><a href="{{ route('signatures.index', [], false) }}">Revenir à mes documents</a></div></div>
        @endif

        <div class="sig-wizard-shell">
            @include('signatures.partials.wizard-nav', ['step' => $initialStep])
            <div class="sig-wizard-body">
                <div class="sig-progress" role="progressbar" aria-label="Progression de la signature" aria-valuemin="1" aria-valuemax="4" aria-valuenow="{{ $initialStep }}"><span data-signature-progress style="width:{{ $initialStep * 25 }}%"></span></div>
                <div class="sig-document-strip"><span class="sig-selected-icon"><i data-lucide="file-text" class="nc-icon" aria-hidden="true"></i></span><div><strong>{{ $document->file_name }}</strong><small>{{ $document->page_count }} {{ $document->page_count > 1 ? 'pages' : 'page' }} · {{ number_format($document->original_size / 1024, 0, ',', ' ') }} Ko</small></div><i data-lucide="lock-keyhole" class="nc-icon" role="img" aria-label="Document privé"></i></div>

                @if (! $document->signed_at)
                    <section class="sig-stage" data-signature-step="2" aria-labelledby="review-title">
                        <h2 id="review-title" tabindex="-1">Relire le PDF</h2>
                        <div class="sig-reader" data-pdf-preview data-pdf-url="{{ route('signatures.file', [$document->uuid, 'original'], false) }}">
                            <div class="sig-preview-controls" aria-label="Navigation du PDF">
                                <button type="button" class="nc-ghost sig-icon-button" data-pdf-previous aria-label="Page précédente" disabled><i data-lucide="chevron-left" class="nc-icon" aria-hidden="true"></i></button>
                                <span data-pdf-page aria-live="polite">Chargement…</span>
                                <button type="button" class="nc-ghost sig-icon-button" data-pdf-next aria-label="Page suivante" disabled><i data-lucide="chevron-right" class="nc-icon" aria-hidden="true"></i></button>
                                <a class="sig-reader-download" href="{{ route('signatures.file', [$document->uuid, 'original'], false) }}"><i data-lucide="download" class="nc-icon" aria-hidden="true"></i> L’original</a>
                            </div>
                            <div class="sig-canvas-wrap" data-pdf-surface aria-busy="true"><canvas data-pdf-canvas role="img" aria-label="Aperçu de la page du PDF"></canvas></div>
                            <p class="sig-help nc-muted" data-pdf-status role="status">Préparation de l’aperçu…</p>
                            <noscript><p>Téléchargez l’original pour le relire dans votre lecteur PDF.</p></noscript>
                        </div>
                        <footer class="sig-stage-actions">
                            <a class="nc-ghost" href="{{ route('signatures.index', [], false) }}">Changer de PDF</a>
                            <button class="nc-button" type="button" data-signature-next hidden @disabled($integrityError)>J’ai relu le PDF <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></button>
                        </footer>
                    </section>

                    <section class="sig-stage" data-signature-step="3" aria-labelledby="decision-title">
                        <h2 id="decision-title" tabindex="-1">Confirmer ma signature</h2>
                        @if (! $certificate['ready'])
                            <div class="sig-system-error" role="status"><i data-lucide="key-round" class="nc-icon" aria-hidden="true"></i><div><strong>Certificat indisponible</strong><p>{{ $certificate['message'] }} Votre PDF reste dans vos documents.</p><button class="nc-ghost" type="button" data-signature-reload>Vérifier à nouveau</button></div></div>
                        @else
                            @if ($certificate['mode'] === 'test')<div class="sig-notice"><i data-lucide="shield-alert" class="nc-icon" aria-hidden="true"></i><span><strong>Certificat de test.</strong> Sans confiance publique.</span></div>@endif
                            <div class="sig-signatory"><span class="sig-person-mark" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><div><strong>{{ auth()->user()->name }}</strong><span>{{ auth()->user()->email }}</span></div><i data-lucide="user-check" class="nc-icon" aria-hidden="true"></i></div>
                            <form action="{{ route('signatures.sign', $document->uuid, false) }}" method="POST" data-signature-form="sign" data-login-url="{{ route('login', [], false) }}">
                                @csrf
                                <input type="hidden" name="original_sha256" value="{{ $document->original_sha256 }}">
                                <input type="hidden" name="certificate_sha256" value="{{ $certificate['fingerprint'] }}">
                                <label class="sig-label" for="reason">Motif</label>
                                <div class="sig-reason-options" role="group" aria-label="Motifs courants">
                                    <button type="button" data-signature-reason="Bon pour accord" aria-pressed="false">Bon pour accord</button>
                                    <button type="button" data-signature-reason="Prise de connaissance" aria-pressed="false">Prise de connaissance</button>
                                </div>
                                <textarea class="nc-input" id="reason" name="reason" rows="2" maxlength="500" required aria-describedby="reason-error" @error('reason') aria-invalid="true" @enderror>{{ old('reason', 'Bon pour accord') }}</textarea>
                                <p class="sig-field-error" id="reason-error" data-field-error="reason" @if (! $errors->has('reason')) hidden @endif>{{ $errors->first('reason') }}</p>
                                <label class="sig-consent" for="consent"><input type="checkbox" id="consent" name="consent" value="1" required aria-describedby="consent-error" @error('consent') aria-invalid="true" @enderror><span>J’ai relu ce document et j’autorise sa signature avec le certificat Néré au nom de {{ auth()->user()->name }}.</span></label>
                                <p class="sig-field-error" id="consent-error" data-field-error="consent" @if (! $errors->has('consent')) hidden @endif>{{ $errors->first('consent') }}</p>
                                <p class="sig-form-error" data-form-error @if (! $errors->any()) hidden @endif>{{ $errors->first() }}</p>
                                <div class="sig-busy" data-signature-busy hidden role="status"><span class="sig-spinner" aria-hidden="true"></span><span>Signature du PDF en cours…</span></div>
                                <footer class="sig-stage-actions"><button class="nc-ghost" type="button" data-signature-back hidden>Relire le PDF</button><button class="nc-button" type="submit" data-pending-label="Signature en cours…" @disabled($integrityError)><i data-lucide="file-signature" class="nc-icon" aria-hidden="true"></i> Signer ce PDF</button></footer>
                            </form>
                            <details class="sig-certificate-details"><summary><i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i> Certificat utilisé</summary><p>Le certificat applique le sceau de Néré. Votre accord est enregistré avec votre compte.</p><dl class="sig-facts"><div><dt>Titulaire</dt><dd>{{ $certificate['subject'] }}</dd></div><div><dt>Émetteur</dt><dd>{{ $certificate['issuer'] }}</dd></div><div><dt>Valide jusqu’au</dt><dd>{{ \Carbon\Carbon::parse($certificate['valid_until'])->format('d/m/Y') }}</dd></div></dl></details>
                        @endif
                        @if (! $certificate['ready'])<footer class="sig-stage-actions"><button class="nc-ghost" type="button" data-signature-back hidden>Relire le PDF</button></footer>@endif
                    </section>
                @else
                    <section class="sig-stage sig-delivery" data-signature-step="4" aria-labelledby="delivery-title">
                        @if (! $integrityError)
                            <span class="sig-delivery-mark" aria-hidden="true"><i data-lucide="check" class="nc-icon"></i></span>
                            <h2 id="delivery-title" tabindex="-1">PDF signé</h2>
                            <p class="sig-verified"><i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i> Intégrité cryptographique vérifiée.</p>
                        @else
                            <h2 id="delivery-title" tabindex="-1">Le PDF doit être vérifié.</h2>
                            <p class="sig-stage-intro">Le téléchargement est suspendu pour protéger votre document. Contactez un administrateur.</p>
                        @endif
                        @if ($document->signature_mode === 'test')<div class="sig-notice"><i data-lucide="shield-alert" class="nc-icon" aria-hidden="true"></i><span><strong>Certificat de test.</strong> Sans confiance publique.</span></div>@endif
                        @if (! $integrityError)<div class="sig-downloads"><a class="nc-button" href="{{ route('signatures.file', [$document->uuid, 'signe'], false) }}"><i data-lucide="download" class="nc-icon" aria-hidden="true"></i> Télécharger le PDF signé</a><a class="sig-text-link" href="{{ route('signatures.evidence', $document->uuid, false) }}">Les éléments de preuve (JSON)</a></div>@endif
                        <dl class="sig-delivery-facts"><div><dt>Signataire</dt><dd>{{ $document->signer_name }}<small>{{ $document->signer_email }}</small></dd></div><div><dt>Signé le</dt><dd>{{ $document->signed_at->format('d/m/Y à H:i') }} UTC</dd></div><div><dt>Motif</dt><dd>{{ $document->reason }}</dd></div><div><dt>Certificat</dt><dd>{{ $document->certificate_subject }}</dd></div></dl>
                        <footer class="sig-stage-actions"><a class="nc-ghost" href="{{ route('signatures.file', [$document->uuid, 'original'], false) }}">L’original</a><a class="nc-ghost" href="{{ route('signatures.index', [], false) }}">Signer un autre PDF <i data-lucide="arrow-right" class="nc-icon" aria-hidden="true"></i></a></footer>
                    </section>
                @endif
            </div>
        </div>
        <details class="sig-details"><summary>Comprendre cette signature</summary><p>La signature protège les octets du PDF. Elle figure dans le panneau de signatures du lecteur, sans ajouter de paraphe sur les pages. Elle ne constitue pas une signature personnelle qualifiée et n’inclut pas d’horodatage tiers.</p><dl class="sig-facts"><div><dt>SHA-256 de l’original</dt><dd class="sig-hash">{{ $document->original_sha256 }}</dd></div>@if ($document->signed_at)<div><dt>SHA-256 du PDF signé</dt><dd class="sig-hash">{{ $document->signed_sha256 }}</dd></div><div><dt>SHA-256 du certificat</dt><dd class="sig-hash">{{ $document->certificate_sha256 }}</dd></div>@elseif ($certificate['ready'])<div><dt>SHA-256 du certificat</dt><dd class="sig-hash">{{ $certificate['fingerprint'] }}</dd></div>@endif</dl></details>
    </section>
@endsection
