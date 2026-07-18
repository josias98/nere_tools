import {
    arrow,
    autoUpdate,
    computePosition,
    flip,
    offset,
    shift,
} from '@floating-ui/dom';

export const bootTooltips = () => {
    const references = document.querySelectorAll('[data-tooltip]');
    if (!references.length) return;

    const tooltip = document.createElement('div');
    tooltip.id = 'nere-admin-tooltip';
    tooltip.className = 'nc-tooltip';
    tooltip.setAttribute('role', 'tooltip');

    const content = document.createElement('span');
    content.className = 'nc-tooltip-content';
    const title = document.createElement('strong');
    const copy = document.createElement('span');
    const pointer = document.createElement('span');
    pointer.className = 'nc-tooltip-arrow';
    content.append(title, copy);
    tooltip.append(content, pointer);

    let activeReference;
    let cleanupPosition;
    let showTimer;
    let hideTimer;
    let removeTimer;

    const clearTimers = () => {
        clearTimeout(showTimer);
        clearTimeout(hideTimer);
        clearTimeout(removeTimer);
    };

    const updatePosition = (reference) => computePosition(reference, tooltip, {
        placement: 'top',
        strategy: 'fixed',
        middleware: [offset(12), flip(), shift({ padding: 8 }), arrow({ element: pointer })],
    }).then(({ x, y, placement, middlewareData }) => {
        if (reference !== activeReference) return;
        Object.assign(tooltip.style, { left: `${x}px`, top: `${y}px` });
        tooltip.dataset.placement = placement.split('-')[0];

        const pointerPosition = middlewareData.arrow ?? {};
        const side = placement.split('-')[0];
        const staticSide = { top: 'bottom', right: 'left', bottom: 'top', left: 'right' }[side];
        Object.assign(pointer.style, { left: '', right: '', top: '', bottom: '' });
        if (pointerPosition.x != null) pointer.style.left = `${pointerPosition.x}px`;
        if (pointerPosition.y != null) pointer.style.top = `${pointerPosition.y}px`;
        pointer.style[staticSide] = '-5px';
    });

    const hide = () => {
        clearTimers();
        activeReference?.removeAttribute('aria-describedby');
        activeReference = undefined;
        cleanupPosition?.();
        cleanupPosition = undefined;
        tooltip.classList.remove('is-open');
        removeTimer = setTimeout(() => tooltip.remove(), 120);
    };

    const show = (reference) => {
        clearTimers();
        if (activeReference && activeReference !== reference) activeReference.removeAttribute('aria-describedby');
        cleanupPosition?.();

        activeReference = reference;
        title.textContent = reference.dataset.tooltipTitle ?? '';
        title.hidden = !title.textContent;
        copy.textContent = reference.dataset.tooltip;
        reference.setAttribute('aria-describedby', tooltip.id);
        document.body.append(tooltip);
        cleanupPosition = autoUpdate(reference, tooltip, () => updatePosition(reference));
        requestAnimationFrame(() => tooltip.classList.add('is-open'));
    };

    const scheduleShow = (reference) => {
        clearTimeout(hideTimer);
        showTimer = setTimeout(() => show(reference), 120);
    };
    const scheduleHide = () => {
        clearTimeout(showTimer);
        hideTimer = setTimeout(hide, 80);
    };

    references.forEach((reference) => {
        let touchTimer;
        let touchShown = false;
        reference.addEventListener('mouseenter', () => scheduleShow(reference));
        reference.addEventListener('mouseleave', scheduleHide);
        reference.addEventListener('focus', () => show(reference));
        reference.addEventListener('blur', scheduleHide);
        reference.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') hide();
        });
        reference.addEventListener('pointerdown', (event) => {
            if (event.pointerType === 'touch') {
                touchTimer = setTimeout(() => {
                    touchShown = true;
                    show(reference);
                }, 350);
            }
        });
        reference.addEventListener('pointerup', () => clearTimeout(touchTimer));
        reference.addEventListener('pointercancel', () => clearTimeout(touchTimer));
        reference.addEventListener('click', (event) => {
            if (!touchShown) return;
            event.preventDefault();
            touchShown = false;
        });
    });

    tooltip.addEventListener('mouseenter', () => clearTimeout(hideTimer));
    tooltip.addEventListener('mouseleave', scheduleHide);
    document.addEventListener('pointerdown', (event) => {
        if (activeReference && !activeReference.contains(event.target) && !tooltip.contains(event.target)) hide();
    });
};
