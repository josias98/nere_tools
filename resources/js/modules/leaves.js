export const bootLeavePage = () => {
    const register = () => {
        Alpine.data('leaveRequestWizard', () => ({
            summaryOpen: false,
            uploading: false,
            progress: 0,
            dirty: false,
            init() {
                this.$watch('$wire.step', () => this.afterStepChange());
                this.$watch('$wire.form', () => { this.dirty = true; });

                window.addEventListener('beforeunload', (event) => {
                    if (this.dirty && this.$wire.step < 5) event.preventDefault();
                });

                this.$el.addEventListener('livewire-upload-start', () => {
                    this.uploading = true;
                    this.progress = 0;
                });
                this.$el.addEventListener('livewire-upload-finish', () => {
                    this.uploading = false;
                    this.progress = 100;
                });
                this.$el.addEventListener('livewire-upload-error', () => {
                    this.uploading = false;
                });
                this.$el.addEventListener('livewire-upload-progress', (event) => {
                    this.progress = event.detail.progress;
                });
            },
            afterStepChange() {
                requestAnimationFrame(() => {
                    const summary = this.$el.querySelector('[data-validation-summary]');
                    const target = summary || this.$el.querySelector('#leave-wizard-title');
                    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    target?.focus({ preventScroll: true });
                    target?.scrollIntoView({ block: 'start', behavior: reduceMotion ? 'auto' : 'smooth' });
                    document.dispatchEvent(new CustomEvent('nere:icons-refresh'));
                });
            },
        }));
    };

    if (window.Alpine) register();
    else document.addEventListener('alpine:init', register, { once: true });
};
