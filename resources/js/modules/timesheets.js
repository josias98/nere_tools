export const bootTimesheetPage = () => {
    const chipTray = document.getElementById('ts-chip-tray');
    const rowToggles = Array.from(document.querySelectorAll('.ts-row-toggle'));
    const csvFileInput = document.getElementById('csv_file');
    const csvFileName = document.getElementById('csv_file_name');
    const periodStart = document.getElementById('period_start');
    const periodEnd = document.getElementById('period_end');
    const zipLabel = document.getElementById('zip_label');
    const logoPreviewText = document.getElementById('logo_preview_text');
    const csvPlaceholder = 'Aucun fichier CSV sÃ©lectionnÃ©.';
    const logoPlaceholder = 'Le logo IP fourni sera appliquÃ© automatiquement dans les PDF gÃ©nÃ©rÃ©s.';

    if (!chipTray && !csvFileInput && !periodStart && !logoPreviewText) {
        return;
    }

    const renderChips = () => {
        if (!chipTray) {
            return;
        }

        const selectedLabels = rowToggles
            .filter((toggle) => toggle.checked)
            .map((toggle) => {
                const row = toggle.closest('tr');
                const firstName = row?.querySelector('input[name$="[first_name]"]')?.value ?? '';
                const lastName = row?.querySelector('input[name$="[last_name]"]')?.value ?? '';

                return `${firstName} ${lastName}`.trim() || toggle.dataset.chipLabel?.trim();
            })
            .filter(Boolean);

        chipTray.replaceChildren();

        if (selectedLabels.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'nc-muted';
            empty.textContent = 'Aucun collaborateur sÃ©lectionnÃ©.';
            chipTray.append(empty);
            return;
        }

        selectedLabels.forEach((label) => {
            const chip = document.createElement('span');
            chip.className = 'ts-chip';
            chip.textContent = label;
            chipTray.append(chip);
        });
    };

    const syncSelectedRows = () => {
        rowToggles.forEach((toggle) => {
            toggle.closest('tr')?.classList.toggle('is-selected', toggle.checked);
        });
    };

    const computedZipLabel = () => {
        if (!periodStart?.value || !periodEnd?.value) {
            return '';
        }

        const start = new Date(`${periodStart.value}T00:00:00`);
        const end = new Date(`${periodEnd.value}T00:00:00`);
        if (Number.isNaN(start.valueOf()) || Number.isNaN(end.valueOf())) {
            return '';
        }

        const sameYear = start.getUTCFullYear() === end.getUTCFullYear();
        const firstHalf = start.getUTCMonth() < 6 && end.getUTCMonth() < 6;
        const secondHalf = start.getUTCMonth() >= 6 && end.getUTCMonth() >= 6;

        if (sameYear && firstHalf) {
            return `Feuilles_de_temps_S1_${start.getUTCFullYear()}`;
        }

        if (sameYear && secondHalf) {
            return `Feuilles_de_temps_S2_${start.getUTCFullYear()}`;
        }

        const stamp = (date) => `${date.getUTCFullYear()}${String(date.getUTCMonth() + 1).padStart(2, '0')}`;

        return `Feuilles_de_temps_${stamp(start)}_${stamp(end)}`;
    };

    const syncZipLabel = () => {
        if (!zipLabel || zipLabel.dataset.userEdited === '1') {
            return;
        }

        zipLabel.value = computedZipLabel();
    };

    rowToggles.forEach((toggle) => toggle.addEventListener('change', () => {
        renderChips();
        syncSelectedRows();
    }));

    document.querySelectorAll('input[name$="[first_name]"], input[name$="[last_name]"]').forEach((input) => {
        input.addEventListener('input', renderChips);
    });

    periodStart?.addEventListener('change', syncZipLabel);
    periodEnd?.addEventListener('change', syncZipLabel);
    zipLabel?.addEventListener('input', () => {
        zipLabel.dataset.userEdited = '1';
    });
    csvFileInput?.addEventListener('change', () => {
        if (csvFileName) {
            csvFileName.textContent = csvFileInput.files?.[0]?.name || csvPlaceholder;
        }
    });

    if (logoPreviewText) {
        logoPreviewText.textContent = logoPlaceholder;
    }

    renderChips();
    syncSelectedRows();
    syncZipLabel();
};
