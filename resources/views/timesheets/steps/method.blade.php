<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><h2 id="step-title">Comment préparer ce lot&nbsp;?</h2></div>
    <div class="ts-method-grid">
        <button type="button" class="ts-method-card" wire:click="chooseMethod('csv')">
            <i data-lucide="file-up" aria-hidden="true"></i>
            <strong>Importer un CSV</strong><small>1 Mo maximum</small><span class="ts-method-action" aria-hidden="true"><i data-lucide="arrow-right"></i></span>
        </button>
        <button type="button" class="ts-method-card" wire:click="chooseMethod('manual')">
            <i data-lucide="users" aria-hidden="true"></i>
            <strong>Saisie manuelle</strong><span class="ts-method-action" aria-hidden="true"><i data-lucide="arrow-right"></i></span>
        </button>
    </div>
</section>
