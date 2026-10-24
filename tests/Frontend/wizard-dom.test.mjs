import { after, before, test } from 'node:test';
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { JSDOM, VirtualConsole } from 'jsdom';
import { createServer } from 'vite';
import { bootSignaturePage } from '../../resources/js/modules/signatures.js';

const pages = JSON.parse(execFileSync('php', ['tests/Frontend/render-wizard-pages.php'], { encoding: 'utf8', maxBuffer: 4 * 1024 * 1024 }));
let server;
let active;
let sequence = 0;
before(async () => { server = await createServer({ configFile: false, server: { middlewareMode: true, hmr: false, watch: null }, appType: 'custom' }); });
after(async () => { active?.window.close(); await server?.close(); });
const tick = () => new Promise(resolve => setTimeout(resolve, 25));
function page(name) {
    active?.window.close();
    active = new JSDOM(pages[name], { url: 'https://tools.example.test/signatures/test', pretendToBeVisual: true, virtualConsole: new VirtualConsole() });
    for (const key of ['window', 'document', 'Event', 'CustomEvent', 'FormData']) globalThis[key] = active.window[key];
    globalThis.matchMedia = active.window.matchMedia = () => ({ matches: true });
    active.window.HTMLElement.prototype.scrollIntoView = () => {};
    // PDF rendering is covered by browser QA; these tests cover the real form DOM.
    document.querySelector('[data-pdf-preview]')?.removeAttribute('data-pdf-preview');
    return document;
}

test('the app binds the wizard even when its script arrives after DOMContentLoaded', async () => {
    page('signatureReview');
    await tick();
    assert.equal(document.readyState, 'complete');
    await server.ssrLoadModule(`/resources/js/app.js?dom-test=${++sequence}`);
    const next = document.querySelector('[data-signature-next]');
    assert.equal(next.hidden, false, 'The continue button must be available after late script loading');
    next.click();
    assert.equal(document.querySelector('[data-signature-step="3"]').hidden, false);
});

test('review and confirmation steps are real navigation controls and back clears consent only', () => {
    page('signatureReview');
    bootSignaturePage();
    const step = document.querySelector('[data-signature-indicator="3"]');
    const go = step.querySelector('button, a');
    assert.ok(go, 'An available step must have a navigation control');
    go.click();
    assert.equal(document.querySelector('[data-signature-step="3"]').hidden, false);
    const reason = document.getElementById('reason');
    reason.value = 'Accord après relecture';
    document.getElementById('consent').checked = true;
    document.querySelector('[data-signature-back]').click();
    assert.equal(document.querySelector('[data-signature-step="2"]').hidden, false);
    assert.equal(reason.value, 'Accord après relecture');
    assert.equal(document.getElementById('consent').checked, false);
});

test('continue sends the selected PDF once and a rejected import can be corrected', async () => {
    page('signatureImport');
    bootSignaturePage();
    const input = document.getElementById('pdf');
    Object.defineProperty(input, 'files', { configurable: true, value: [new File(['%PDF-1.7\nexample'], 'Document.pdf')] });
    input.dispatchEvent(new Event('change', { bubbles: true }));
    let calls = 0;
    globalThis.fetch = async (_url, options) => {
        calls++;
        assert.equal(options.method, 'POST');
        return new Response(JSON.stringify({ errors: { pdf: ['Ce PDF doit être exporté à nouveau.'] } }), { status: 422, headers: { 'Content-Type': 'application/json' } });
    };
    const submit = document.querySelector('[data-pending-label]');
    submit.click();
    submit.click();
    await tick();
    assert.equal(calls, 1);
    assert.equal(submit.disabled, false);
    assert.match(document.querySelector('[data-field-error="pdf"]').textContent, /exporté à nouveau/);
    assert.equal(document.querySelector('[data-toast]').getAttribute('role'), 'alert');
    document.querySelector('[data-toast-close]').click();
    assert.equal(document.querySelector('[data-toast]'), null);
});

test('signing requires consent and a lost response is checked before another attempt', async () => {
    page('signatureReview');
    bootSignaturePage();
    document.querySelector('[data-signature-next]').click();
    const submit = document.querySelector('[data-pending-label]');
    let calls = 0;
    globalThis.fetch = async () => { calls++; throw new TypeError('offline'); };
    submit.click();
    await tick();
    assert.equal(calls, 0);
    assert.equal(document.getElementById('consent').getAttribute('aria-invalid'), 'true');
    document.getElementById('reason').value = 'Bon pour accord';
    document.getElementById('consent').checked = true;
    document.getElementById('consent').dispatchEvent(new Event('change', { bubbles: true }));
    submit.click();
    await tick();
    assert.equal(calls, 1);
    assert.equal(submit.disabled, true);
    assert.match(document.querySelector('[data-form-error]').textContent, /peut-être été signé/);
    globalThis.fetch = async (_url, options) => {
        assert.notEqual(options.method, 'POST');
        return Response.json({ signed: false, integrity_ok: true });
    };
    document.querySelector('.sig-recovery').click();
    await tick();
    assert.equal(submit.disabled, false);
    assert.equal(document.getElementById('reason').value, 'Bon pour accord');
    assert.equal(document.getElementById('consent').checked, false);
});

test('an unavailable certificate leaves review and recovery usable without a signing form', () => {
    page('signatureUnavailable');
    assert.doesNotThrow(() => bootSignaturePage());
    document.querySelector('[data-signature-next]').click();
    assert.equal(document.querySelector('[data-signature-step="3"]').hidden, false);
    assert.equal(document.querySelector('[data-signature-form="sign"]'), null);
    assert.equal(document.querySelector('[data-signature-reload]').disabled, false);
});

test('the leave wizard can reach review with the actual Blade fields', async () => {
    page('leaves');
    const { bootLeavePage } = await server.ssrLoadModule('/resources/js/modules/leaves.js');
    bootLeavePage();
    document.querySelector('[data-leave-category="annual"]').click();
    await new Promise(resolve => setTimeout(resolve, 160));
    assert.equal(document.querySelector('[data-leave-step="details"]').hidden, false);
    for (const [id, value] of [['leave_start_date', '2026-11-02'], ['leave_end_date', '2026-11-03']]) {
        document.getElementById(id).value = value;
        document.getElementById(id).dispatchEvent(new Event('change', { bubbles: true }));
    }
    document.querySelector('[data-leave-next="review"]').click();
    await tick();
    assert.equal(document.querySelector('[data-leave-step="review"]').hidden, false);
});
