{{-- Hallmark · pre-emit critique: P5 H5 E4 S5 R5 V4 · design-system: design.md --}}
@php
    $active = $document->status === \App\Models\LeaveDocument::STATUS_ACTIVE;
    $revoked = $document->status === \App\Models\LeaveDocument::STATUS_REVOKED;
    $canManage = auth()->user()?->canAccessAdmin() && $leaveRequest->status === 'approved';
@endphp
<section class="nc-panel leave-certificate" id="attestation" aria-labelledby="certificate-title" tabindex="-1">
    <div class="nc-panel-heading">
        <div>
            <h2 id="certificate-title">Attestation de congé</h2>
            <p class="leave-certificate-reference">{{ $document->document_reference }}</p>
        </div>
        <span class="leave-status {{ $active ? 'is-approved' : ($revoked ? 'is-rejected' : '') }}">
            <i data-lucide="{{ $active ? 'shield-check' : ($revoked ? 'shield-off' : 'history') }}" class="nc-icon" aria-hidden="true"></i>
            {{ $active ? 'Active' : ($revoked ? 'Révoquée' : 'Remplacée') }}
        </span>
    </div>

    @if ($revoked)
        <div class="leave-certificate-notice is-revoked">
            <strong>Cette attestation n’est plus valide.</strong>
            <p>Sa page de vérification indique qu’elle a été révoquée. La demande de congé reste approuvée et le solde reste inchangé.</p>
            <p><strong>Motif :</strong> {{ $document->status_reason ?: 'Non renseigné.' }}</p>
        </div>
        @if ($canManage)
            <form method="POST" action="{{ route('admin.leaves.documents.restore', $document) }}" class="leave-certificate-restore" data-document-action>
                @csrf
                <div>
                    <h3>Révocation faite par erreur ?</h3>
                    <p>Réactivez la même attestation : le PDF, la référence et le QR code d’origine redeviennent valides.</p>
                </div>
                <button class="nc-button is-secondary" type="submit" data-pending-label="Réactivation…">
                    <i data-lucide="undo-2" class="nc-icon" aria-hidden="true"></i>
                    Annuler la révocation
                </button>
            </form>
        @endif
    @elseif ($active)
        <p>Cette attestation est valide. Son QR code permet d’en vérifier l’authenticité.</p>
    @else
        <p>Cette version a été remplacée. Utilisez la dernière attestation disponible.</p>
    @endif

    <div class="nc-actions">
        @if ($active)
            <a href="{{ route('leaves.documents.download', $document) }}" class="nc-button">
                <i data-lucide="download" class="nc-icon" aria-hidden="true"></i>
                Télécharger le PDF
            </a>
        @endif
        <a class="nc-ghost" href="{{ $document->verification_url }}">
            <i data-lucide="scan-line" class="nc-icon" aria-hidden="true"></i>
            Vérifier l’attestation
        </a>
    </div>

    @if ($canManage)
        <div class="leave-certificate-tools">
            @if ($active)
                <details class="leave-certificate-action" @if(old('document_action') === 'revoke' && $errors->any()) open @endif>
                    <summary>Révoquer l’attestation</summary>
                    <form method="POST" action="{{ route('admin.leaves.documents.revoke', $document) }}" data-document-action>
                        @csrf
                        <input type="hidden" name="document_action" value="revoke">
                        <p>Le PDF sera signalé comme révoqué dès confirmation. Le congé et son solde restent inchangés. Vous pourrez annuler cette révocation en cas d’erreur.</p>
                        <label class="nc-field" for="revocation-reason">
                            <span>Motif de révocation <small>Obligatoire</small></span>
                            <textarea id="revocation-reason" name="reason" rows="3" maxlength="2000" required aria-required="true" aria-describedby="revocation-reason-help" @error('reason') aria-invalid="true" @enderror>{{ old('document_action') === 'revoke' ? old('reason') : '' }}</textarea>
                            <small id="revocation-reason-help">@error('reason'){{ $message }}@else Ce motif sera conservé dans l’historique de l’attestation. @enderror</small>
                        </label>
                        <div class="nc-actions">
                            <button class="nc-ghost danger" type="submit" data-pending-label="Révocation…">Confirmer la révocation</button>
                            <button class="nc-ghost" type="button" data-close-document-action>Garder l’attestation</button>
                        </div>
                    </form>
                </details>
            @endif

            <details class="leave-certificate-action" @if(old('document_action') === 'regenerate' && $errors->any()) open @endif>
                <summary>Créer une nouvelle version</summary>
                <form method="POST" action="{{ route('admin.leaves.documents.regenerate', $leaveRequest->uuid) }}" data-document-action>
                    @csrf
                    <input type="hidden" name="document_action" value="regenerate">
                    <p>Une nouvelle référence et un nouveau QR code seront créés. @if($active) L’attestation actuelle sera remplacée. @else Pour corriger une révocation accidentelle, utilisez « Annuler la révocation ». @endif</p>
                    <label class="nc-field" for="regeneration-reason">
                        <span>Motif de la nouvelle version <small>Facultatif</small></span>
                        <textarea id="regeneration-reason" name="reason" rows="2" maxlength="2000">{{ old('document_action') === 'regenerate' ? old('reason') : '' }}</textarea>
                    </label>
                    <div class="nc-actions">
                        <button class="nc-button is-secondary" type="submit" data-pending-label="Création…">Créer la nouvelle version</button>
                        <button class="nc-ghost" type="button" data-close-document-action>Annuler</button>
                    </div>
                </form>
            </details>
        </div>
    @endif
</section>
