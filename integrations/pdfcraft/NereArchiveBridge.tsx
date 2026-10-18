'use client';

import { useEffect, useState } from 'react';
import { usePathname } from 'next/navigation';

type NereSession = { csrfToken: string; name: string };

let sessionPromise: Promise<NereSession> | null = null;
let operationId = crypto.randomUUID();
let pendingSources: Promise<void> = Promise.resolve();

function toolSlug(): string {
  const segments = window.location.pathname.split('/').filter(Boolean);
  const toolIndex = segments.indexOf('tools');
  return toolIndex >= 0 && segments[toolIndex + 1] !== 'category'
    ? segments[toolIndex + 1] || 'pdfcraft'
    : segments.includes('workflow') ? 'workflow' : segments.includes('studio') ? 'studio' : 'pdfcraft';
}

async function session(): Promise<NereSession> {
  sessionPromise ??= fetch('/pdfcraft/api/session', { credentials: 'same-origin', cache: 'no-store' })
    .then(async response => {
      if (!response.ok) throw new Error('Votre session Néré Tools a expiré. Reconnectez-vous.');
      return response.json() as Promise<NereSession>;
    }).catch(error => { sessionPromise = null; throw error; });
  return sessionPromise;
}

async function archive(file: Blob, filename: string, kind: 'source' | 'result'): Promise<void> {
  const userSession = await session();
  const form = new FormData();
  form.append('file', file, filename);
  form.append('operation_id', operationId);
  form.append('tool_slug', toolSlug());
  form.append('kind', kind);

  const response = await fetch('/pdfcraft/api/files', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-TOKEN': userSession.csrfToken, Accept: 'application/json' },
    body: form,
  });

  if (!response.ok) {
    throw new Error(response.status === 413 || response.status === 422
      ? 'Ce fichier dépasse la taille autorisée pour l’archivage Néré Tools.'
      : 'L’archivage Néré Tools a échoué. Réessayez avant de télécharger.');
  }
}

export function NereArchiveBridge() {
  const pathname = usePathname();
  const [message, setMessage] = useState('');
  const [error, setError] = useState(false);

  useEffect(() => { operationId = crypto.randomUUID(); }, [pathname]);

  useEffect(() => {
    const blobs = new Map<string, Blob>();
    const createObjectURL = URL.createObjectURL.bind(URL);
    const revokeObjectURL = URL.revokeObjectURL.bind(URL);

    URL.createObjectURL = (object: Blob | MediaSource) => {
      const url = createObjectURL(object);
      if (object instanceof Blob) blobs.set(url, object);
      return url;
    };
    URL.revokeObjectURL = (url: string) => {
      blobs.delete(url);
      revokeObjectURL(url);
    };

    const archiveSources = (files: FileList | File[] | null | undefined) => {
      if (!files?.length) return;
      const selected = Array.from(files);
      setError(false);
      setMessage(`Archivage de ${selected.length} fichier(s) source…`);
      pendingSources = pendingSources.catch(() => {}).then(async () => {
        await Promise.all(selected.map(file => archive(file, file.name, 'source')));
        setMessage('Fichiers sources enregistrés dans Néré Tools.');
      }).catch(reason => {
        setError(true);
        setMessage(reason instanceof Error ? reason.message : 'Archivage impossible.');
        throw reason;
      });
    };

    const onChange = (event: Event) => {
      const input = event.target;
      if (input instanceof HTMLInputElement && input.type === 'file') archiveSources(input.files);
    };
    const onDrop = (event: DragEvent) => archiveSources(event.dataTransfer?.files);
    const onPaste = (event: ClipboardEvent) => archiveSources(event.clipboardData?.files);

    const onClick = (event: MouseEvent) => {
      const target = event.target;
      const link = target instanceof Element ? target.closest('a[download]') : null;
      if (!(link instanceof HTMLAnchorElement) || link.dataset.nereArchiveBypass === 'true') return;
      if (!link.href.startsWith('blob:') && !link.href.startsWith('data:')) return;
      const blob = blobs.get(link.href);

      event.preventDefault();
      event.stopImmediatePropagation();
      const filename = link.download || 'document.pdf';
      setError(false);
      setMessage('Archivage du résultat dans Néré Tools…');

      void (async () => {
        let result: Blob;
        if (blob) {
          result = blob;
        } else {
          const response = await fetch(link.href);
          result = await response.blob();
        }
        await pendingSources;
        await archive(result, filename, 'result');
        const replayUrl = createObjectURL(result);
        const replay = document.createElement('a');
        replay.href = replayUrl;
        replay.download = filename;
        replay.dataset.nereArchiveBypass = 'true';
        document.body.appendChild(replay);
        replay.click();
        replay.remove();
        setTimeout(() => revokeObjectURL(replayUrl), 60_000);
        setMessage('Résultat enregistré. Disponible dans Mes documents.');
      })().catch(reason => {
        setError(true);
        setMessage(reason instanceof Error ? reason.message : 'Archivage impossible.');
      });
    };

    document.addEventListener('change', onChange, true);
    document.addEventListener('drop', onDrop, true);
    document.addEventListener('paste', onPaste, true);
    document.addEventListener('click', onClick, true);
    return () => {
      document.removeEventListener('change', onChange, true);
      document.removeEventListener('drop', onDrop, true);
      document.removeEventListener('paste', onPaste, true);
      document.removeEventListener('click', onClick, true);
      URL.createObjectURL = createObjectURL;
      URL.revokeObjectURL = revokeObjectURL;
      blobs.clear();
    };
  }, []);

  return message ? <div role={error ? 'alert' : 'status'} className={`nere-archive-status ${error ? 'is-error' : ''}`}>{message}</div> : null;
}
