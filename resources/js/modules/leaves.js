export const bootLeavePage = () => {
    const startInput = document.getElementById('leave_start_date');
    const endInput = document.getElementById('leave_end_date');
    const startTimeInput = document.getElementById('leave_start_time');
    const endTimeInput = document.getElementById('leave_end_time');
    const hourFields = document.querySelector('[data-hour-fields]');
    const typeInput = document.getElementById('leave_type_id');
    const count = document.getElementById('leave_day_count');
    const balance = document.getElementById('leave_remaining_balance');
    const hint = document.getElementById('leave_balance_hint');
    const form = document.querySelector('[data-leave-form]');
    const ruleData = JSON.parse(document.getElementById('leave-rule-data')?.textContent || '{}');
    if (!startInput || !endInput || !count) return;

    const effectiveConfiguration = (option) => {
        const type = ruleData[option?.value] ?? {};
        const date = startInput.value;
        const rule = date
            ? type.rules?.find((candidate) => candidate.effective_from <= date && (!candidate.effective_until || candidate.effective_until >= date))
            : null;
        return { ...(type.defaults ?? {}), ...(rule?.configuration ?? {}) };
    };

    const sync = () => {
        const option = typeInput?.selectedOptions[0];
        const configuration = effectiveConfiguration(option);
        const requiredContext = { family_event: 'relationship', other_absence: 'reason' }[option?.dataset.slug];
        document.querySelectorAll('[data-leave-context]').forEach((field) => {
            const required = field.dataset.leaveContext === requiredContext;
            field.querySelector('input').required = required;
            field.querySelector('[data-leave-context-required]').hidden = !required;
        });
        const unit = configuration.unit ?? option?.dataset.unit ?? 'calendar_day';
        const isHourly = unit === 'hour';
        if (hourFields) hourFields.hidden = !isHourly;
        if (startTimeInput) startTimeInput.required = isHourly;
        if (endTimeInput) endTimeInput.required = isHourly;

        const attachmentRequired = configuration.requires_attachment ?? option?.dataset.attachment === '1';
        document.getElementById('leave_attachment_hint').textContent = attachmentRequired ? 'Obligatoire' : 'Facultatif';
        const attachments = document.getElementById('leave_attachments');
        if (attachments) attachments.required = attachmentRequired;
        const requirement = document.getElementById('leave_attachment_requirement');
        if (requirement) requirement.textContent = attachmentRequired ? 'Requis' : 'Facultatif';

        const startTime = isHourly ? startTimeInput?.value : '00:00';
        const endTime = isHourly ? endTimeInput?.value : '00:00';
        const start = new Date(`${startInput.value}T${startTime || '00:00'}:00`);
        const end = new Date(`${endInput.value}T${endTime || '00:00'}:00`);
        if (isHourly && (!startTime || !endTime)) return;
        if (!startInput.value || !endInput.value || Number.isNaN(start.valueOf()) || Number.isNaN(end.valueOf())) return;
        const calendarDays = Math.floor((end - start) / 86400000) + 1;
        if (end <= start && isHourly) { count.textContent = 'Erreur'; return; }
        if (calendarDays < 1 && !isHourly) { count.textContent = 'Erreur'; return; }

        let duration = calendarDays;
        if (isHourly) duration = Number(((end - start) / 3600000).toFixed(2));
        else if (unit === 'working_day') {
            duration = 0;
            for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                if (date.getDay() !== 0 && date.getDay() !== 6) duration++;
            }
        } else if (unit === 'week') duration = Number((calendarDays / 7).toFixed(2));
        else if (unit === 'month') duration = Number((calendarDays / 30).toFixed(2));

        count.textContent = String(duration);
        const unitLabels = { calendar_day: 'jour calendaire', working_day: 'jour ouvrable', hour: 'heure', week: 'semaine', month: 'mois' };
        document.getElementById('leave_unit_label').textContent = `${unitLabels[unit] ?? option?.dataset.unitLabel ?? 'jour'}(s)`;
        const returnAt = new Date(end);
        if (!isHourly) returnAt.setDate(returnAt.getDate() + 1);
        document.getElementById('leave_return_date').textContent = isHourly ? returnAt.toLocaleString('fr-FR') : returnAt.toLocaleDateString('fr-FR');

        const impacts = configuration.counts_against_balance ?? option?.dataset.balanceImpact === '1';
        const projected = Number(form?.dataset.projectedBalance ?? 0);
        if (balance) balance.textContent = impacts ? `${(projected - duration).toFixed(2)} jours` : 'Sans impact';
        if (hint) hint.textContent = impacts && duration > projected ? 'Cette demande dépasse votre solde projeté.' : impacts ? 'Votre solde projeté couvre cette demande.' : 'Ce type utilise un quota séparé du congé annuel.';
    };

    startInput.addEventListener('change', sync);
    endInput.addEventListener('change', sync);
    startTimeInput?.addEventListener('change', sync);
    endTimeInput?.addEventListener('change', sync);
    typeInput?.addEventListener('change', sync);
    sync();
    document.querySelector('[data-validation-summary]')?.focus();
};
