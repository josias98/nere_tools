export function createNotifications(container, template) {
    const show = ({ title, message, type = 'info', action, key = message }) => {
        if (!container || !template) return;
        const existing = [...container.children].find((item) => item.dataset.key === key);
        if (existing) return;
        while (container.children.length >= 2) container.firstElementChild.dismissNotification();
        const toast = template.content.firstElementChild.cloneNode(true);
        const origin = document.activeElement;
        toast.dataset.key = key;
        toast.dataset.type = type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.querySelector('[data-toast-title]').textContent = title;
        toast.querySelector('[data-toast-message]').textContent = message;
        toast.querySelector('[data-toast-symbol]').textContent = { error: '!', success: '✓', info: 'i' }[type] || 'i';
        let timer;
        const pause = () => clearTimeout(timer);
        const dismiss = () => {
            pause();
            if (toast.contains(document.activeElement) && origin?.isConnected) origin.focus();
            toast.remove();
        };
        toast.dismissNotification = dismiss;
        const resume = () => {
            pause();
            if (type !== 'error' && !toast.matches(':hover') && !toast.contains(document.activeElement)) timer = setTimeout(dismiss, 7000);
        };
        toast.querySelector('[data-toast-close]').addEventListener('click', dismiss);
        toast.addEventListener('mouseenter', pause);
        toast.addEventListener('mouseleave', resume);
        toast.addEventListener('focusin', pause);
        toast.addEventListener('focusout', () => setTimeout(resume, 0));
        toast.addEventListener('keydown', (event) => { if (event.key === 'Escape') dismiss(); });
        if (action) {
            const button = toast.querySelector('[data-toast-action]');
            button.hidden = false;
            button.textContent = action.label;
            button.addEventListener('click', () => { dismiss(); action.run(); });
        }
        container.append(toast);
        document.dispatchEvent(new CustomEvent('nere:icons-refresh'));
        resume();
    };
    show.dismiss = (key) => {
        [...(container?.children || [])].filter((item) => item.dataset.key === key).forEach((item) => item.dismissNotification());
    };
    return show;
}
