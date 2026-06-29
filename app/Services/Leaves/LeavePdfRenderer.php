<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
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
        $signatoryName = (string) ($data['signed_by_label'] ?? 'Direction Generale');
        $signatoryTitle = (string) ($data['signed_by_role'] ?? 'Directeur general');
        $days = rtrim(rtrim(number_format((float) $request->requested_days, 2, ',', ' '), '0'), ',');
        $generatedAt = now();
        $content = "0.32 0.15 0.04 RG\n0.8 w\n";

        if (isset($images['Logo'])) {
            $this->drawImageTop($content, 'Logo', 58, 38, 122, 58);
        } else {
            $this->textTop($content, 'NERE CAPITAL', 58, 68, 22, 'F2', 'left', [0.32, 0.15, 0.04]);
        }

        $this->multilineTextTop(
            $content,
            $company."\nOuagadougou, Burkina Faso\nEmail : tools@nerecapital.com",
            320,
            45,
            215,
            8.5,
            'F1',
            11,
        );
        $this->lineTop($content, 58, 116, 537, 116);
        $this->textTop($content, 'ATTESTATION DE CONGES', self::PAGE_WIDTH / 2, 172, 19, 'F2', 'center', [0.32, 0.15, 0.04]);
        $this->lineTop($content, 185, 194, 410, 194);

        $body = sprintf(
            'Je soussigne, %s, %s, atteste que %s, employe(e) a %s en qualite de %s, beneficie d un conge de %s jours allant du %s au %s inclus.',
            $signatoryName,
            $signatoryTitle,
            $employee?->name() ?: '-',
            $company,
            $employee?->job_title ?: '-',
            $days,
            $this->date($request->start_date),
            $this->date($request->end_date),
        );

        $this->multilineTextTop($content, $body, 82, 248, 430, 12, 'F1', 19);
        $this->multilineTextTop(
            $content,
            'En foi de quoi, la presente attestation lui est delivree pour servir et valoir ce que de droit.',
            82,
            368,
            430,
            12,
            'F1',
            19,
        );

        $this->textTop($content, 'Fait a Ouagadougou, le '.$this->date($generatedAt), 312, 500, 11, 'F1');
        $this->textTop($content, $signatoryTitle, 365, 586, 10.5, 'F1', 'center');

        if (isset($images['Signature'])) {
            $this->drawImageTop($content, 'Signature', 323, 602, 130, 58);
        }

        $this->lineTop($content, 295, 678, 500, 678);
        $this->textTop($content, $signatoryName, 397, 697, 11, 'F2', 'center');

        $this->textTop($content, 'Reference interne : '.($data['document_reference'] ?? '-'), 58, 790, 7.8, 'F1', 'left', [0.36, 0.32, 0.28]);

        return $content;
    }

    private function date(?CarbonInterface $date): string
    {
        return $date?->format('d/m/Y') ?: '-';
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
