export function bootSignaturePage() {
    document.querySelectorAll('[data-signature-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('[data-pending-label]');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = button.dataset.pendingLabel;
        });
        window.addEventListener('pageshow', (event) => { if (event.persisted) window.location.reload(); });
    });
    const preview = document.querySelector('[data-pdf-preview]');
    if (preview) loadPreview(preview);
}

async function loadPreview(preview) {
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
                status.textContent = 'Aperçu de lecture. Vous pouvez aussi télécharger le PDF pour le consulter dans votre lecteur.';
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
        surface.setAttribute('aria-busy', 'false');
        label.textContent = 'Aperçu indisponible';
        status.textContent = 'L’aperçu ne peut pas être affiché. Téléchargez l’original pour le relire avant de signer.';
        previous.disabled = next.disabled = true;
    }
}
