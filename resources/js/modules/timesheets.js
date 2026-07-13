export const bootTimesheetPage = () => {
    const register = () => {
        Alpine.data('timesheetWizard', () => ({
            dirty: false,
            init() {
                const saved = JSON.parse(sessionStorage.getItem('nere.timesheet-wizard') || 'null');
                if (saved && !this.$wire.generationUuid) {
                    for (const [key, value] of Object.entries(saved)) this.$wire.$set(key, value, false);
                }
                this.$wire.$watch('step', () => this.persist());
                this.$wire.$watch('rows', () => { this.dirty = true; this.persist(); });
                window.addEventListener('beforeunload', (event) => {
                    if (this.dirty && this.$wire.step > 1 && this.$wire.step < 6) event.preventDefault();
                });
                document.addEventListener('livewire:navigated', () => document.dispatchEvent(new CustomEvent('nere:icons-refresh')));
            },
            persist() {
                if (this.$wire.step === 6) return sessionStorage.removeItem('nere.timesheet-wizard');
                sessionStorage.setItem('nere.timesheet-wizard', JSON.stringify({
                    step: this.$wire.step, method: this.$wire.method, periodStart: this.$wire.periodStart,
                    periodEnd: this.$wire.periodEnd, zipLabel: this.$wire.zipLabel, rows: this.$wire.rows,
                }));
            },
        }));
    };

    if (window.Alpine) register();
    else document.addEventListener('alpine:init', register, { once: true });
};
