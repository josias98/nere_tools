export const bootLeavePage = () => {
    const startInput = document.getElementById('leave_start_date');
    const endInput = document.getElementById('leave_end_date');
    const typeInput = document.getElementById('leave_type_id');
    const count = document.getElementById('leave_day_count');
    const balance = document.getElementById('leave_remaining_balance');
    const hint = document.getElementById('leave_balance_hint');
    const form = document.querySelector('[data-leave-form]');
    if (!startInput || !endInput || !count) return;

    const sync = () => {
        const start = new Date(`${startInput.value}T00:00:00`);
        const end = new Date(`${endInput.value}T00:00:00`);
        if (!startInput.value || !endInput.value || Number.isNaN(start.valueOf()) || Number.isNaN(end.valueOf())) return;
        const calendarDays = Math.floor((end - start) / 86400000) + 1;
        if (calendarDays < 1) { count.textContent = 'Erreur'; return; }

        const option = typeInput?.selectedOptions[0];
        const unit = option?.dataset.unit ?? 'calendar_day';
        let duration = calendarDays;
        if (unit === 'working_day') {
            duration = 0;
            for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                if (date.getDay() !== 0 && date.getDay() !== 6) duration++;
            }
        } else if (unit === 'week') duration = Number((calendarDays / 7).toFixed(2));
        else if (unit === 'month') duration = Number((calendarDays / 30).toFixed(2));

        count.textContent = String(duration);
        document.getElementById('leave_unit_label').textContent = `${option?.dataset.unitLabel ?? 'jour'}(s)`;
        document.getElementById('leave_attachment_hint').textContent = option?.dataset.attachment === '1' ? 'Obligatoire' : 'Facultatif';
        const attachments = document.getElementById('leave_attachments');
        const attachmentRequired = option?.dataset.attachment === '1';
        if (attachments) attachments.required = attachmentRequired;
        const requirement = document.getElementById('leave_attachment_requirement');
        if (requirement) requirement.textContent = attachmentRequired ? 'Requis' : 'Facultatif';
        const returnAt = new Date(end); returnAt.setDate(returnAt.getDate() + 1);
        document.getElementById('leave_return_date').textContent = returnAt.toLocaleDateString('fr-FR');

        const impacts = option?.dataset.balanceImpact === '1';
        const projected = Number(form?.dataset.projectedBalance ?? 0);
        if (balance) balance.textContent = impacts ? `${(projected - duration).toFixed(2)} jours` : 'Sans impact';
        if (hint) hint.textContent = impacts && duration > projected ? 'Cette demande dépasse votre solde projeté.' : impacts ? 'Votre solde projeté couvre cette demande.' : 'Ce type utilise un quota séparé du congé annuel.';
    };

    startInput.addEventListener('change', sync);
    endInput.addEventListener('change', sync);
    typeInput?.addEventListener('change', sync);
    sync();
    document.querySelector('[data-validation-summary]')?.focus();
};
