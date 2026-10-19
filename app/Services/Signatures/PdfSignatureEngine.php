<?php

namespace App\Services\Signatures;

use App\Exceptions\PdfSignatureException;
use SignerPHP\PdfCore\PdfValue\PDFValue;
use SignerPHP\PdfCore\Service\PdfDocumentPreparer;
use SignerPHP\PdfSigner\Application\DTO\SignatureActorDto;
use SignerPHP\PdfSigner\Application\DTO\SignatureMetadataDto;
use SignerPHP\PdfSigner\Presentation\Signer;
use Throwable;

class PdfSignatureEngine
{
    public function inspect(string $pdf): int
    {
        if (strlen($pdf) > config('pdf_signatures.max_upload_kb') * 1024 || ! str_starts_with($pdf, '%PDF-')) {
            throw new PdfSignatureException('Importez un fichier PDF de 10 Mo maximum.');
        }

        try {
            $document = (new PdfDocumentPreparer)->prepare($pdf);
            if (isset($document->getTrailerObject()['Encrypt'])) {
                throw new PdfSignatureException('Les PDF protégés par mot de passe ne sont pas acceptés.');
            }
            $this->inspectValue($document->getTrailerObject());
            $xref = $document->getXrefTable();
            if (count($xref) > 20000) {
                throw new PdfSignatureException('Ce PDF est trop complexe. Exportez une copie PDF simplifiée.');
            }
            foreach ($xref as $id => $entry) {
                if ((int) $id === 0 || $entry === null) {
                    continue;
                }
                $object = $document->getObject((int) $id);
                if ($object) {
                    $this->inspectValue($object->getValue());
                    if ($object->getGeneration() !== 0) {
                        throw new PdfSignatureException('La structure de ce PDF n’est pas prise en charge. Exportez une nouvelle copie PDF.');
                    }
                }
            }
            $pages = 0;
            while ($document->getPageInfo()->getPage($pages) !== null && $pages <= config('pdf_signatures.max_pages')) {
                $pages++;
            }
            if ($pages < 1 || $pages > config('pdf_signatures.max_pages')) {
                throw new PdfSignatureException('Le PDF doit contenir entre 1 et 100 pages.');
            }

            return $pages;
        } catch (PdfSignatureException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new PdfSignatureException('Ce PDF ne peut pas être lu. Exportez une nouvelle copie PDF puis réessayez.');
        }
    }

    private function inspectValue(PDFValue $value, int $depth = 0): void
    {
        if ($depth > 40) {
            throw new PdfSignatureException('Ce PDF est trop complexe pour être signé.');
        }
        $data = $value->val();
        if (! is_array($data)) {
            return;
        }
        foreach ($data as $name => $child) {
            $name = preg_replace_callback('/#([a-f0-9]{2})/i', fn (array $m): string => chr(hexdec($m[1])), (string) $name);
            $type = is_string($child->val()) ? preg_replace_callback('/#([a-f0-9]{2})/i', fn (array $m): string => chr(hexdec($m[1])), $child->val()) : null;
            if (in_array($name, ['Encrypt', 'ByteRange', 'AcroForm', 'Perms', 'XFA', 'JavaScript', 'JS', 'A', 'AA', 'OpenAction', 'EmbeddedFiles', 'EF', 'RichMedia', 'Launch'], true)
                || ($name === 'Type' && in_array($type, ['Sig', 'DocTimeStamp', 'EmbeddedFile'], true))) {
                throw new PdfSignatureException('Ce PDF contient déjà une signature, un formulaire ou du contenu actif. Utilisez un PDF simple non signé.');
            }
            $this->inspectValue($child, $depth + 1);
        }
    }

    public function sign(string $pdf, array $certificate, string $name, string $email, string $reason): string
    {
        $this->inspect($pdf);
        $signed = Signer::signer()
            ->withPdfContent($pdf)
            ->withCertificateContent($certificate['pkcs12'], $certificate['password'])
            ->withoutDefaultAppearance()
            ->withoutTimestamp()
            ->withPadesBaselineB()
            ->withMetadata(new SignatureMetadataDto(
                reason: $reason,
                actor: new SignatureActorDto(name: $name.' — via Néré Tools', contactInfo: $email),
            ))
            ->sign();

        if (! str_starts_with($signed, $pdf) || ! $this->verify($signed, $certificate['fingerprint'])) {
            throw new PdfSignatureException('La signature n’a pas pu être vérifiée. Le document original est conservé.');
        }

        return $signed;
    }

    // Only verify our single-signature outputs, never claim to validate arbitrary uploaded PDFs.
    public function verify(string $pdf, string $fingerprint): bool
    {
        $files = [];
        try {
            $document = (new PdfDocumentPreparer)->prepare($pdf);
            $signatures = [];
            foreach ($document->getXrefTable() as $id => $entry) {
                if ((int) $id === 0 || $entry === null) {
                    continue;
                }
                $object = $document->getObject((int) $id);
                if ($object && $object['Type']?->val() === 'Sig') {
                    $signatures[] = $object;
                }
            }
            if (count($signatures) !== 1) {
                return false;
            }
            $signature = $signatures[0];
            if (! preg_match('/\A\[\s*(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*\]\z/', (string) $signature['ByteRange'], $ranges)) {
                return false;
            }
            $range = array_map('intval', array_slice($ranges, 1));
            if ($range[0] !== 0 || $range[1] < 1 || $range[2] <= $range[1]
                || $range[3] < 1 || strlen($pdf) !== $range[2] + $range[3]) {
                return false;
            }
            $gap = substr($pdf, $range[1], $range[2] - $range[1]);
            if (! preg_match('/\A<([0-9a-fA-F]+)>\z/', $gap, $matches)
                || strtolower($matches[1]) !== strtolower($signature['Contents']->val())) {
                return false;
            }

            foreach (['content', 'cms', 'signer'] as $part) {
                $path = tempnam(sys_get_temp_dir(), 'nere-signature-');
                if ($path === false) {
                    throw new PdfSignatureException('Espace temporaire indisponible.');
                }
                $files[$part] = $path;
                chmod($path, 0600);
            }
            $content = substr($pdf, 0, $range[1]).substr($pdf, $range[2], $range[3]);
            if (file_put_contents($files['content'], $content) === false
                || file_put_contents($files['cms'], hex2bin($matches[1])) === false) {
                return false;
            }
            $valid = @openssl_cms_verify($files['content'], OPENSSL_CMS_BINARY | OPENSSL_CMS_NOVERIFY | OPENSSL_CMS_DETACHED,
                $files['signer'], [], null, null, null, $files['cms'], OPENSSL_ENCODING_DER);
            $signer = file_get_contents($files['signer']);

            return $valid && $signer && hash_equals($fingerprint, openssl_x509_fingerprint($signer, 'sha256'));
        } catch (Throwable) {
            return false;
        } finally {
            foreach ($files as $path) {
                @unlink($path);
            }
        }
    }
}
