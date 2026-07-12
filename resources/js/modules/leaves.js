export const bootLeavePage = () => {
    const leaveStart = document.getElementById('leave_start_date');
    const leaveEnd = document.getElementById('leave_end_date');
    const leaveDayCount = document.getElementById('leave_day_count');
    const leaveBalanceHint = document.getElementById('leave_balance_hint');
    const leaveForm = document.querySelector('[data-leave-form]');

    if (!leaveStart || !leaveEnd || !leaveDayCount) {
        return;
    }

    const syncLeaveDays = () => {
        const start = new Date(`${leaveStart.value}T00:00:00`);
        const end = new Date(`${leaveEnd.value}T00:00:00`);

        if (!leaveStart.value || !leaveEnd.value || Number.isNaN(start.valueOf()) || Number.isNaN(end.valueOf())) {
            leaveDayCount.textContent = '-';
            if (leaveBalanceHint) {
                leaveBalanceHint.textContent = 'Sélectionnez les dates pour vérifier le solde.';
            }
            return;
        }

        const days = Math.floor((end - start) / 86400000) + 1;

        if (days < 1) {
            leaveDayCount.textContent = 'Erreur';
            if (leaveBalanceHint) {
                leaveBalanceHint.textContent = 'La date de fin doit être postérieure ou égale à la date de début.';
            }
            return;
        }

        leaveDayCount.textContent = String(days);

        if (leaveBalanceHint && leaveForm) {
            const projected = Number(leaveForm.dataset.projectedBalance ?? 0);
            leaveBalanceHint.textContent = days > projected
                ? 'Cette demande dépasse votre solde projeté. Elle pourra être refusée.'
                : 'Votre solde projeté couvre cette demande.';
        }
    };

    leaveStart.addEventListener('change', syncLeaveDays);
    leaveEnd.addEventListener('change', syncLeaveDays);
    syncLeaveDays();
};
