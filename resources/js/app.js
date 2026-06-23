import { bootLeavePage } from './modules/leaves';
import { bootTimesheetPage } from './modules/timesheets';

document.addEventListener('DOMContentLoaded', () => {
    bootTimesheetPage();
    bootLeavePage();
});
