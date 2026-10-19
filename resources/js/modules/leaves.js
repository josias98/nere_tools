export const bootLeavePage = () => {
    document.querySelector('[data-validation-summary]')?.focus();
    document.querySelectorAll('[data-close-document-action]').forEach((button) => {
        button.addEventListener('click', () => {
            const details = button.closest('details');
            details.open = false;
            details.querySelector('summary').focus();
        });
    });
    document.querySelectorAll('[data-document-action], [data-leave-reminder]').forEach((actionForm) => {
        const button = actionForm.querySelector('[type="submit"]');
        const idleLabel = button.innerHTML;
        actionForm.addEventListener('submit', (event) => {
            if (actionForm.getAttribute('aria-busy') === 'true') {
                event.preventDefault();
                return;
            }
            actionForm.setAttribute('aria-busy', 'true');
            button.disabled = true;
            button.textContent = button.dataset.pendingLabel;
        });
        window.addEventListener('pageshow', () => {
            actionForm.removeAttribute('aria-busy');
            button.disabled = false;
            button.innerHTML = idleLabel;
        });
    });

    const startInput = document.getElementById('leave_start_date');
    const endInput = document.getElementById('leave_end_date');
    const startTimeInput = document.getElementById('leave_start_time');
    const endTimeInput = document.getElementById('leave_end_time');
    const hourFields = document.querySelector('[data-hour-fields]');
    const typeInput = document.getElementById('leave_type_id');
    const count = document.getElementById('leave_day_count');
    const balance = document.getElementById('leave_remaining_balance');
    const hint = document.getElementById('leave_balance_hint');
    const form = document.querySelector('[data-leave-form]');
    const ruleData = JSON.parse(document.getElementById('leave-rule-data')?.textContent || '{}');
    const employeeInput = document.getElementById('leave_employee_id');
    const employeeBalances = JSON.parse(document.getElementById('leave-employee-balance-data')?.textContent || '{}');
    if (!startInput || !endInput || !count) return;

    const effectiveConfiguration = (option) => {
        const type = ruleData[option?.value] ?? {};
        const date = startInput.value;
        const rule = date
            ? type.rules?.find((candidate) => candidate.effective_from <= date && (!candidate.effective_until || candidate.effective_until >= date))
            : null;
        return { ...(type.defaults ?? {}), ...(rule?.configuration ?? {}) };
    };

    const sync = () => {
        if (employeeInput && employeeBalances[employeeInput.value]) {
            form.dataset.projectedBalance = employeeBalances[employeeInput.value].projected_balance;
            document.getElementById('leave_projected_balance').textContent = `${Number(form.dataset.projectedBalance).toFixed(2)} jours`;
        }

        const option = typeInput?.selectedOptions[0];
        const configuration = effectiveConfiguration(option);
        const requiredContext = { family_event: 'relationship', other_absence: 'reason' }[option?.dataset.slug];
        document.querySelectorAll('[data-leave-context]').forEach((field) => {
            const required = field.dataset.leaveContext === requiredContext;
            if (form.classList.contains('is-enhanced')) field.hidden = !required;
            field.querySelector('input').required = required;
            field.querySelector('input').disabled = !required;
        });
        const unit = configuration.unit ?? option?.dataset.unit ?? 'calendar_day';
        const isHourly = unit === 'hour';
        if (hourFields) hourFields.hidden = !isHourly;
        if (startTimeInput) startTimeInput.required = isHourly;
        if (endTimeInput) endTimeInput.required = isHourly;

        const attachmentRequired = configuration.requires_attachment ?? option?.dataset.attachment === '1';
        document.getElementById('leave_attachment_hint').textContent = attachmentRequired ? 'Obligatoire' : 'Facultatif';
        const attachments = document.getElementById('leave_attachments');
        if (attachments) attachments.required = attachmentRequired;
        const requirement = document.getElementById('leave_attachment_requirement');
        if (requirement) requirement.textContent = attachmentRequired ? 'Requis' : 'Facultatif';
        const uploadSlot = document.querySelector('[data-leave-upload-slot]');
        const optionalUpload = document.querySelector('[data-leave-optional-upload]');
        const uploadField = document.querySelector('.leave-wizard-upload');
        if (form.classList.contains('is-enhanced') && uploadField) {
            (attachmentRequired ? uploadSlot : optionalUpload)?.append(uploadField);
        }

        count.textContent = '–';
        if (balance) balance.textContent = '–';
        document.getElementById('leave_return_date').textContent = '–';
        if (hint) hint.textContent = 'Choisissez vos dates.';

        const startTime = isHourly ? startTimeInput?.value : '00:00';
        const endTime = isHourly ? endTimeInput?.value : '00:00';
        const start = new Date(`${startInput.value}T${startTime || '00:00'}:00`);
        const end = new Date(`${endInput.value}T${endTime || '00:00'}:00`);
        if (isHourly && (!startTime || !endTime)) return;
        if (!startInput.value || !endInput.value || Number.isNaN(start.valueOf()) || Number.isNaN(end.valueOf())) return;
        const calendarDays = Math.floor((end - start) / 86400000) + 1;
        if (end <= start && isHourly) { count.textContent = 'Erreur'; return; }
        if (calendarDays < 1 && !isHourly) { count.textContent = 'Erreur'; return; }

        let duration = calendarDays;
        if (isHourly) duration = Number(((end - start) / 3600000).toFixed(2));
        else if (unit === 'working_day') {
            duration = 0;
            for (const date = new Date(start); date <= end; date.setDate(date.getDate() + 1)) {
                if (date.getDay() !== 0 && date.getDay() !== 6) duration++;
            }
        } else if (unit === 'week') duration = Number((calendarDays / 7).toFixed(2));
        else if (unit === 'month') duration = Number((calendarDays / 30).toFixed(2));

        count.textContent = String(duration);
        const unitLabels = {
            calendar_day: ['jour calendaire', 'jours calendaires'],
            working_day: ['jour ouvrable', 'jours ouvrables'],
            hour: ['heure', 'heures'],
            week: ['semaine', 'semaines'],
            month: ['mois', 'mois'],
        };
        document.getElementById('leave_unit_label').textContent = (unitLabels[unit] ?? [option?.dataset.unitLabel ?? 'jour', option?.dataset.unitLabel ?? 'jours'])[duration === 1 ? 0 : 1];
        const returnAt = new Date(end);
        if (!isHourly) returnAt.setDate(returnAt.getDate() + 1);
        document.getElementById('leave_return_date').textContent = isHourly ? returnAt.toLocaleString('fr-FR') : returnAt.toLocaleDateString('fr-FR');

        const impacts = configuration.counts_against_balance ?? option?.dataset.balanceImpact === '1';
        const projected = Number(form?.dataset.projectedBalance ?? 0);
        if (balance) balance.textContent = impacts ? `${(projected - duration).toFixed(2)} jours` : 'Sans impact';
        if (hint) hint.textContent = impacts && duration > projected ? 'Cette demande dépasse votre solde projeté.' : impacts ? 'Votre solde projeté couvre cette demande.' : 'Ce type utilise un quota séparé du congé annuel.';
    };

    startInput.addEventListener('change', sync);
    endInput.addEventListener('change', sync);
    startTimeInput?.addEventListener('change', sync);
    endTimeInput?.addEventListener('change', sync);
    typeInput?.addEventListener('change', sync);
    employeeInput?.addEventListener('change', sync);
    sync();

    if (!form || !typeInput) return;
    const stage = form.querySelector('[data-leave-stage]');
    const views = Object.fromEntries([...form.querySelectorAll('[data-leave-step]')].map((view) => [view.dataset.leaveStep, view]));
    if (!stage || Object.keys(views).length !== 4) return;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const slugToOption = Object.fromEntries([...typeInput.options].filter((option) => option.dataset.slug).map((option) => [option.dataset.slug, option]));
    const categoryFor = (slug) => {
        if (slug === 'annual_leave') return 'annual';
        if (slug === 'other_absence') return 'other';
        if (['non_occupational_illness', 'occupational_injury'].includes(slug)) return 'health';
        if (['maternity', 'internal_paternity', 'family_event'].includes(slug)) return 'family';
        return null;
    };
    const typeLabel = (option) => ({
        family_event: 'Décès d’un proche',
        occupational_injury: 'Accident du travail',
        other_absence: 'Autre absence',
    }[option?.dataset.slug] ?? option?.textContent.trim() ?? '');
    let category = categoryFor(typeInput.selectedOptions[0]?.dataset.slug);
    let current = (typeInput.value || form.dataset.hasErrors === '1') ? 'details' : 'choice';
    let moving = false;

    const path = form.querySelector('[data-leave-path]');
    const progressBar = form.querySelector('[data-leave-progress-bar]');
    const updatePath = () => {
        const steps = ['choice', ...(category === 'health' || category === 'family' ? ['clarify'] : []), 'details', 'review'];
        const labels = { choice: 'Motif', clarify: 'Précision', details: 'Dates', review: 'Envoi' };
        const index = Math.max(0, steps.indexOf(current));
        progressBar.style.width = `${((index + 1) / steps.length) * 100}%`;
        path.replaceChildren(...steps.map((step, position) => {
            const item = document.createElement('li');
            item.textContent = labels[step];
            if (position === index) item.setAttribute('aria-current', 'step');
            if (position < index) item.classList.add('is-done');
            return item;
        }));
    };

    const updateChoice = () => {
        form.querySelector('[data-leave-clarify-title]').textContent = category === 'health' ? 'Maladie ou accident du travail ?' : 'Quelle situation ?';
        form.querySelectorAll('[data-leave-branch]').forEach((button) => {
            button.hidden = button.dataset.leaveBranch !== category;
        });
        const option = typeInput.selectedOptions[0];
        const picked = form.querySelector('[data-leave-picked]');
        picked.textContent = option?.value ? typeLabel(option) : '';
        picked.hidden = !option?.value;
        form.querySelector('.leave-type-fallback').hidden = Boolean(category && category !== 'catalog');
        views.details.querySelector('[data-leave-back="choice"]').textContent = category === 'health' || category === 'family' ? '← Revenir au choix' : '← Revenir aux motifs';
    };

    const formatDate = (value) => {
        if (!value) return 'à préciser';
        const [year, month, day] = value.split('-').map(Number);
        return new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date(year, month - 1, day, 12));
    };
    const updateReview = () => {
        const option = typeInput.selectedOptions[0];
        form.querySelector('[data-review-type]').textContent = typeLabel(option) || '–';
        form.querySelector('[data-review-period]').textContent = `Du ${formatDate(startInput.value)} au ${formatDate(endInput.value)}`;
        form.querySelector('[data-review-duration]').textContent = `${count.textContent} ${document.getElementById('leave_unit_label').textContent}`;
        const extraName = { family_event: 'relationship', other_absence: 'reason' }[option?.dataset.slug];
        const extraRow = form.querySelector('[data-review-extra]');
        extraRow.hidden = !extraName;
        if (extraName) {
            form.querySelector('[data-review-extra-label]').textContent = extraName === 'relationship' ? 'Lien de parenté' : 'Motif';
            form.querySelector('[data-review-extra-value]').textContent = form.elements[extraName].value.trim();
        }
        const attachmentRow = form.querySelector('[data-review-attachment]');
        const files = document.getElementById('leave_attachments').files;
        attachmentRow.hidden = files.length === 0;
        form.querySelector('[data-review-attachment-value]').textContent = [...files].map((file) => file.name).join(', ');
    };

    const showStep = async (next, animated = true) => {
        if (moving || !views[next] || next === current) return;
        if (next === 'clarify' || next === 'details') updateChoice();
        if (next === 'review') updateReview();
        const previous = views[current];
        const following = views[next];
        const order = { choice: 0, clarify: 1, details: 2, review: 3 };
        const direction = order[next] > order[current] ? 1 : -1;
        current = next;
        updatePath();
        if (!animated || reducedMotion.matches) {
            previous.hidden = true;
            following.hidden = false;
            following.querySelector('h2')?.focus({ preventScroll: true });
            return;
        }
        moving = true;
        const fromHeight = stage.offsetHeight;
        previous.style.position = 'absolute';
        previous.style.inset = '0';
        previous.style.width = '100%';
        following.hidden = false;
        const toHeight = following.getBoundingClientRect().height;
        stage.style.height = `${fromHeight}px`;
        const heightMotion = stage.animate([{ height: `${fromHeight}px` }, { height: `${toHeight}px` }], { duration: 430, easing: 'cubic-bezier(.16,1,.3,1)' });
        const exit = previous.animate([{ opacity: 1, transform: 'translateX(0) scale(1)' }, { opacity: 0, transform: `translateX(${-22 * direction}px) scale(.985)` }], { duration: 250, easing: 'ease-in', fill: 'forwards' });
        const enter = following.animate([{ opacity: 0, transform: `translateX(${30 * direction}px) scale(.985)` }, { opacity: 1, transform: 'translateX(0) scale(1)' }], { duration: 430, easing: 'cubic-bezier(.16,1,.3,1)', fill: 'forwards' });
        await Promise.all([heightMotion.finished, exit.finished, enter.finished].map((promise) => promise.catch(() => {})));
        previous.hidden = true;
        previous.style.position = '';
        previous.style.inset = '';
        previous.style.width = '';
        stage.style.height = '';
        heightMotion.cancel();
        exit.cancel();
        enter.cancel();
        moving = false;
        following.querySelector('h2')?.focus({ preventScroll: true });
    };

    const error = form.querySelector('[data-leave-error]');
    const validateDetails = () => {
        error.hidden = true;
        if (!form.checkValidity()) {
            const invalid = form.querySelector(':invalid');
            if (invalid?.closest('[data-leave-more]')) form.querySelector('[data-leave-more]').open = true;
            invalid?.reportValidity();
            return false;
        }
        if (endInput.value < startInput.value) {
            error.textContent = 'La date de fin doit être postérieure ou égale à la date de début.';
            error.hidden = false;
            endInput.focus();
            return false;
        }
        return true;
    };

    form.classList.add('is-enhanced');
    form.noValidate = true;
    form.querySelectorAll('.leave-wizard-view').forEach((view) => { view.hidden = view.dataset.leaveStep !== current; });
    form.querySelectorAll('.leave-wizard-view h2').forEach((heading) => { heading.tabIndex = -1; });
    updateChoice();
    sync();
    updatePath();
    typeInput.addEventListener('change', updateChoice);

    const selectType = (slug) => {
        const value = slugToOption[slug]?.value ?? '';
        if (typeInput.value !== value) document.getElementById('leave_attachments').value = '';
        typeInput.value = value;
        sync();
    };
    form.querySelectorAll('[data-leave-category]').forEach((button) => button.addEventListener('click', () => {
        if (moving) return;
        category = button.dataset.leaveCategory;
        if (category === 'annual' || category === 'other') {
            selectType(category === 'annual' ? 'annual_leave' : 'other_absence');
        } else {
            selectType('');
        }
        button.classList.add('is-selected');
        window.setTimeout(() => {
            button.classList.remove('is-selected');
            showStep(category === 'health' || category === 'family' ? 'clarify' : 'details');
        }, 130);
    }));
    form.querySelectorAll('[data-leave-kind]').forEach((button) => button.addEventListener('click', () => {
        if (moving) return;
        selectType(button.dataset.leaveKind);
        button.classList.add('is-selected');
        window.setTimeout(() => {
            button.classList.remove('is-selected');
            showStep('details');
        }, 130);
    }));
    form.querySelectorAll('[data-leave-back]').forEach((button) => button.addEventListener('click', () => {
        const target = button.dataset.leaveBack === 'choice' && current === 'details' && ['health', 'family'].includes(category) ? 'clarify' : button.dataset.leaveBack;
        showStep(target);
    }));
    form.querySelector('[data-leave-next="review"]').addEventListener('click', () => {
        if (validateDetails()) showStep('review');
    });
    form.addEventListener('submit', (event) => {
        if (current !== 'review' || !form.checkValidity() || endInput.value < startInput.value) {
            event.preventDefault();
            if (current !== 'details') showStep('details').then(() => validateDetails());
            else validateDetails();
        }
    });
};
