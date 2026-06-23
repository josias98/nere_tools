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
                leaveBalanceHint.textContent = 'SÃ©lectionnez les dates pour vÃ©rifier le solde.';
            }
            return;
        }

        const days = Math.floor((end - start) / 86400000) + 1;

        if (days < 1) {
            leaveDayCount.textContent = 'Erreur';
            if (leaveBalanceHint) {
                leaveBalanceHint.textContent = 'La date de fin doit Ãªtre postÃ©rieure ou Ã©gale Ã  la date de dÃ©but.';
            }
            return;
        }

        leaveDayCount.textContent = String(days);

        if (leaveBalanceHint && leaveForm) {
            const projected = Number(leaveForm.dataset.projectedBalance ?? 0);
            leaveBalanceHint.textContent = days > projected
                ? 'Cette demande dÃ©passe votre solde projetÃ©. Elle pourra Ãªtre refusÃ©e.'
                : 'Votre solde projetÃ© couvre cette demande.';
        }
    };

    leaveStart.addEventListener('change', syncLeaveDays);
    leaveEnd.addEventListener('change', syncLeaveDays);
    syncLeaveDays();
};
