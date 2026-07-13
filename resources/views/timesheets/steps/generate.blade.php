<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><span>Étape 5 sur 6</span><h2 id="step-title">Confirmer la génération</h2><p>La génération commence immédiatement et peut prendre quelques instants. Gardez cette page ouverte.</p></div>
    <div class="ts-final-summary"><i data-lucide="files" aria-hidden="true"></i><div><strong>{{ count($rows) }} feuille(s) de temps</strong><span>Du {{ \Carbon\Carbon::parse($periodStart)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($periodEnd)->format('d/m/Y') }}</span><small>{{ $zipLabel }}.zip</small></div></div>
    <label class="ts-confirm"><input type="checkbox" wire:model="confirmed"><span>Je confirme avoir vérifié la période, les collaborateurs et leurs répartitions.</span></label>
    @error('confirmed')<p class="nc-error">{{ $message }}</p>@enderror
    <button class="nc-button ts-generate" type="button" wire:click="generate" wire:loading.attr="disabled" wire:target="generate">Générer le lot</button>
</section>
