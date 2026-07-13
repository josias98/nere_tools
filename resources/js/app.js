import { bootLeavePage } from './modules/leaves';
import { bootTimesheetPage } from './modules/timesheets';
import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });

    bootTimesheetPage();
    bootLeavePage();

    const search = document.querySelector('[data-admin-search]');
    search?.addEventListener('input', ({ target }) => {
        const query = target.value.trim().toLocaleLowerCase('fr');
        document.querySelectorAll('[data-searchable]').forEach((item) => {
            item.hidden = query !== '' && !item.dataset.searchable.toLocaleLowerCase('fr').includes(query);
        });
    });

    document.querySelectorAll('[data-dirty-form]').forEach((form) => {
        const initial = new FormData(form);
        const changed = () => [...new FormData(form)].some(([key, value]) => String(initial.get(key)) !== String(value));
        form.addEventListener('input', () => form.querySelector('[data-unsaved-indicator]')?.toggleAttribute('hidden', !changed()));
        window.addEventListener('beforeunload', (event) => { if (changed()) event.preventDefault(); });
    });
});

document.addEventListener('nere:icons-refresh', () => createIcons({ icons }));
