<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><span>Point de départ</span><h2 id="step-title">Comment souhaitez-vous préparer ce lot&nbsp;?</h2><p>Vous pourrez tout vérifier avant la création des PDF.</p></div>
    <div class="ts-method-grid">
        <button type="button" class="ts-method-card" wire:click="chooseMethod('csv')">
            <span class="ts-method-index">01</span><i data-lucide="file-up" aria-hidden="true"></i>
            <strong>J’ai une clé CSV</strong><span>Chargez plusieurs collaborateurs en une fois. Chaque ligne sera analysée et restera modifiable.</span><small><b>Conseillé</b> pour un lot récurrent · CSV, 1 Mo maximum</small><span class="ts-method-action">Choisir l’import <i data-lucide="arrow-right" aria-hidden="true"></i></span>
        </button>
        <button type="button" class="ts-method-card" wire:click="chooseMethod('manual')">
            <span class="ts-method-index">02</span><i data-lucide="users" aria-hidden="true"></i>
            <strong>Je pars de zéro</strong><span>Recherchez les collaborateurs puis ajustez leurs répartitions directement dans l’assistant.</span><small>Adapté à un besoin ponctuel · aucun fichier requis</small><span class="ts-method-action">Commencer la saisie <i data-lucide="arrow-right" aria-hidden="true"></i></span>
        </button>
    </div>
</section>
