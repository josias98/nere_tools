<section class="nc-panel" id="settings">
    <div class="nc-panel-heading"><div><h2>Paramètres de calcul et de documents</h2><p>Ces valeurs s’appliquent aux futures opérations et chaque modification est historisée.</p></div></div>
    <form method="POST" action="{{ route('admin.leaves.settings.update') }}" class="nc-form-grid" data-dirty-form>
        @csrf @method('PUT')
        <input type="hidden" name="settings_updated_at" value="{{ $settings->get('monthly_accrual_days')?->updated_at?->toISOString() }}">
        <label class="nc-field"><span>Jours acquis par mois</span><input type="number" step="0.01" min="0" max="31" name="monthly_accrual_days" value="{{ old('monthly_accrual_days', $settings->get('monthly_accrual_days')?->value ?? '2.5') }}" required></label>
        <label class="nc-field"><span>Politique d’acquisition</span><select name="accrual_policy" required>@foreach (['end_of_month' => 'Fin de mois', 'start_of_month' => 'Début de mois', 'prorated' => 'Prorata'] as $value => $label)<option value="{{ $value }}" @selected(old('accrual_policy', $settings->get('accrual_policy')?->value ?? 'end_of_month') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label class="nc-field"><span>Nom du signataire</span><input name="LEAVE_CERTIFICATE_SIGNATORY_NAME" value="{{ old('LEAVE_CERTIFICATE_SIGNATORY_NAME', $settings->get('LEAVE_CERTIFICATE_SIGNATORY_NAME')?->value ?? config('leaves.certificate_signatory_name')) }}"></label>
        <label class="nc-field"><span>Fonction du signataire</span><input name="LEAVE_CERTIFICATE_SIGNATORY_TITLE" value="{{ old('LEAVE_CERTIFICATE_SIGNATORY_TITLE', $settings->get('LEAVE_CERTIFICATE_SIGNATORY_TITLE')?->value ?? config('leaves.certificate_signatory_title')) }}"><small>Image de signature : {{ $signatureExists ? 'disponible' : 'absente (facultative)' }}.</small></label>
        <label class="nc-field"><span>Motif du changement</span><textarea name="change_reason" maxlength="1000" placeholder="Pourquoi ce réglage change-t-il ?">{{ old('change_reason') }}</textarea></label>
        <div class="nc-actions"><button class="nc-button" type="submit" data-tooltip-title="Appliquer les paramètres" data-tooltip="Enregistre les réglages pour les opérations futures et conserve une trace du changement."><i data-lucide="save" aria-hidden="true"></i>Enregistrer les paramètres</button><span class="unsaved-indicator" data-unsaved-indicator hidden>Modifications non enregistrées</span></div>
    </form>
</section>
