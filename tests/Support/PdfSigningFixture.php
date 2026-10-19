<?php

namespace Tests\Support;

class PdfSigningFixture
{
    public static function certificate(string $password = 'test-password', int $days = 30): string
    {
        $options = [
            'config' => __DIR__.'/../../resources/security/signature-test.cnf',
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
            'digest_alg' => 'sha256',
        ];
        $key = openssl_pkey_new($options);
        $csr = openssl_csr_new(['commonName' => 'NERE Tools - DEVELOPPEMENT', 'organizationName' => 'NERE CAPITAL'], $key, $options);
        $cert = openssl_csr_sign($csr, null, $key, $days, $options, random_int(1, PHP_INT_MAX));
        openssl_pkcs12_export($cert, $pkcs12, $key, $password);

        return $pkcs12;
    }

    public static function pdf(string $catalogExtra = '', string $trailerExtra = ''): string
    {
        $stream = "BT /F1 12 Tf 72 720 Td (Montant approuve : 1500 EUR) Tj ET\n";
        $objects = [
            '<< /Type /Catalog /Pages 2 0 R '.$catalogExtra.' >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            '<< /Length '.strlen($stream).">>\nstream\n".$stream.'endstream',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pdf = "%PDF-1.7\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n".$object."\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 6\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size 6 /Root 1 0 R ".$trailerExtra." >>\nstartxref\n".$xref."\n%%EOF\n";
    }
}
