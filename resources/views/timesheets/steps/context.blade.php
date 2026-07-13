<section aria-labelledby="step-title" class="ts-step-panel">
    <div class="ts-step-heading"><span>Étape 2 sur 6</span><h2 id="step-title">Définir le contexte</h2><p>Cette période sera appliquée à toutes les feuilles du lot.</p></div>
    <div class="nc-form-grid">
        <div class="nc-field"><label for="period_start">Début de période</label><input id="period_start" type="date" wire:model="periodStart" wire:change="syncZipLabel" aria-describedby="period_start_error">@error('periodStart')<small id="period_start_error" class="nc-error">{{ $message }}</small>@enderror</div>
        <div class="nc-field"><label for="period_end">Fin de période</label><input id="period_end" type="date" wire:model="periodEnd" wire:change="syncZipLabel" aria-describedby="period_end_error">@error('periodEnd')<small id="period_end_error" class="nc-error">{{ $message }}</small>@enderror</div>
        <div class="nc-field ts-span"><label for="zip_label">Nom du livrable</label><input id="zip_label" type="text" wire:model="zipLabel" maxlength="120">@error('zipLabel')<small class="nc-error">{{ $message }}</small>@enderror</div>
    </div>
    <div class="ts-context-note"><i data-lucide="shield-check" aria-hidden="true"></i><p><strong>Données protégées.</strong> Les fichiers produits restent dans le stockage privé et leur téléchargement est contrôlé par Laravel.</p></div>
</section>
