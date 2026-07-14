@props(['request', 'canSeeValidatorNames' => false])

<section class="nc-confirmation">
    <span class="nc-confirmation-mark" aria-hidden="true"><i data-lucide="check"></i></span>
    <div>
        <p class="nc-kicker">Demande soumise</p>
        <h2>Votre demande a ete creee.</h2>
        <dl class="nc-review-grid">
            <div><dt>Reference</dt><dd>{{ $request?->uuid }}</dd></div>
            <div><dt>Statut</dt><dd>{{ $request?->statusLabel() }}</dd></div>
            <div><dt>Prochaine etape</dt><dd>{{ $request?->currentApproval?->step_label ?? 'Suivi RH' }}</dd></div>
            @if ($canSeeValidatorNames && $request?->currentApproval?->validatorUser)
                <div><dt>Validateur</dt><dd>{{ $request->currentApproval->validatorUser->name }}</dd></div>
            @endif
        </dl>
        <div class="nc-actions">
            @if ($request)
                <a class="nc-button" href="{{ route('leaves.show', $request->uuid) }}">Voir le detail</a>
            @endif
            <a class="nc-ghost" href="{{ route('leaves.index') }}">Retour aux conges</a>
            <button class="nc-ghost" type="button" wire:click="createAnother">Creer une autre demande</button>
        </div>
    </div>
</section>
