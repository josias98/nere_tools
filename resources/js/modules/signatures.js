import { createNotifications } from './notifications.js';

export async function pdfFileError(file) {
    if (!file) return 'Choisissez un PDF sur votre appareil pour continuer.';
    if (!file.size) return 'Ce fichier est vide. Choisissez le PDF contenant votre document.';
    if (file.size > 10 * 1024 * 1024) return 'Ce PDF dépasse 10 Mo. Exportez une version plus légère, puis réessayez.';
    if (!/\.pdf$/i.test(file.name)) return 'Ce format n’est pas accepté. Exportez votre document en PDF, puis sélectionnez-le.';
    try {
        const header = new TextDecoder().decode(await file.slice(0, 5).arrayBuffer());
        if (header !== '%PDF-') return 'Ce fichier ne semble pas être un PDF lisible. Exportez une nouvelle copie PDF.';
    } catch {
        return 'Ce fichier ne peut pas être lu sur votre appareil. Sélectionnez-le à nouveau.';
    }
    return null;
}

export function signatureFieldErrors(reason, consent) {
    const errors = {};
    if (!reason.trim()) errors.reason = ['Indiquez le motif de votre signature, par exemple « Bon pour accord ».'];
    else if ([...reason].length > 500) errors.reason = ['Le motif est trop long. Limitez-le à 500 caractères.'];
    if (!consent) errors.consent = ['Cochez la confirmation pour autoriser la signature de ce PDF.'];
    return errors;
}

export function requestProblem(status, kind, retryAfter) {
    if (status === 401 || status === 419) return {
        title: 'Votre session a expiré', action: status === 401 ? 'login' : 'reload',
        message: kind === 'import' ? 'Reconnectez-vous ou actualisez la page, puis sélectionnez à nouveau votre PDF.' : 'Votre document est conservé. Renouvelez votre session avant de confirmer votre accord.',
    };
    if (status === 403) return { title: 'Accès non autorisé', message: 'Votre compte n’a plus accès à cette action. Demandez à un administrateur de vérifier vos droits.', action: 'reload' };
    if (status === 404) return { title: 'Document introuvable', message: 'Ce document n’est plus disponible à cette adresse. Actualisez la page pour vérifier son état.', action: 'reload' };
    if (status === 413) return { title: 'PDF trop volumineux', message: 'Le serveur refuse la taille de ce fichier. Choisissez une version PDF plus légère.' };
    if (status === 429) {
        const seconds = Math.min(3600, Math.max(1, parseInt(retryAfter, 10) || 60));
        return { title: 'Un instant avant de réessayer', message: `Plusieurs tentatives ont été faites. Patientez ${seconds} secondes, puis réessayez.` };
    }
    if (kind === 'sign') return { title: 'Résultat à vérifier', message: 'La confirmation du serveur n’a pas été reçue. Votre PDF a peut-être été signé. Vérifiez son état avant de réessayer.', action: 'check' };
    return { title: status === 0 ? 'Connexion interrompue' : 'Import momentanément indisponible', message: 'Votre fichier reste sélectionné. Vérifiez votre connexion, puis réessayez.', action: 'retry' };
}

export function sameOriginRedirect(target, base) {
    if (typeof target !== 'string' || !target) return null;
    try {
        const url = new URL(target, base);
        return url.origin === new URL(base).origin && ['http:', 'https:'].includes(url.protocol) ? url.href : null;
    } catch { return null; }
}

export function bootSignaturePage() {
    const root = document.querySelector('[data-signature-wizard]');
    if (!root) return;
    const notify = createNotifications(root.querySelector('[data-notifications]'), root.querySelector('[data-notification-template]'));
    const preview = root.querySelector('[data-pdf-preview]');
    let previewStarted = false;
    const showStep = (step, focus = true) => {
        if (root.getAttribute('aria-busy') === 'true') return;
        const stage = root.querySelector(`[data-signature-step="${step}"]`);
        if (!stage) return;
        if (Number(root.dataset.currentStep) === 3 && step !== 3) {
            const consent = root.querySelector('[name="consent"]');
            if (consent) consent.checked = false;
        }
        root.dataset.currentStep = String(step);
        root.querySelectorAll('[data-signature-step]').forEach((item) => { item.hidden = item !== stage; });
        root.querySelectorAll('[data-signature-indicator]').forEach((item) => {
            const number = Number(item.dataset.signatureIndicator);
            item.classList.toggle('is-current', number === step);
            item.classList.toggle('is-done', number < step);
            if (number === step) item.setAttribute('aria-current', 'step');
            else item.removeAttribute('aria-current');
        });
        root.querySelector('[data-signature-progress]').style.width = `${step * 25}%`;
        root.querySelector('[role="progressbar"]').setAttribute('aria-valuenow', step);
        if (focus) {
            stage.querySelector('h2')?.focus({ preventScroll: true });
            root.querySelector('.sig-wizard-shell').scrollIntoView({ block: 'start', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
            if (!matchMedia('(prefers-reduced-motion: reduce)').matches) stage.animate([{ opacity: .3, transform: 'translateY(8px)' }, { opacity: 1, transform: 'translateY(0)' }], { duration: 180, easing: 'ease-out' });
        }
        if (step === 2 && preview && !previewStarted) { previewStarted = true; loadPreview(preview, notify); }
    };
    root.classList.add('is-enhanced');
    root.querySelectorAll('[data-signature-next], [data-signature-back]').forEach((button) => { button.hidden = false; });
    root.querySelector('[data-signature-next]')?.addEventListener('click', () => showStep(3));
    root.querySelectorAll('[data-signature-back]').forEach((button) => button.addEventListener('click', () => {
        const consent = root.querySelector('[name="consent"]');
        if (consent) consent.checked = false;
        showStep(2);
    }));
    root.querySelectorAll('[data-signature-go]').forEach((link) => link.addEventListener('click', (event) => {
        event.preventDefault();
        showStep(Number(link.dataset.signatureGo));
    }));
    root.querySelectorAll('[data-signature-reload]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
    showStep(Number(root.dataset.currentStep), false);
    root.querySelectorAll('[data-signature-form]').forEach((form) => bootForm(form, root, notify, showStep));
    const error = root.querySelector('[data-form-error]:not([hidden])');
    if (error?.textContent.trim()) notify({ title: 'Une correction est nécessaire', message: error.textContent.trim(), type: 'error' });
}

function displayErrors(form, errors) {
    form.querySelectorAll('[data-field-error]').forEach((item) => { item.textContent = ''; item.hidden = true; });
    form.querySelectorAll('[aria-invalid]').forEach((item) => item.removeAttribute('aria-invalid'));
    for (const [name, messages] of Object.entries(errors)) {
        const field = form.elements.namedItem(name);
        const output = [...form.querySelectorAll('[data-field-error]')].find((item) => item.dataset.fieldError === name);
        if (!output || !field) continue;
        output.textContent = messages[0];
        output.hidden = false;
        field.setAttribute('aria-invalid', 'true');
    }
    const summary = form.querySelector('[data-form-error]');
    summary.textContent = Object.values(errors).flat()[0] || '';
    summary.hidden = !summary.textContent;
    form.querySelector('[data-signature-dropzone]')?.classList.toggle('has-error', Boolean(errors.pdf));
}

function bootForm(form, root, notify, showStep) {
    const kind = form.dataset.signatureForm;
    const button = form.querySelector('[data-pending-label]');
    const idleNodes = [...button.childNodes];
    const recovery = document.createElement('button');
    recovery.type = 'button';
    recovery.className = 'nc-ghost sig-recovery';
    recovery.hidden = true;
    form.querySelector('[data-form-error]').after(recovery);
    let pending = false;
    let uncertain = false;
    let recover;
    let controls = [];
    recovery.addEventListener('click', () => recover?.());
    const busy = (value) => {
        root.setAttribute('aria-busy', String(value));
        root.querySelectorAll('[data-signature-go]').forEach((link) => {
            if (value) link.setAttribute('aria-disabled', 'true');
            else link.removeAttribute('aria-disabled');
        });
        form.querySelector('[data-signature-busy]').hidden = !value;
        if (value) {
            controls = [...form.querySelectorAll('button, input:not([type="hidden"]), textarea')].map((control) => [control, control.disabled]);
            controls.forEach(([control]) => { control.disabled = true; });
            button.textContent = button.dataset.pendingLabel;
            button.setAttribute('aria-busy', 'true');
        } else {
            controls.forEach(([control, disabled]) => { control.disabled = disabled; });
            button.replaceChildren(...idleNodes);
            button.removeAttribute('aria-busy');
            if (uncertain) button.disabled = true;
        }
    };
    const problem = (status, retryAfter) => {
        const issue = requestProblem(status, kind, retryAfter);
        const actions = {
            reload: { label: 'Actualiser la page', run: () => window.location.reload() },
            check: { label: 'Vérifier le document', run: checkStatus },
            login: { label: 'Se reconnecter', run: () => window.location.assign(form.dataset.loginUrl) },
            retry: { label: 'Réessayer l’import', run: () => form.requestSubmit() },
        };
        const action = actions[issue.action];
        displayErrors(form, { request: [issue.message] });
        uncertain = ['reload', 'check', 'login'].includes(issue.action);
        recovery.hidden = !action;
        recovery.textContent = action?.label || '';
        recover = action?.run;
        notify.dismiss('signature-request');
        notify({ ...issue, type: 'error', action, key: 'signature-request' });
    };
    async function checkStatus() {
        if (pending) return;
        pending = true;
        recovery.disabled = true;
        recovery.textContent = 'Vérification en cours…';
        const abort = new AbortController();
        const timeout = setTimeout(() => abort.abort(), 15000);
        try {
            const response = await fetch(window.location.href, { headers: { Accept: 'application/json' }, credentials: 'same-origin', cache: 'no-store', signal: abort.signal });
            const data = await response.json().catch(() => null);
            if (!response.ok || typeof data?.signed !== 'boolean') { problem(response.status); return; }
            if (data.signed || !data.integrity_ok) {
                const redirect = sameOriginRedirect(data.redirect_url, window.location.href);
                if (redirect) { window.location.assign(redirect); return; }
                problem(0);
                return;
            }
            uncertain = false;
            notify.dismiss('signature-request');
            form.elements.namedItem('consent').checked = false;
            button.disabled = false;
            recovery.hidden = true;
            displayErrors(form, {});
            notify({ title: 'Votre document est toujours à signer', message: 'Aucune signature n’est enregistrée pour le moment. Votre motif est conservé ; confirmez à nouveau votre accord pour poursuivre.', type: 'info' });
            form.elements.namedItem('consent').focus();
        } catch { problem(0); }
        finally { clearTimeout(timeout); recovery.disabled = false; pending = false; }
    }
    const reason = form.elements.namedItem('reason');
    if (reason) {
        const updateReasons = () => form.querySelectorAll('[data-signature-reason]').forEach((item) => item.setAttribute('aria-pressed', String(reason.value.trim() === item.dataset.signatureReason)));
        form.querySelectorAll('[data-signature-reason]').forEach((item) => item.addEventListener('click', () => { reason.value = item.dataset.signatureReason; reason.dispatchEvent(new Event('input', { bubbles: true })); }));
        reason.addEventListener('input', updateReasons);
        updateReasons();
        for (const field of [reason, form.elements.namedItem('consent')]) {
            const check = () => {
                if (!field.hasAttribute('aria-invalid')) return;
                const errors = signatureFieldErrors(reason.value, form.elements.namedItem('consent').checked);
                if (!errors[field.name]) {
                    const output = form.querySelector(`[data-field-error="${field.name}"]`);
                    const previousMessage = output.textContent;
                    field.removeAttribute('aria-invalid');
                    output.hidden = true;
                    const summary = form.querySelector('[data-form-error]');
                    if (summary.textContent === previousMessage) {
                        summary.textContent = form.querySelector('[data-field-error]:not([hidden])')?.textContent || '';
                        summary.hidden = !summary.textContent;
                    }
                    if (!Object.keys(errors).length) notify.dismiss('signature-validation');
                }
            };
            field.addEventListener('input', check);
            field.addEventListener('change', check);
        }
    }
    if (kind === 'import') bootFilePicker(form, notify);
    form.noValidate = true;
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (pending || uncertain) return;
        notify.dismiss('signature-validation');
        notify.dismiss('signature-request');
        pending = true;
        recovery.hidden = true;
        const errors = kind === 'import'
            ? { pdf: [await pdfFileError(form.elements.namedItem('pdf').files[0])] }
            : signatureFieldErrors(reason.value, form.elements.namedItem('consent').checked);
        if (errors.pdf?.[0] === null) delete errors.pdf;
        displayErrors(form, errors);
        if (Object.keys(errors).length) {
            pending = false;
            notify({ title: kind === 'import' ? 'Choisissez un PDF valide' : 'Votre accord est nécessaire', message: Object.values(errors)[0][0], type: 'error', key: 'signature-validation' });
            form.querySelector('[aria-invalid]')?.focus();
            return;
        }
        const payload = new FormData(form);
        const abort = new AbortController();
        const timeout = setTimeout(() => abort.abort(), 45000);
        let focusAfter = false;
        busy(true);
        try {
            const response = await fetch(form.action, { method: 'POST', body: payload, headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', signal: abort.signal });
            const data = response.headers.get('content-type')?.includes('application/json') ? await response.json().catch(() => null) : null;
            const redirect = response.ok ? sameOriginRedirect(data?.redirect_url, window.location.href) : null;
            if (redirect) { window.location.assign(redirect); return; }
            if (response.status === 422 && data?.errors && typeof data.errors === 'object') {
                const messages = Object.fromEntries(Object.entries(data.errors).filter(([, values]) => Array.isArray(values) && values.length && typeof values[0] === 'string'));
                if (!Object.keys(messages).length) { problem(422); return; }
                displayErrors(form, messages);
                if (kind === 'sign') showStep(3);
                notify({ title: kind === 'import' ? 'Ce PDF ne peut pas être importé' : 'La signature n’a pas été effectuée', message: Object.values(messages)[0][0], type: 'error', key: 'signature-validation' });
                focusAfter = true;
            } else problem(response.status, response.headers.get('Retry-After'));
        } catch { problem(0); }
        finally {
            clearTimeout(timeout); busy(false); pending = false;
            if (focusAfter) form.querySelector('[aria-invalid]:not([type="hidden"])')?.focus();
        }
    });
    window.addEventListener('pageshow', (event) => { if (event.persisted && kind === 'sign') window.location.reload(); });
}

function bootFilePicker(form, notify) {
    const input = form.elements.namedItem('pdf');
    const zone = form.querySelector('[data-signature-dropzone]');
    const selected = form.querySelector('[data-signature-selected]');
    let version = 0;
    const refresh = async () => {
        const current = ++version;
        const file = input.files[0];
        const error = file ? await pdfFileError(file) : null;
        if (current !== version) return;
        selected.hidden = !file;
        if (file) {
            selected.querySelector('[data-selected-name]').textContent = file.name;
            selected.querySelector('[data-selected-size]').textContent = file.size >= 1048576 ? `${(file.size / 1048576).toFixed(1)} Mo` : `${Math.ceil(file.size / 1024)} Ko`;
        }
        form.querySelector('[data-file-picker-label]').textContent = file ? 'Changer de PDF' : 'Choisir un PDF';
        displayErrors(form, error ? { pdf: [error] } : {});
        notify.dismiss('signature-validation');
        if (error) notify({ title: 'Vérifiez le fichier choisi', message: error, type: 'error', key: 'signature-validation' });
    };
    input.addEventListener('change', refresh);
    form.querySelector('[data-signature-clear]').addEventListener('click', () => { input.value = ''; refresh(); input.focus(); });
    zone.addEventListener('dragover', (event) => { event.preventDefault(); if (!input.disabled) zone.classList.add('is-dragging'); });
    zone.addEventListener('dragleave', (event) => { if (!zone.contains(event.relatedTarget)) zone.classList.remove('is-dragging'); });
    zone.addEventListener('drop', (event) => {
        event.preventDefault();
        zone.classList.remove('is-dragging');
        if (input.disabled) return;
        if (event.dataTransfer.files.length !== 1) { notify({ title: 'Un document à la fois', message: 'Déposez un seul PDF pour commencer sa signature.', type: 'error' }); return; }
        input.files = event.dataTransfer.files;
        refresh();
    });
}

async function loadPreview(preview, notify) {
    const status = preview.querySelector('[data-pdf-status]');
    const label = preview.querySelector('[data-pdf-page]');
    const surface = preview.querySelector('[data-pdf-surface]');
    const canvas = preview.querySelector('canvas');
    const previous = preview.querySelector('[data-pdf-previous]');
    const next = preview.querySelector('[data-pdf-next]');
    try {
        const pdfjs = await import('pdfjs-dist');
        const { default: workerUrl } = await import('pdfjs-dist/build/pdf.worker.min.mjs?url');
        pdfjs.GlobalWorkerOptions.workerSrc = workerUrl;
        const pdf = await pdfjs.getDocument({
            url: preview.dataset.pdfUrl, withCredentials: true,
            isEvalSupported: false, enableXfa: false, maxImageSize: 16000000,
        }).promise;
        let current = 1;
        let rendering = false;
        const render = async () => {
            if (rendering) return;
            rendering = true;
            previous.disabled = next.disabled = true;
            surface.setAttribute('aria-busy', 'true');
            label.textContent = 'Chargement…';
            try {
                const page = await pdf.getPage(current);
                const natural = page.getViewport({ scale: 1 });
                const width = Math.min(900, Math.max(200, surface.clientWidth - 32));
                const scale = width / natural.width;
                const viewport = page.getViewport({ scale });
                const ratio = Math.min(window.devicePixelRatio || 1, 2);
                canvas.width = Math.floor(viewport.width * ratio);
                canvas.height = Math.floor(viewport.height * ratio);
                canvas.style.aspectRatio = `${viewport.width} / ${viewport.height}`;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport, transform: [ratio, 0, 0, ratio, 0, 0] }).promise;
                canvas.setAttribute('aria-label', `Aperçu du PDF, page ${current} sur ${pdf.numPages}. Le texte complet est accessible en téléchargeant le document.`);
                label.textContent = `Page ${current} sur ${pdf.numPages}`;
                status.hidden = true;
            } finally {
                rendering = false;
                surface.setAttribute('aria-busy', 'false');
                previous.disabled = current <= 1;
                next.disabled = current >= pdf.numPages;
            }
        };
        await render();
        previous.addEventListener('click', () => { if (!rendering && current > 1) { current--; render().catch(fail); } });
        next.addEventListener('click', () => { if (!rendering && current < pdf.numPages) { current++; render().catch(fail); } });
    } catch {
        fail();
    }
    function fail() {
        status.hidden = false;
        surface.setAttribute('aria-busy', 'false');
        label.textContent = 'Aperçu indisponible';
        status.textContent = 'L’aperçu ne peut pas être affiché. Téléchargez l’original pour le relire avant de signer.';
        previous.disabled = next.disabled = true;
        if (!preview.querySelector('[data-pdf-retry]')) {
            const retry = document.createElement('button');
            retry.type = 'button';
            retry.className = 'nc-ghost sig-preview-retry';
            retry.dataset.pdfRetry = '1';
            retry.textContent = 'Réessayer l’aperçu';
            retry.addEventListener('click', () => { retry.remove(); loadPreview(preview, notify); });
            status.after(retry);
        }
        notify({ title: 'L’aperçu est indisponible', message: 'Réessayez l’aperçu ou téléchargez l’original pour le relire. Votre document reste disponible.', type: 'error' });
    }
}
