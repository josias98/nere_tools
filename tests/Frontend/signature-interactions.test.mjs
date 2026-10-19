import { test } from 'node:test';
import assert from 'node:assert/strict';
import { pdfFileError, requestProblem, sameOriginRedirect, signatureFieldErrors } from '../../resources/js/modules/signatures.js';

test('a PDF is accepted even when the browser cannot determine its MIME type', async () => {
    assert.equal(await pdfFileError(new File(['%PDF-1.7\ncontent'], 'Document.PDF', { type: '' })), null);
});

test('empty, oversized, and disguised PDF files receive actionable errors', async () => {
    assert.match(await pdfFileError(null), /Choisissez un PDF/);
    assert.match(await pdfFileError(new File([], 'empty.pdf')), /vide/);
    assert.match(await pdfFileError({ name: 'large.pdf', size: 10485761 }), /10 Mo/);
    assert.match(await pdfFileError(new File(['%PDF-1.7'], 'document.docx')), /Exportez votre document en PDF/);
    assert.match(await pdfFileError(new File(['<html>not a PDF'], 'document.pdf')), /nouvelle copie PDF/);
});

test('an unreadable local file does not crash the wizard', async () => {
    assert.match(await pdfFileError({ name: 'unreadable.pdf', size: 8, slice() { throw new Error('permission'); } }), /à nouveau/);
});

test('a signature requires both a meaningful reason and explicit consent', () => {
    assert.deepEqual(Object.keys(signatureFieldErrors('   ', false)), ['reason', 'consent']);
    assert.deepEqual(signatureFieldErrors('Bon pour accord', true), {});
    assert.deepEqual(Object.keys(signatureFieldErrors('Bon pour accord', false)), ['consent']);
    assert.deepEqual(Object.keys(signatureFieldErrors('a'.repeat(501), true)), ['reason']);
});

test('uncertain signing results require a status check rather than an automatic retry', () => {
    assert.equal(requestProblem(0, 'sign').action, 'check');
    assert.equal(requestProblem(500, 'sign').action, 'check');
    assert.equal(requestProblem(200, 'sign').action, 'check');
    assert.equal(requestProblem(0, 'import').action, 'retry');
});

test('expired sessions and rate limits provide specific recovery instructions', () => {
    assert.equal(requestProblem(401, 'sign').action, 'login');
    assert.equal(requestProblem(419, 'sign').action, 'reload');
    assert.match(requestProblem(429, 'sign', '17').message, /17 secondes/);
    assert.equal(requestProblem(429, 'sign', '17').action, undefined);
    assert.match(requestProblem(413, 'import').message, /plus légère/);
});

test('a server response cannot redirect the signing flow to another origin or script', () => {
    const origin = 'https://tools.example.test/signatures';
    assert.equal(sameOriginRedirect('/signatures/123', origin), 'https://tools.example.test/signatures/123');
    assert.equal(sameOriginRedirect('https://other.test/collect', origin), null);
    assert.equal(sameOriginRedirect('javascript:alert(1)', origin), null);
    assert.equal(sameOriginRedirect('//other.test/collect', origin), null);
    assert.equal(sameOriginRedirect(null, origin), null);
});
