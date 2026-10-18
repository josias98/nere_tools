import { spawn } from 'node:child_process';
import { createHash } from 'node:crypto';
import { copyFile, mkdir, mkdtemp, readFile, readdir, rm, stat, writeFile } from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const upstream = 'https://github.com/PDFCraftTool/pdfcraft.git';
const revision = '0be0f8b6a7ada15b60a42d4789238bf49283b494';
const integration = path.join(root, 'integrations', 'pdfcraft');
const pages = path.join(root, 'resources', 'pdfcraft-pages');
const assets = path.join(root, 'public', 'pdfcraft');
const suppliedSource = process.argv.indexOf('--source');
const useExport = process.argv.includes('--use-export');

function run(command, args, cwd, env = process.env) {
    return new Promise((resolve, reject) => {
        const child = spawn(command, args, { cwd, env, stdio: 'inherit', shell: false });
        child.once('error', reject);
        child.once('close', code => code === 0 ? resolve() : reject(new Error(`${command} exited with ${code}`)));
    });
}

async function copyTree(source, relative = '') {
    for (const entry of await readdir(path.join(source, relative), { withFileTypes: true })) {
        const next = path.join(relative, entry.name);
        if (entry.isDirectory()) {
            await copyTree(source, next);
        } else if (entry.isFile() && !next.endsWith('.map') && !['coi-serviceworker.js', 'sw.js'].includes(entry.name)) {
            const target = path.join(next.endsWith('.html') ? pages : assets, next);
            await mkdir(path.dirname(target), { recursive: true });
            await copyFile(path.join(source, next), target);
        }
    }
}

function npm(args, cwd, env = process.env) {
    return process.platform === 'win32'
        ? run('cmd.exe', ['/d', '/s', '/c', 'npm', ...args], cwd, env)
        : run('npm', args, cwd, env);
}

async function main() {
    if (useExport && suppliedSource < 0) throw new Error('--use-export requires --source');
    let source;
    let temporary = false;
    if (suppliedSource >= 0) {
        source = path.resolve(process.argv[suppliedSource + 1] || '');
        if (!source || !(await stat(source)).isDirectory()) throw new Error('Invalid --source directory');
    } else {
        source = await mkdtemp(path.join(os.tmpdir(), 'nere-pdfcraft-'));
        temporary = true;
    }

    try {
        if (temporary) {
            await run('git', ['clone', '--filter=blob:none', '--no-checkout', upstream, source], root);
            await run('git', ['checkout', revision], source);
            await run('git', ['apply', path.join(integration, 'nere.patch')], source);
            await mkdir(path.join(source, 'src', 'components', 'nere'), { recursive: true });
            await copyFile(path.join(integration, 'NereArchiveBridge.tsx'), path.join(source, 'src', 'components', 'nere', 'NereArchiveBridge.tsx'));
            await npm(['ci', '--ignore-scripts'], source);
        }

        if (!useExport) {
            await npm(['run', 'build'], source, {
                ...process.env,
                BASE_PATH: '/pdfcraft',
                NEXT_PUBLIC_BASE_PATH: '/pdfcraft',
                DISABLE_UPDATE_CHECK: 'true',
            });
        }

        const exported = path.join(source, 'out');
        if (!(await stat(path.join(exported, 'index.html'))).isFile()
            || !(await stat(path.join(exported, 'fr', 'index.html'))).isFile()) {
            throw new Error('The French PDFCraft export is missing.');
        }
        await rm(pages, { recursive: true, force: true });
        await rm(assets, { recursive: true, force: true });
        await mkdir(pages, { recursive: true });
        await mkdir(assets, { recursive: true });
        await copyTree(exported);
        await copyFile(path.join(integration, 'assets.htaccess'), path.join(assets, '.htaccess'));
        await copyFile(path.join(source, 'LICENSE'), path.join(assets, 'LICENSE'));
        await writeFile(path.join(pages, '.build-revision'), revision + '\n');
        const fingerprint = createHash('sha256')
            .update(revision)
            .update(await readFile(path.join(integration, 'nere.patch')))
            .update(await readFile(path.join(integration, 'NereArchiveBridge.tsx')))
            .digest('hex');
        await writeFile(path.join(pages, '.build-fingerprint'), fingerprint + '\n');
        console.log('PDFCraft export installed for Laravel at /pdfcraft.');
    } finally {
        if (temporary) await rm(source, { recursive: true, force: true });
    }
}

main().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
