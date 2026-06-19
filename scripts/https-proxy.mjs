import fs from 'node:fs';
import http from 'node:http';
import https from 'node:https';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const projectRoot = path.resolve(__dirname, '..');

function loadDotEnv() {
    const envPath = path.join(projectRoot, '.env');

    if (!fs.existsSync(envPath)) {
        return {};
    }

    return fs
        .readFileSync(envPath, 'utf8')
        .split(/\r?\n/)
        .filter((line) => line.trim() !== '' && !line.trim().startsWith('#'))
        .reduce((env, line) => {
            const separator = line.indexOf('=');

            if (separator === -1) {
                return env;
            }

            const key = line.slice(0, separator).trim();
            let value = line.slice(separator + 1).trim();

            if (
                (value.startsWith('"') && value.endsWith('"')) ||
                (value.startsWith("'") && value.endsWith("'"))
            ) {
                value = value.slice(1, -1);
            }

            env[key] = value;

            return env;
        }, {});
}

const env = { ...loadDotEnv(), ...process.env };
const httpsPort = Number(env.LOCAL_HTTPS_PORT || 8443);
const targetPort = Number(env.LOCAL_LARAVEL_HTTP_PORT || 8000);
const pfxPath = path.resolve(projectRoot, env.LOCAL_HTTPS_PFX_PATH || 'storage/local-certs/localhost.pfx');
const passphrase = env.LOCAL_HTTPS_PFX_PASSPHRASE || 'nere-tools-local-dev';

if (!fs.existsSync(pfxPath)) {
    console.error(`Local HTTPS certificate not found at ${pfxPath}`);
    console.error('Run: npm run setup:https');
    process.exit(1);
}

const server = https.createServer(
    {
        pfx: fs.readFileSync(pfxPath),
        passphrase,
    },
    (clientRequest, clientResponse) => {
        const forwardedHost = clientRequest.headers.host || `localhost:${httpsPort}`;

        const proxyRequest = http.request(
            {
                hostname: '127.0.0.1',
                port: targetPort,
                method: clientRequest.method,
                path: clientRequest.url,
                headers: {
                    ...clientRequest.headers,
                    host: `127.0.0.1:${targetPort}`,
                    'x-forwarded-for': clientRequest.socket.remoteAddress || '127.0.0.1',
                    'x-forwarded-host': forwardedHost,
                    'x-forwarded-port': String(httpsPort),
                    'x-forwarded-proto': 'https',
                },
            },
            (proxyResponse) => {
                clientResponse.writeHead(proxyResponse.statusCode || 502, proxyResponse.headers);
                proxyResponse.pipe(clientResponse, { end: true });
            },
        );

        proxyRequest.on('error', () => {
            clientResponse.writeHead(502, { 'content-type': 'text/plain; charset=utf-8' });
            clientResponse.end(
                `Laravel is not responding on http://127.0.0.1:${targetPort}. Start it with npm run serve:https.`,
            );
        });

        clientRequest.pipe(proxyRequest, { end: true });
    },
);

server.listen(httpsPort, 'localhost', () => {
    console.log(`HTTPS proxy ready: https://localhost:${httpsPort}`);
    console.log(`Forwarding to: http://127.0.0.1:${targetPort}`);
});
