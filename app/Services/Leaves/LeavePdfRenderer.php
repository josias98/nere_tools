<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class LeavePdfRenderer
{
    private const PAGE_WIDTH = 595.276;

    private const PAGE_HEIGHT = 841.89;

    public function render(LeaveRequest $request, array $data): string
    {
        $images = array_filter([
            'Logo' => $this->jpegAsset(public_path('brand/nere-capital-rgb.jpg')),
            'Signature' => $this->signatureAsset(),
        ]);

        return $this->simplePdf([$this->page($request, $data, $images)], $images);
    }

    private function page(LeaveRequest $request, array $data, array $images): string
    {
        $employee = $request->employee;
        $company = $employee?->entity ?: 'NERE CAPITAL PARTNERS';
        $signatoryName = (string) ($data['signed_by_label'] ?? 'Monsieur ZONGO P. Job');
        $signatoryTitle = (string) ($data['signed_by_role'] ?? 'Directeur Général');
        $days = rtrim(rtrim(number_format((float) $request->requested_days, 2, ',', ' '), '0'), ',');
        $generatedAt = now();
        $content = "0.32 0.15 0.04 RG\n0.8 w\n";

        if (isset($images['Logo'])) {
            $this->drawImageTop($content, 'Logo', 58, 38, 122, 58);
        } else {
            $this->textTop($content, 'NERE CAPITAL', 58, 68, 22, 'F2', 'left', [0.32, 0.15, 0.04]);
        }

        $this->multilineTextTop($content, "NERE CAPITAL PARTNERS\nOuaga 2000, Rue Bitto\n01 BP 595 Ouagadougou 01\nTél : +226 25 37 57 66\nEmail : info@nerecapital.com", 320, 40, 220, 8.5, 'F1', 11);
        $this->brandRule($content, 58, 116, 479);
        $this->rectangleTop($content, 82, 174, 431, 38, [0.76, 0.76, 0.76]);
        $this->textTop($content, 'ATTESTATION DE CONGES', self::PAGE_WIDTH / 2, 181, 19, 'F2', 'center');

        $body = sprintf(
            'Je soussigné, %s, %s, atteste que %s, employé(e) à %s en qualité de %s, bénéficie d\'un congé de %s jours allant du %s au %s inclus.',
            $signatoryName,
            $signatoryTitle,
            $employee?->name() ?: '-',
            $company,
            $employee?->job_title ?: '-',
            $days,
            $this->date($request->start_date),
            $this->date($request->end_date),
        );

        $this->multilineTextTop($content, $body, 82, 260, 430, 11.5, 'F1', 19);
        $this->multilineTextTop(
            $content,
            'En foi de quoi, la présente attestation lui est délivrée pour servir et valoir ce que de droit.',
            82,
            380,
            430,
            12,
            'F1',
            19,
        );

        $this->textTop($content, 'Fait à Ouagadougou, le '.$this->date($generatedAt), 312, 494, 11, 'F1');
        $this->textTop($content, $signatoryTitle, 397, 565, 10.5, 'F1', 'center');

        if (isset($images['Signature'])) {
            $this->drawImageTop($content, 'Signature', 332, 584, 130, 58);
        }

        $this->textTop($content, $signatoryName, 397, 657, 11, 'F2', 'center');

        $verificationUrl = (string) ($data['verification_url'] ?? '');
        if ($verificationUrl !== '') {
            $this->qrCode($content, $verificationUrl, 62, 708, 52);
        }
        $this->textTop($content, 'Ce document peut être authentifié numériquement.', 124, 714, 8.2, 'F2', 'left', [0.24, 0.22, 0.20]);
        $this->textTop($content, 'Vérification : '.$verificationUrl, 124, 728, 7.2, 'F1', 'left', [0.36, 0.32, 0.28]);
        $this->textTop($content, 'Référence : '.($data['document_reference'] ?? '-'), 124, 741, 7.2, 'F1', 'left', [0.36, 0.32, 0.28]);
        $this->brandRule($content, 58, 778, 479);
        $this->multilineTextTop($content, 'NERE CAPITAL PARTNERS, Société au capital de 10 000 000 Francs CFA - RCCM : BF OUA O1 2024 B16 10564 - IFU : 00241622Y, Régime Simplifié d\'imposition DCI OUAGA 8', 82, 790, 431, 7.1, 'F2', 9);

        return $content;
    }

    private function date(?CarbonInterface $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        return $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }

    private function rectangleTop(string &$content, float $x, float $top, float $width, float $height, array $rgb): void
    {
        $content .= sprintf("%.2F %.2F %.2F rg %.2F %.2F %.2F %.2F re f 0 g\n", $rgb[0], $rgb[1], $rgb[2], $x, self::PAGE_HEIGHT - $top - $height, $width, $height);
    }

    private function brandRule(string &$content, float $x, float $top, float $width): void
    {
        $colors = [[0.35, 0.28, 0.30], [0.92, 0.48, 0.14], [0.45, 0.18, 0.05], [0.64, 0.50, 0.25], [0.55, 0.16, 0.14], [0.55, 0.68, 0.82], [0.27, 0.10, 0.02]];
        $segment = $width / count($colors);
        foreach ($colors as $index => $color) {
            $this->rectangleTop($content, $x + ($index * $segment), $top, $segment + 0.2, 4, $color);
        }
    }

    private function qrCode(string &$content, string $value, float $x, float $top, float $size): void
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::M())->getMatrix();
        $margin = 2;
        $module = $size / ($matrix->getWidth() + ($margin * 2));
        $this->rectangleTop($content, $x, $top, $size, $size, [1, 1, 1]);

        foreach ($matrix->getArray() as $row => $values) {
            foreach ($values as $column => $enabled) {
                if ($enabled) {
                    $this->rectangleTop($content, $x + (($column + $margin) * $module), $top + (($row + $margin) * $module), $module + 0.05, $module + 0.05, [0.12, 0.12, 0.12]);
                }
            }
        }
    }

    private function textTop(
        string &$content,
        string $text,
        float $x,
        float $top,
        float $size = 10,
        string $font = 'F1',
        string $align = 'left',
        array $rgb = [0, 0, 0],
    ): void {
        if ($align !== 'left') {
            $x -= $this->textWidth($text, $size) / ($align === 'center' ? 2 : 1);
        }

        $y = self::PAGE_HEIGHT - $top - $size;
        $content .= sprintf(
            "%.2F %.2F %.2F rg BT /%s %.2F Tf %.2F %.2F Td (%s) Tj ET\n0 g\n",
            $rgb[0],
            $rgb[1],
            $rgb[2],
            $font,
            $size,
            $x,
            $y,
            $this->pdfText($text),
        );
    }

    private function multilineTextTop(
        string &$content,
        string $text,
        float $x,
        float $top,
        float $maxWidth,
        float $size = 9,
        string $font = 'F1',
        float $lineHeight = 11,
    ): void {
        foreach ($this->wrap($text, $maxWidth, $size) as $index => $line) {
            $this->textTop($content, $line, $x, $top + ($index * $lineHeight), $size, $font);
        }
    }

    private function lineTop(string &$content, float $x1, float $top1, float $x2, float $top2): void
    {
        $content .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $x1, self::PAGE_HEIGHT - $top1, $x2, self::PAGE_HEIGHT - $top2);
    }

    private function drawImageTop(string &$content, string $name, float $x, float $top, float $w, float $h): void
    {
        $content .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n", $w, $h, $x, self::PAGE_HEIGHT - $top - $h, $name);
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, float $maxWidth, float $size): array
    {
        $lines = [];

        foreach (preg_split('/\n+/', trim($text)) ?: [] as $paragraph) {
            $line = '';

            foreach (preg_split('/\s+/', trim($paragraph)) ?: [] as $word) {
                $candidate = $line === '' ? $word : $line.' '.$word;

                if ($this->textWidth($candidate, $size) <= $maxWidth) {
                    $line = $candidate;
                } else {
                    $lines[] = $line;
                    $line = $word;
                }
            }

            if ($line !== '') {
                $lines[] = $line;
            }
        }

        return $lines === [] ? ['-'] : $lines;
    }

    private function textWidth(string $text, float $size): float
    {
        $width = 0.0;

        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $character) {
            $width += match (true) {
                preg_match('/\s/u', $character) === 1 => $size * 0.28,
                preg_match('/[ilI\.,:;!\']/', $character) === 1 => $size * 0.24,
                preg_match('/[mwMW@#%&QGO0-9]/u', $character) === 1 => $size * 0.62,
                preg_match('/[A-Z]/u', $character) === 1 => $size * 0.58,
                default => $size * 0.49,
            };
        }

        return $width;
    }

    private function simplePdf(array $pages, array $images): string
    {
        $pageCount = count($pages);
        $font1 = 3 + $pageCount;
        $font2 = $font1 + 1;
        $objects = [
            1 => "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            2 => '2 0 obj << /Type /Pages /Kids ['.implode(' ', array_map(fn (int $i): string => (3 + $i).' 0 R', array_keys($pages)))."] /Count {$pageCount} >> endobj\n",
            $font1 => "{$font1} 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> endobj\n",
            $font2 => "{$font2} 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> endobj\n",
        ];
        $nextId = $font2 + 1;
        $imageIds = [];

        foreach ($images as $name => $image) {
            $imageIds[$name] = $nextId;
            $objects[$nextId] = "{$nextId} 0 obj << /Type /XObject /Subtype /Image /Width {$image['width']} /Height {$image['height']} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($image['data'])." >> stream\n{$image['data']}\nendstream\nendobj\n";
            $nextId++;
        }

        foreach ($pages as $i => $content) {
            $pageId = 3 + $i;
            $contentId = $nextId++;
            $xObjects = $imageIds === [] ? '' : ' /XObject << '.collect($imageIds)->map(fn (int $id, string $name): string => "/{$name} {$id} 0 R")->implode(' ').' >>';
            $objects[$pageId] = "{$pageId} 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 ".self::PAGE_WIDTH.' '.self::PAGE_HEIGHT."] /Resources << /Font << /F1 {$font1} 0 R /F2 {$font2} 0 R >>{$xObjects} >> /Contents {$contentId} 0 R >> endobj\n";
            $objects[$contentId] = $contentId.' 0 obj << /Length '.strlen($content)." >> stream\n{$content}endstream\nendobj\n";
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $size = max(array_keys($objects)) + 1;
        $pdf .= "xref\n0 {$size}\n0000000000 65535 f \n";

        for ($i = 1; $i < $size; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $pdf."trailer << /Size {$size} /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    private function pdfText(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
        $text = $encoded === false ? Str::ascii($text) : $encoded;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function signatureAsset(): ?array
    {
        foreach ([storage_path('app/signatures/dg-signature.png'), public_path('brand/dg-signature.png')] as $path) {
            if ($image = $this->jpegAsset($path)) {
                return $image;
            }
        }

        return null;
    }

    private function jpegAsset(string $path): ?array
    {
        if (! is_file($path)) {
            return null;
        }

        $size = getimagesize($path);
        $raw = file_get_contents($path);

        if ($size === false || $raw === false) {
            return null;
        }

        if (($size['mime'] ?? null) === 'image/jpeg') {
            return ['width' => $size[0], 'height' => $size[1], 'data' => $raw];
        }

        if (($size['mime'] ?? null) === 'image/png' && function_exists('imagecreatefrompng')) {
            $png = imagecreatefrompng($path);

            if (! $png) {
                return null;
            }

            ob_start();
            imagejpeg($png, null, 92);
            $jpg = ob_get_clean();
            imagedestroy($png);

            return $jpg === false ? null : ['width' => $size[0], 'height' => $size[1], 'data' => $jpg];
        }

        return null;
    }
}
