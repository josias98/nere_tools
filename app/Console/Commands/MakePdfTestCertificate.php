<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakePdfTestCertificate extends Command
{
    protected $signature = 'signatures:make-test-certificate';

    protected $description = 'Créer un certificat PDF de développement, interdit en production';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Cette commande est réservée aux environnements local et testing.');

            return self::FAILURE;
        }
        $password = (string) config('pdf_signatures.password');
        if (strlen($password) < 12) {
            $this->error('Définissez PDF_SIGNATURE_PASSWORD avec au moins 12 caractères avant de créer le certificat.');

            return self::FAILURE;
        }
        $path = storage_path('app/private/signature-keys/development.p12');
        File::ensureDirectoryExists(dirname($path), 0700);
        $file = @fopen($path, 'x');
        if (! $file) {
            $this->error('Le certificat existe déjà ou le dossier privé est inaccessible. Aucun fichier remplacé.');

            return self::FAILURE;
        }
        try {
            chmod($path, 0600);
            $options = ['config' => resource_path('security/signature-test.cnf'), 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 3072, 'digest_alg' => 'sha256'];
            $key = openssl_pkey_new($options);
            $csr = $key ? openssl_csr_new(['commonName' => 'NERE Tools - DEVELOPPEMENT', 'organizationName' => 'NERE CAPITAL'], $key, $options) : false;
            $cert = $csr ? openssl_csr_sign($csr, null, $key, 365, $options, random_int(1, PHP_INT_MAX)) : false;
            if (! $cert || ! openssl_pkcs12_export($cert, $pkcs12, $key, $password) || fwrite($file, $pkcs12) !== strlen($pkcs12)) {
                throw new \RuntimeException('Échec de la création du certificat de test.');
            }
        } catch (\Throwable) {
            fclose($file);
            @unlink($path);
            $this->error('Impossible de créer le certificat de test. Vérifiez OpenSSL et les droits du dossier privé.');

            return self::FAILURE;
        }
        fclose($file);
        $this->info('Certificat de développement créé : '.$path);
        $this->line('Configurez PDF_SIGNATURE_MODE=test et PDF_SIGNATURE_CERTIFICATE_PATH avec ce chemin.');

        return self::SUCCESS;
    }
}
