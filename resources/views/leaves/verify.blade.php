@extends('layouts.app', [
    'title' => 'Verification document - Nere Tools',
])

@section('content')
    <section class="nc-page leave-workbench">
        <div class="nc-title-row">
            <div>
                <p class="nc-kicker">Verification</p>
                <h1 class="nc-title">Verification d'un document Nere Tools</h1>
                <p class="nc-lead">Controlez un visa electronique interne via son lien de verification ou en deposant un PDF genere par le portail.</p>
            </div>
        </div>

        <div class="leave-review-grid leave-verify-grid">
            <section class="nc-panel nc-panel-premium leave-verify-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Resultat du lien ou du QR code</h2>
                        <p>Le QR code des documents Conges pointe vers cette page, jamais vers le PDF lui-meme.</p>
                    </div>
                </div>

                @if ($tokenResult)
                    <div class="verify-status verify-status--{{ $tokenResult['status'] }}">
                        <strong>{{ $tokenResult['title'] }}</strong>
                        <p>{{ $tokenResult['message'] }}</p>
                    </div>
                @else
                    <div class="verify-status verify-status--neutral">
                        <strong>En attente de verification</strong>
                        <p>Scannez un QR code Nere Tools ou utilisez un lien complet de type <span class="verify-inline">/conges/verify/...</span>.</p>
                    </div>
                @endif

                <dl class="leave-detail-grid leave-verify-facts">
                    <div>
                        <dt>Reference</dt>
                        <dd>{{ $document?->document_reference ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt>Type</dt>
                        <dd>Demande de conge</dd>
                    </div>
                    <div>
                        <dt>Statut</dt>
                        <dd>{{ $document?->status ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt>Date de validation</dt>
                        <dd>{{ $document?->signed_at?->format('d/m/Y H:i') ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt>Validateur</dt>
                        <dd>
                            @if ($canSeePrivateDetails)
                                {{ $document?->signedBy?->name ?: 'Nere Capital' }}
                            @else
                                {{ $document ? 'Valide par Nere Capital' : '-' }}
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt>Empreinte SHA-256</dt>
                        <dd>{{ $document?->shortHash() ?: '-' }}</dd>
                    </div>
                </dl>

                @if ($document && $canSeePrivateDetails)
                    <div class="leave-verify-private">
                        <h3>Details internes</h3>
                        <dl class="leave-detail-grid">
                            <div>
                                <dt>Collaborateur</dt>
                                <dd>{{ $document->leaveRequest?->employee?->name() ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt>Departement</dt>
                                <dd>{{ $document->leaveRequest?->employee?->department?->name ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt>Periode</dt>
                                <dd>{{ $document->leaveRequest?->periodLabel() ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt>Durée</dt>
                                <dd>{{ $document->leaveRequest?->durationLabel() ?? '-' }}</dd>
                            </div>
                        </dl>

                        <div class="nc-actions">
                            <a href="{{ route('leaves.documents.download', $document) }}" class="nc-button">
                                <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                                Telecharger le PDF
                            </a>
                        </div>
                    </div>
                @endif
            </section>

            <section class="nc-panel leave-verify-panel">
                <div class="nc-panel-heading">
                    <div>
                        <h2>Verifier un fichier PDF</h2>
                        <p>Vous disposez d'un PDF ? Deposez-le ici pour verifier s'il correspond exactement a un document genere par Nere Tools.</p>
                    </div>
                </div>

                @if ($uploadResult)
                    <div class="verify-status verify-status--{{ $uploadResult['status'] }}">
                        <strong>{{ $uploadResult['title'] }}</strong>
                        <p>{{ $uploadResult['message'] }}</p>
                    </div>
                @endif

                @if ($uploadErrors?->any())
                    <div class="nc-alert">
                        {{ $uploadErrors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('leaves.verify.upload') }}" enctype="multipart/form-data" class="leave-verify-upload">
                    @csrf
                    <label class="nc-field">
                        <span>Fichier PDF</span>
                        <input type="file" name="file" accept="application/pdf,.pdf" required>
                    </label>
                    <button type="submit" class="nc-button">
                        <i data-lucide="shield-check" class="nc-icon" aria-hidden="true"></i>
                        Verifier le fichier
                    </button>
                </form>

                <p class="nc-muted verify-footnote">
                    Format accepte : PDF uniquement, taille maximale 10 Mo. Le fichier n'est jamais stocke durablement.
                </p>
            </section>
        </div>
    </section>
@endsection
