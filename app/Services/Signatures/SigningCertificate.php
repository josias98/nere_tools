<?php

namespace App\Services\Signatures;

use RuntimeException;

class SigningCertificate
{
    public function load(): array
    {
        $mode = config('pdf_signatures.mode');
        if (! in_array($mode, ['test', 'production'], true)) {
            throw new RuntimeException('La signature est indisponible. Un administrateur doit configurer le certificat.');
        }
        if ($mode === 'test' && ! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Les certificats de test sont interdits dans cet environnement.');
        }

        $path = realpath((string) config('pdf_signatures.certificate_path'));
        $public = realpath(public_path());
        if (! $path || ! is_file($path) || ! is_readable($path) || filesize($path) > 1048576
            || ($public && str_starts_with(strtolower($path), strtolower($public).DIRECTORY_SEPARATOR))) {
            throw new RuntimeException('Le certificat de signature est indisponible ou son emplacement est incorrect.');
        }

        $pkcs12 = file_get_contents($path);
        if (! $pkcs12 || ! @openssl_pkcs12_read($pkcs12, $keys, (string) config('pdf_signatures.password'))) {
            throw new RuntimeException('Le certificat de signature ne peut pas être ouvert. Contactez un administrateur.');
        }
        $certificate = openssl_x509_parse($keys['cert']);
        $key = openssl_pkey_get_private($keys['pkey']);
        $details = $key ? openssl_pkey_get_details($key) : false;
        if (! $certificate || ! $key || ! $details || ! openssl_x509_check_private_key($keys['cert'], $key)
            || $details['type'] !== OPENSSL_KEYTYPE_RSA || $details['bits'] < 2048) {
            throw new RuntimeException('Un certificat RSA avec sa clé privée d’au moins 2048 bits est requis.');
        }
        if ($certificate['validFrom_time_t'] > time() || $certificate['validTo_time_t'] <= time()) {
            throw new RuntimeException('Le certificat de signature n’est pas valide à la date actuelle.');
        }
        $usage = $certificate['extensions']['keyUsage'] ?? '';
        if ($usage !== '' && ! str_contains($usage, 'Digital Signature') && ! str_contains($usage, 'Non Repudiation')) {
            throw new RuntimeException('Ce certificat n’autorise pas la signature de documents.');
        }
        $selfSigned = openssl_x509_verify($keys['cert'], openssl_pkey_get_public($keys['cert'])) === 1;
        if ($mode === 'production' && ($selfSigned || str_contains($certificate['extensions']['basicConstraints'] ?? '', 'CA:TRUE'))) {
            throw new RuntimeException('Un certificat de signature émis par une autorité est requis en production.');
        }

        return [
            'pkcs12' => $pkcs12,
            'password' => (string) config('pdf_signatures.password'),
            'pem' => $keys['cert'],
            'mode' => $mode,
            'subject' => $certificate['subject']['CN'] ?? $certificate['subject']['O'] ?? 'Certificat Néré',
            'issuer' => $certificate['issuer']['CN'] ?? $certificate['issuer']['O'] ?? 'Autorité non précisée',
            'fingerprint' => openssl_x509_fingerprint($keys['cert'], 'sha256'),
            'valid_until' => gmdate('Y-m-d', $certificate['validTo_time_t']),
        ];
    }

    public function status(): array
    {
        try {
            $certificate = $this->load();

            return ['ready' => true] + array_diff_key($certificate, array_flip(['pkcs12', 'password', 'pem']));
        } catch (RuntimeException $exception) {
            return ['ready' => false, 'message' => $exception->getMessage()];
        }
    }
}
