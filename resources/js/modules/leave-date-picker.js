const dateFromIso = (value) => {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;
    const [year, month, day] = value.split('-').map(Number);
    const date = new Date(year, month - 1, day, 12);
    return date.getFullYear() === year && date.getMonth() === month - 1 && date.getDate() === day ? date : null;
};

const isoFromDate = (date) => `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
const dateLabel = (date) => new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);

export const bootLeaveDatePicker = ({ form, startInput, endInput, reducedMotion }) => {
    const panel = form.querySelector('[data-leave-date-calendar]');
    const triggers = [...form.querySelectorAll('[data-leave-date-trigger]')];
    if (!panel || triggers.length !== 2) return { close: () => {}, open: () => {} };

    const days = panel.querySelector('[data-leave-calendar-days]');
    const monthLabel = panel.querySelector('[data-leave-calendar-month]');
    const prompt = panel.querySelector('[data-leave-date-prompt]');
    const todayButton = panel.querySelector('[data-leave-calendar-today]');
    const inputs = { start: startInput, end: endInput };
    const today = new Date();
    let month = new Date(today.getFullYear(), today.getMonth(), 1);
    let active = 'start';
    let isOpen = false;
    let panelMotion;
    let monthMotion;

    const syncTriggers = () => {
        triggers.forEach((trigger) => {
            const input = inputs[trigger.dataset.leaveDateTrigger];
            const date = dateFromIso(input.value);
            const field = trigger.dataset.leaveDateTrigger === 'start' ? 'début' : 'fin';
            trigger.querySelector('[data-leave-date-value]').textContent = date ? dateLabel(date) : 'Choisir une date';
            trigger.setAttribute('aria-label', date ? `Date de ${field} : ${dateLabel(date)}. Modifier` : `Choisir la date de ${field}`);
            trigger.classList.toggle('has-date', Boolean(date));
            if (input.getAttribute('aria-invalid') === 'true') trigger.setAttribute('aria-invalid', 'true');
            else trigger.removeAttribute('aria-invalid');
            if (input.hasAttribute('aria-describedby')) trigger.setAttribute('aria-describedby', input.getAttribute('aria-describedby'));
            else trigger.removeAttribute('aria-describedby');
        });
        endInput.min = startInput.value;
    };

    const focusDay = (value) => {
        (days.querySelector(`[data-date="${value}"]`) ?? days.querySelector('button:not(:disabled)'))?.focus();
    };

    const render = () => {
        const year = month.getFullYear();
        const monthIndex = month.getMonth();
        const first = new Date(year, monthIndex, 1, 12);
        const offset = (first.getDay() + 6) % 7;
        const start = startInput.value;
        const end = endInput.value;
        const todayIso = isoFromDate(today);
        monthLabel.textContent = new Intl.DateTimeFormat('fr-FR', { month: 'long', year: 'numeric' }).format(first);
        prompt.textContent = active === 'start' ? 'Choisissez le début' : 'Choisissez la fin';
        todayButton.disabled = active === 'end' && Boolean(start) && todayIso < start;
        days.replaceChildren(...Array.from({ length: 42 }, (_, index) => {
            const date = new Date(year, monthIndex, index - offset + 1, 12);
            const iso = isoFromDate(date);
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.date = iso;
            button.textContent = String(date.getDate());
            button.setAttribute('aria-label', dateLabel(date));
            button.setAttribute('aria-pressed', String(iso === start || iso === end));
            if (iso === todayIso) button.setAttribute('aria-current', 'date');
            if (date.getMonth() !== monthIndex) button.classList.add('is-outside');
            if (start && end && iso > start && iso < end) button.classList.add('is-in-range');
            if (iso === start) button.classList.add('is-start');
            if (iso === end) button.classList.add('is-end');
            if (active === 'end' && start && iso < start) button.disabled = true;
            return button;
        }));
        triggers.forEach((trigger) => {
            trigger.setAttribute('aria-expanded', String(isOpen && trigger.dataset.leaveDateTrigger === active));
            trigger.classList.toggle('is-active', isOpen && trigger.dataset.leaveDateTrigger === active);
        });
    };

    const animatePanel = (keyframes, duration, onFinish = () => {}) => {
        panelMotion?.cancel();
        if (reducedMotion.matches || !panel.animate) { onFinish(); return; }
        panelMotion = panel.animate(keyframes, { duration, easing: 'cubic-bezier(.16,1,.3,1)' });
        panelMotion.finished.then(onFinish).catch(() => {});
    };

    const close = (restoreFocus = false) => {
        if (!isOpen) return;
        isOpen = false;
        const trigger = triggers.find((item) => item.dataset.leaveDateTrigger === active);
        render();
        animatePanel([
            { opacity: 1, transform: 'translateY(0) scale(1)' },
            { opacity: 0, transform: 'translateY(-8px) scale(.985)' },
        ], 170, () => { if (!isOpen) panel.hidden = true; });
        if (restoreFocus) trigger?.focus();
    };

    const open = (field) => {
        active = field === 'end' && !startInput.value ? 'start' : field;
        const selected = dateFromIso(inputs[active].value);
        const startDate = dateFromIso(startInput.value);
        const chosen = (active === 'end' && selected && startDate && selected < startDate ? null : selected)
            ?? startDate ?? today;
        month = new Date(chosen.getFullYear(), chosen.getMonth(), 1);
        const wasOpen = isOpen;
        isOpen = true;
        panel.hidden = false;
        render();
        if (!wasOpen) animatePanel([
            { opacity: 0, transform: 'translateY(-10px) scale(.985)' },
            { opacity: 1, transform: 'translateY(0) scale(1)' },
        ], 260);
        focusDay(isoFromDate(chosen));
    };

    const setValue = (input, value) => {
        if (input.value === value) return;
        input.value = value;
        input.removeAttribute('aria-invalid');
        input.closest('label')?.querySelector('.nc-error')?.remove();
        input.removeAttribute('aria-describedby');
        input.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const choose = (value) => {
        if (active === 'end' && startInput.value && value < startInput.value) return;
        if (active === 'start') {
            setValue(startInput, value);
            if (endInput.value && endInput.value < value) setValue(endInput, '');
            active = 'end';
            syncTriggers();
            render();
            focusDay(value);
            return;
        }
        setValue(endInput, value);
        syncTriggers();
        close(true);
    };

    const moveMonth = (offset) => {
        month = new Date(month.getFullYear(), month.getMonth() + offset, 1);
        monthMotion?.cancel();
        render();
        if (!reducedMotion.matches && days.animate) {
            monthMotion = days.animate([
                { opacity: 0, transform: `translateX(${offset > 0 ? 14 : -14}px)` },
                { opacity: 1, transform: 'translateX(0)' },
            ], { duration: 220, easing: 'cubic-bezier(.16,1,.3,1)' });
        }
    };

    triggers.forEach((trigger) => {
        const field = trigger.dataset.leaveDateTrigger;
        trigger.hidden = false;
        trigger.closest('.leave-date-field').querySelector('label').addEventListener('click', (event) => {
            event.preventDefault();
            open(field);
        });
        inputs[field].tabIndex = -1;
        inputs[field].setAttribute('aria-hidden', 'true');
        trigger.addEventListener('click', () => isOpen && active === field ? close(true) : open(field));
    });
    panel.querySelector('[data-leave-calendar-prev]').addEventListener('click', () => moveMonth(-1));
    panel.querySelector('[data-leave-calendar-next]').addEventListener('click', () => moveMonth(1));
    panel.querySelector('[data-leave-calendar-close]').addEventListener('click', () => close(true));
    todayButton.addEventListener('click', () => choose(isoFromDate(today)));
    panel.querySelector('[data-leave-calendar-clear]').addEventListener('click', () => {
        setValue(startInput, '');
        setValue(endInput, '');
        active = 'start';
        syncTriggers();
        render();
        focusDay(isoFromDate(today));
    });
    days.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-date]');
        if (button && !button.disabled) choose(button.dataset.date);
    });
    days.addEventListener('keydown', (event) => {
        const button = event.target.closest('button[data-date]');
        if (!button) return;
        const shift = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[event.key];
        if (shift === undefined && !['Home', 'End', 'PageUp', 'PageDown'].includes(event.key)) return;
        event.preventDefault();
        const date = dateFromIso(button.dataset.date);
        if (shift !== undefined) date.setDate(date.getDate() + shift);
        if (event.key === 'Home') date.setDate(date.getDate() - ((date.getDay() + 6) % 7));
        if (event.key === 'End') date.setDate(date.getDate() + (6 - ((date.getDay() + 6) % 7)));
        if (event.key === 'PageUp' || event.key === 'PageDown') {
            const targetMonth = new Date(date.getFullYear(), date.getMonth() + (event.key === 'PageUp' ? -1 : 1), 1);
            date.setFullYear(targetMonth.getFullYear(), targetMonth.getMonth(), Math.min(date.getDate(), new Date(targetMonth.getFullYear(), targetMonth.getMonth() + 1, 0).getDate()));
        }
        if (active === 'end' && startInput.value && isoFromDate(date) < startInput.value) return;
        month = new Date(date.getFullYear(), date.getMonth(), 1);
        render();
        focusDay(isoFromDate(date));
    });
    panel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') { event.preventDefault(); close(true); }
    });
    document.addEventListener('pointerdown', (event) => {
        if (isOpen && !panel.contains(event.target) && !triggers.some((trigger) => trigger.contains(event.target))) close();
    });
    startInput.addEventListener('change', syncTriggers);
    endInput.addEventListener('change', syncTriggers);
    syncTriggers();

    return { close, open };
};
