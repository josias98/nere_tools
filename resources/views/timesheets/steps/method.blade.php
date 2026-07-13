<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><span>Étape 1 sur 6</span><h2 id="step-title">Comment souhaitez-vous commencer ?</h2><p>Choisissez selon les données dont vous disposez.</p></div>
    <div class="ts-method-grid">
        <button type="button" class="ts-method-card" wire:click="chooseMethod('csv')">
            <span class="nc-badge active">Recommandé pour les lots</span><i data-lucide="file-up" aria-hidden="true"></i>
            <strong>Importer une clé CSV</strong><span>Rapide pour plusieurs collaborateurs, avec analyse et correction avant génération.</span><small>Prérequis : fichier CSV de 1 Mo maximum.</small>
        </button>
        <button type="button" class="ts-method-card" wire:click="chooseMethod('manual')">
            <span class="nc-badge">Sans fichier</span><i data-lucide="users" aria-hidden="true"></i>
            <strong>Créer le lot manuellement</strong><span>Idéal pour quelques collaborateurs ou une répartition ponctuelle.</span><small>Prérequis : collaborateurs actifs dans Néré Tools.</small>
        </button>
    </div>
</section>
