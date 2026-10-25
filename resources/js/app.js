import { bootLeavePage } from './modules/leaves';
import { bootTimesheetPage } from './modules/timesheets';
import { bootTooltips } from './modules/tooltips';
import { bootSignaturePage } from './modules/signatures';
import { createIcons, icons } from 'lucide';

const bootPage = () => {
    createIcons({ icons });

    bootTimesheetPage();
    bootLeavePage();
    bootTooltips();
    bootSignaturePage();

    document.querySelectorAll('.nc-account').forEach((account) => {
        account.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') { account.open = false; account.querySelector('summary').focus(); }
        });
        document.addEventListener('click', (event) => { if (!account.contains(event.target)) account.open = false; });
    });

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
};

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bootPage, { once: true });
else bootPage();

document.addEventListener('nere:icons-refresh', () => createIcons({ icons }));
