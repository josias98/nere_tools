<section aria-labelledby="step-title" class="ts-step-panel ts-success-panel">
    <div class="ts-success-icon"><i data-lucide="check" aria-hidden="true"></i></div><div class="ts-step-heading"><span>Terminé</span><h2 id="step-title">Votre lot est prêt</h2><p>{{ $generation?->pdf_count }} PDF ont été regroupés dans une archive privée.</p></div>
    @if ($generation)<div class="ts-delivery-actions"><a class="nc-button" href="{{ route('timesheets.download.zip', $generation) }}">Télécharger le ZIP</a><a class="nc-ghost" href="{{ route('timesheets.result', $generation) }}">Voir le détail du lot</a></div>@endif
    <button class="nc-ghost" type="button" wire:click="resetWizard">Créer un nouveau lot</button>
</section>
