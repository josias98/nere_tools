import { bootLeavePage } from './modules/leaves';
import { bootTimesheetPage } from './modules/timesheets';
import { createIcons, icons } from 'lucide';

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });

    bootTimesheetPage();
    bootLeavePage();
});

document.addEventListener('nere:icons-refresh', () => createIcons({ icons }));
