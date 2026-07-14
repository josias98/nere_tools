import { createWriteStream } from 'node:fs';
import { lstat, mkdir, readdir, readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { deflateRawSync } from 'node:zlib';
import { build as viteBuild } from 'vite';

const root = process.cwd();
const distDir = path.join(root, 'dist');
const runtimeDirs = [
    'storage/app',
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache',
];

const localOnlyDirs = new Set([
    '.codex',
    '.cursor',
    '.git',
    '.github',
    '.idea',
    '.nova',
    '.phpunit.cache',
    '.vscode',
    '.zed',
    'node_modules',
    'dist',
    'release',
    'releases',
    'tests',
    'tmp',
]);

const localOnlyFiles = new Set([
    '.DS_Store',
    '.env',
    '.phpunit.result.cache',
    '9f1r7v_josias@9f1r7v.ftp.infomaniak.com',
    '_ide_helper.php',
    'auth.json',
    'Homestead.json',
    'Homestead.yaml',
    'public/fonts-manifest.dev.json',
    'Thumbs.db',
]);

function toZipPath(filePath) {
    return filePath.split(path.sep).join('/');
}

function timestamp() {
    const now = new Date();
    const pad = (value) => String(value).padStart(2, '0');

    return [
        now.getFullYear(),
        pad(now.getMonth() + 1),
        pad(now.getDate()),
        '-',
        pad(now.getHours()),
        pad(now.getMinutes()),
    ].join('');
}

function shouldSkip(relPath, dirent) {
    const name = dirent.name;
    const zipPath = toZipPath(relPath);

    if (localOnlyDirs.has(name) || localOnlyDirs.has(zipPath)) return true;
    if (localOnlyFiles.has(name) || localOnlyFiles.has(zipPath)) return true;
    if (name === '.env' || name.startsWith('.env.')) return true;
    if (name === 'npm-debug.log' || name.startsWith('npm-debug.log.')) return true;
    if (name.endsWith('.log') || name.endsWith('.zip')) return true;
    if (name.endsWith('.sqlite') || name.endsWith('.sqlite3')) return true;

    if (zipPath === 'public/hot' || zipPath === 'public/storage') return true;
    if (zipPath === 'bootstrap/cache') return true;
    if (zipPath === 'storage' || zipPath.startsWith('storage/')) return true;

    return false;
}

function dosTime(date) {
    const year = Math.max(date.getFullYear(), 1980);
    const time = (date.getHours() << 11) | (date.getMinutes() << 5) | Math.floor(date.getSeconds() / 2);
    const day = ((year - 1980) << 9) | ((date.getMonth() + 1) << 5) | date.getDate();

    return { time, day };
}

const crcTable = new Uint32Array(256);
for (let i = 0; i < 256; i += 1) {
    let value = i;
    for (let bit = 0; bit < 8; bit += 1) {
        value = value & 1 ? 0xedb88320 ^ (value >>> 1) : value >>> 1;
    }
    crcTable[i] = value >>> 0;
}

function crc32(buffer) {
    let crc = 0xffffffff;
    for (const byte of buffer) {
        crc = crcTable[(crc ^ byte) & 0xff] ^ (crc >>> 8);
    }

    return (crc ^ 0xffffffff) >>> 0;
}

function header(signature, size) {
    const buffer = Buffer.alloc(size);
    buffer.writeUInt32LE(signature, 0);
    return buffer;
}

class ZipWriter {
    constructor(target) {
        this.out = createWriteStream(target);
        this.offset = 0;
        this.central = [];
        this.names = new Set();
    }

    write(buffer) {
        this.out.write(buffer);
        this.offset += buffer.length;
    }

    addDirectory(name) {
        const zipName = name.endsWith('/') ? name : `${name}/`;
        if (this.names.has(zipName)) return;

        const nameBuffer = Buffer.from(zipName);
        const { time, day } = dosTime(new Date());
        const localOffset = this.offset;
        const local = header(0x04034b50, 30);
        local.writeUInt16LE(20, 4);
        local.writeUInt16LE(0, 6);
        local.writeUInt16LE(0, 8);
        local.writeUInt16LE(time, 10);
        local.writeUInt16LE(day, 12);
        local.writeUInt16LE(nameBuffer.length, 26);

        this.write(local);
        this.write(nameBuffer);
        this.central.push({ zipName, nameBuffer, crc: 0, compressedSize: 0, size: 0, method: 0, time, day, localOffset, externalAttrs: 0x10 });
        this.names.add(zipName);
    }

    addFileBuffer(zipName, content, modifiedAt = new Date()) {
        if (this.names.has(zipName)) return;

        const compressed = content.length ? deflateRawSync(content, { level: 9 }) : content;
        const method = content.length ? 8 : 0;
        const checksum = crc32(content);
        const nameBuffer = Buffer.from(zipName);
        const { time, day } = dosTime(modifiedAt);
        const localOffset = this.offset;
        const local = header(0x04034b50, 30);
        local.writeUInt16LE(20, 4);
        local.writeUInt16LE(0, 6);
        local.writeUInt16LE(method, 8);
        local.writeUInt16LE(time, 10);
        local.writeUInt16LE(day, 12);
        local.writeUInt32LE(checksum, 14);
        local.writeUInt32LE(compressed.length, 18);
        local.writeUInt32LE(content.length, 22);
        local.writeUInt16LE(nameBuffer.length, 26);

        this.write(local);
        this.write(nameBuffer);
        this.write(compressed);
        this.central.push({ zipName, nameBuffer, crc: checksum, compressedSize: compressed.length, size: content.length, method, time, day, localOffset, externalAttrs: 0 });
        this.names.add(zipName);
    }

    async addFile(filePath, zipName) {
        const [content, fileStat] = await Promise.all([readFile(filePath), stat(filePath)]);
        this.addFileBuffer(zipName, content, fileStat.mtime);
    }

    async close() {
        const centralStart = this.offset;

        for (const entry of this.central) {
            const central = header(0x02014b50, 46);
            central.writeUInt16LE(20, 4);
            central.writeUInt16LE(20, 6);
            central.writeUInt16LE(0, 8);
            central.writeUInt16LE(entry.method, 10);
            central.writeUInt16LE(entry.time, 12);
            central.writeUInt16LE(entry.day, 14);
            central.writeUInt32LE(entry.crc, 16);
            central.writeUInt32LE(entry.compressedSize, 20);
            central.writeUInt32LE(entry.size, 24);
            central.writeUInt16LE(entry.nameBuffer.length, 28);
            central.writeUInt32LE(entry.externalAttrs, 38);
            central.writeUInt32LE(entry.localOffset, 42);
            this.write(central);
            this.write(entry.nameBuffer);
        }

        const centralSize = this.offset - centralStart;
        const end = header(0x06054b50, 22);
        end.writeUInt16LE(this.central.length, 8);
        end.writeUInt16LE(this.central.length, 10);
        end.writeUInt32LE(centralSize, 12);
        end.writeUInt32LE(centralStart, 16);
        this.write(end);
        this.out.end();

        await new Promise((resolve, reject) => {
            this.out.on('finish', resolve);
            this.out.on('error', reject);
        });
    }
}

function addDirWithParents(zip, dir) {
    const parts = dir.split('/');
    for (let i = 1; i <= parts.length; i += 1) {
        zip.addDirectory(parts.slice(0, i).join('/'));
    }
}

async function addRuntimeDirs(zip) {
    for (const dir of runtimeDirs) {
        addDirWithParents(zip, dir);

        const placeholder = path.join(root, ...dir.split('/'), '.gitignore');
        try {
            const content = await readFile(placeholder);
            const fileStat = await stat(placeholder);
            zip.addFileBuffer(`${dir}/.gitignore`, content, fileStat.mtime);
        } catch {
            zip.addFileBuffer(`${dir}/.gitignore`, Buffer.from('*\n!.gitignore\n'));
        }
    }
}

async function walk(zip, currentDir = root, currentRel = '') {
    const dirents = await readdir(currentDir, { withFileTypes: true });

    for (const dirent of dirents) {
        const relPath = currentRel ? path.join(currentRel, dirent.name) : dirent.name;
        if (shouldSkip(relPath, dirent)) continue;

        const fullPath = path.join(currentDir, dirent.name);
        const zipPath = toZipPath(relPath);

        if (dirent.isSymbolicLink()) continue;
        if (dirent.isDirectory()) {
            zip.addDirectory(zipPath);
            await walk(zip, fullPath, relPath);
            continue;
        }
        if (dirent.isFile()) {
            await zip.addFile(fullPath, zipPath);
        }
    }
}

async function main() {
    console.log('Building Vite assets...');
    await viteBuild();

    try {
        const buildStat = await lstat(path.join(root, 'public', 'build'));
        if (!buildStat.isDirectory()) throw new Error('public/build is not a directory');
    } catch {
        throw new Error('Vite build did not create public/build.');
    }

    await mkdir(distDir, { recursive: true });
    const zipPath = path.join(distDir, `nere-tools-release-${timestamp()}.zip`);
    const zip = new ZipWriter(zipPath);

    await walk(zip);
    await addRuntimeDirs(zip);
    await zip.close();

    console.log(`Release ZIP created: ${zipPath}`);
}

main().catch((error) => {
    console.error(error.message);
    process.exit(1);
});
