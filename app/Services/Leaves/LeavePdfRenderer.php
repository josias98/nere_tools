<?php

namespace App\Services\Leaves;

use App\Models\LeaveRequest;
use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Encoder\Encoder;
use Illuminate\Support\Str;

class LeavePdfRenderer
{
    private const PAGE_WIDTH = 595.276;

    private const PAGE_HEIGHT = 841.89;

    public function render(LeaveRequest $request, array $data): string
    {
        $logo = $this->logoAsset();
        $page = $this->page($request, $data, $logo !== null);

        return $this->simplePdf([$page], $logo);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function page(LeaveRequest $request, array $data, bool $hasLogo): string
    {
        $employee = $request->employee;
        $department = $employee?->department?->name ?: ($employee?->entity ?: '-');
        $contact = $employee?->email ?: '-';
        $reviewerComment = trim((string) ($request->reviewer_comment ?? ''));
        $requesterComment = trim((string) ($request->requester_comment ?? ''));
        $signedByLabel = $data['signed_by_label'] ?? 'Nere Capital';
        $signedByRole = $data['signed_by_role'] ?? null;
        $types = $data['type_choices'] ?? [];
        $qrMatrix = $this->qrMatrix((string) $data['verification_url']);
        $content = "0.35 0.33 0.31 RG\n0.55 w\n";

        if ($hasLogo) {
            $this->drawImageTop($content, 'Im1', 56, 42, 162, 86);
        } else {
            $this->textTop($content, 'NERE CAPITAL', 62, 74, 28, 'F2', 'left', [0.34, 0.15, 0.04]);
        }

        $this->fillRectTop($content, 230, 50, 301, 72, 0.90);
        $this->rectTop($content, 236, 44, 301, 72);
        $this->textTop($content, 'DEMANDE DE CONGE OU', 386, 67, 20, 'F2', 'center');
        $this->textTop($content, "D'AUTORISATION D'ABSENCE", 386, 95, 20, 'F2', 'center');

        $this->row($content, 55, 146, [82, 185, 60, 158], [
            ['Nom', 'F2', 'left'],
            [$employee?->last_name ?: '-', 'F1', 'left'],
            ['Prenom', 'F2', 'left'],
            [$employee?->first_name ?: $employee?->name() ?: '-', 'F1', 'left'],
        ], 28);
        $this->row($content, 55, 174, [82, 403], [
            ['Poste', 'F2', 'left'],
            [$employee?->job_title ?: '-', 'F1', 'left'],
        ], 28);
        $this->row($content, 55, 202, [82, 403], [
            ['Departement', 'F2', 'left'],
            [$department, 'F1', 'left'],
        ], 28);

        $this->rectTop($content, 55, 230, 485, 133);
        $this->textTop($content, 'TYPE DE CONGE', 297.5, 248, 12, 'F2', 'center');
        $this->drawTypeColumn($content, 66, 273, array_slice($types, 0, 4));
        $this->drawTypeColumn($content, 300, 273, array_slice($types, 4), $requesterComment);
        $this->lineTop($content, 55, 329, 540, 329);

        $this->rectTop($content, 55, 329, 485, 112);
        $this->lineTop($content, 292, 329, 292, 441);
        $this->lineTop($content, 402, 329, 402, 441);
        $this->lineTop($content, 55, 385, 292, 385);
        $this->textTop($content, 'Duree du conge', 66, 346, 11, 'F2');
        $this->textTop($content, 'Debut : '.$request->start_date->format('d/m/Y'), 66, 374, 10);
        $this->textTop($content, 'Fin : '.$request->end_date->format('d/m/Y'), 66, 397, 10);
        $this->textTop($content, 'Lieu : '.($employee?->location ?: '-'), 66, 416, 10);
        $this->textTop($content, 'Contact : '.$contact, 66, 438, 10);
        $this->textTop($content, 'Nombre de jours : '.rtrim(rtrim(number_format((float) $request->requested_days, 2, '.', ''), '0'), '.'), 304, 346, 10);
        $this->textTop($content, 'Date de la demande :', 414, 346, 10);
        $this->textTop($content, $request->submitted_at?->format('d/m/Y') ?: '-', 414, 366, 11, 'F2');
        $this->textTop($content, 'Signature du demandeur :', 414, 406, 10);

        $this->rectTop($content, 55, 441, 485, 242);
        $this->lineTop($content, 303, 441, 303, 683);
        $this->decisionBlock(
            $content,
            55,
            441,
            248,
            'AVIS DU :',
            [
                ['Superieur hierarchique direct', false],
                ['Avis favorable', true],
                ['Avis defavorable', false],
            ],
            $reviewerComment !== '' ? $reviewerComment : 'Validation electronique interne via Nere Tools.',
            $request->reviewed_at?->format('d/m/Y H:i') ?: '-',
            true
        );
        $this->decisionBlock(
            $content,
            303,
            441,
            237,
            'DECISION DU :',
            [
                ['DIRECTEUR GENERAL', true],
                ['Autorisation accordee', true],
                ['Autorisation refusee', false],
            ],
            'Valide par '.$signedByLabel.($signedByRole ? ', '.$signedByRole : '.'),
            $request->reviewed_at?->format('d/m/Y H:i') ?: '-',
            false
        );

        $this->rectTop($content, 55, 683, 485, 82);
        $this->lineTop($content, 160, 683, 160, 765);
        $this->lineTop($content, 290, 683, 290, 765);
        $this->lineTop($content, 445, 683, 445, 765);
        $this->lineTop($content, 372, 721, 372, 765);
        $this->lineTop($content, 290, 721, 445, 721);
        $this->lineTop($content, 290, 747, 445, 747);
        $this->textTop($content, 'RESERVE AU SERVICE', 107.5, 728, 10, 'F2', 'center');
        $this->textTop($content, 'ADMINISTRATIF', 107.5, 748, 10, 'F2', 'center');
        $this->textTop($content, 'Nom du remplacant', 172, 698, 10);
        $this->textTop($content, "Cumul des conges de l'interesse", 367, 698, 9, 'F1', 'center');
        $this->textTop($content, 'pendant les 12 mois precedents', 367, 716, 9, 'F1', 'center');
        $this->textTop($content, "Autorisation d'absence", 331, 734, 8, 'F3', 'center');
        $this->textTop($content, 'Conge administratif', 408, 734, 8, 'F3', 'center');
        $this->multilineTextTop($content, 'Incidence sur le traitement exprimee en jours', 454, 697, 74, 9, 'F1');

        $this->rectTop($content, 55, 772, 485, 52);
        $this->textTop($content, 'Visa electronique interne', 66, 783, 10, 'F2');
        $this->textTop($content, 'Reference : '.($data['document_reference'] ?? '-'), 66, 796, 8);
        $this->textTop($content, 'Verification : '.($data['verification_path'] ?? '-'), 66, 807, 8);
        $this->textTop($content, 'Validateur : '.$signedByLabel.($signedByRole ? ', '.$signedByRole : ''), 235, 783, 8);
        $this->textTop($content, 'Date de validation : '.($request->reviewed_at?->format('d/m/Y H:i') ?: '-'), 235, 794, 8);
        $this->multilineTextTop(
            $content,
            'Ce document a ete genere et valide electroniquement via Nere Tools. Empreinte SHA-256 exacte disponible via la page de verification.',
            235,
            805,
            215,
            7
        );
        $this->drawQrMatrix($content, $qrMatrix, 472, 776, 1.5);

        return $content;
    }

    /**
     * @param array<int, array{label: string, checked: bool, note: ?string}> $choices
     */
    private function drawTypeColumn(string &$content, float $x, float $top, array $choices, ?string $otherNote = null): void
    {
        $cursor = $top;

        foreach ($choices as $choice) {
            $this->checkbox($content, $x, $cursor, (bool) $choice['checked']);
            $this->textTop($content, Str::upper($choice['label']), $x + 18, $cursor + 2, 9, 'F2');

            if (! empty($choice['note'])) {
                $this->dottedLine($content, $x + 18, $cursor + 16, 152);
                $this->textTop($content, (string) $choice['note'], $x + 18, $cursor + 13, 8);
                $cursor += 14;
            } elseif (Str::contains(Str::lower($choice['label']), 'autre')) {
                $this->dottedLine($content, $x + 18, $cursor + 16, 154);
                if ($otherNote) {
                    $this->textTop($content, $otherNote, $x + 18, $cursor + 13, 8);
                }
                $cursor += 14;
            }

            $cursor += 19;
        }
    }

    /**
     * @param array<int, array{0: string, 1: bool}> $choices
     */
    private function decisionBlock(
        string &$content,
        float $x,
        float $top,
        float $width,
        string $title,
        array $choices,
        string $observation,
        string $date,
        bool $showReplacement,
    ): void {
        $this->textTop($content, $title, $x + 12, $top + 16, 11, 'F2');
        $cursor = $top + 36;

        foreach ($choices as [$label, $checked]) {
            $this->radio($content, $x + 14, $cursor, $checked);
            $this->textTop($content, $label, $x + 34, $cursor + 1, 10, 'F2');
            $cursor += 34;

            if ($showReplacement && $label === 'Avis favorable') {
                $this->textTop($content, 'Remplacement :', $x + 12, $cursor + 4, 10);
                foreach (['Inutile', 'Souhaitable', 'Necessaire'] as $index => $replacement) {
                    $this->radio($content, $x + 14 + ($index * 78), $cursor + 18, false);
                    $this->textTop($content, $replacement, $x + 34 + ($index * 78), $cursor + 19, 9);
                }
                $cursor += 48;
            }

            if (! $showReplacement && $label === 'Autorisation accordee') {
                $this->radio($content, $x + 54, $cursor - 4, false);
                $this->textTop($content, 'Avec remplacement', $x + 74, $cursor - 3, 9);
                $this->radio($content, $x + 54, $cursor + 18, true);
                $this->textTop($content, 'Sans remplacement', $x + 74, $cursor + 19, 9);
                $cursor += 52;
            }
        }

        $this->textTop($content, 'Observations :', $x + 12, $top + 158, 10);
        $this->multilineTextTop($content, $observation, $x + 12, $top + 174, $width - 24, 9);
        $this->textTop($content, 'Date et signature : '.$date, $x + 12, $top + 226, 10);
    }

    /**
     * @param array<int, array{0: string, 1: string, 2: string}> $cells
     * @param array<int, float> $widths
     */
    private function row(string &$content, float $x, float $top, array $widths, array $cells, float $height): void
    {
        $this->rectTop($content, $x, $top, array_sum($widths), $height);
        $cursor = $x;

        foreach (array_slice($widths, 0, -1) as $width) {
            $cursor += $width;
            $this->lineTop($content, $cursor, $top, $cursor, $top + $height);
        }

        $cursor = $x;
        foreach ($cells as $index => [$text, $font, $align]) {
            $this->textTop(
                $content,
                $text,
                $align === 'center' ? $cursor + ($widths[$index] / 2) : $cursor + 10,
                $top + 18,
                10,
                $font,
                $align
            );
            $cursor += $widths[$index];
        }
    }

    /**
     * @param array<int, array<int, bool>> $matrix
     */
    private function drawQrMatrix(string &$content, array $matrix, float $x, float $top, float $moduleSize): void
    {
        foreach ($matrix as $rowIndex => $row) {
            foreach ($row as $columnIndex => $filled) {
                if (! $filled) {
                    continue;
                }

                $this->fillRectTop(
                    $content,
                    $x + ($columnIndex * $moduleSize),
                    $top + ($rowIndex * $moduleSize),
                    $moduleSize,
                    $moduleSize,
                    0.08
                );
            }
        }
    }

    private function checkbox(string &$content, float $x, float $top, bool $checked): void
    {
        $this->rectTop($content, $x, $top, 12, 12);

        if ($checked) {
            $this->lineTop($content, $x, $top, $x + 12, $top + 12);
            $this->lineTop($content, $x + 12, $top, $x, $top + 12);
        }
    }

    private function radio(string &$content, float $x, float $top, bool $checked): void
    {
        $this->ellipseTop($content, $x, $top, 14, 14);

        if ($checked) {
            $this->filledEllipseTop($content, $x + 4, $top + 4, 6, 6, 0.1);
        }
    }

    private function dottedLine(string &$content, float $x, float $top, float $width): void
    {
        $cursor = $x;

        while ($cursor < $x + $width) {
            $this->lineTop($content, $cursor, $top, min($cursor + 2.2, $x + $width), $top);
            $cursor += 4.1;
        }
    }

    private function rectTop(string &$content, float $x, float $top, float $w, float $h): void
    {
        $y = self::PAGE_HEIGHT - $top - $h;
        $content .= sprintf("%.2F %.2F %.2F %.2F re S\n", $x, $y, $w, $h);
    }

    private function fillRectTop(string &$content, float $x, float $top, float $w, float $h, float $gray): void
    {
        $y = self::PAGE_HEIGHT - $top - $h;
        $content .= sprintf("%.2F %.2F %.2F rg %.2F %.2F %.2F %.2F re f\n0 g\n", $gray, $gray, $gray, $x, $y, $w, $h);
    }

    private function lineTop(string &$content, float $x1, float $top1, float $x2, float $top2): void
    {
        $y1 = self::PAGE_HEIGHT - $top1;
        $y2 = self::PAGE_HEIGHT - $top2;
        $content .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $x1, $y1, $x2, $y2);
    }

    private function drawImageTop(string &$content, string $name, float $x, float $top, float $w, float $h): void
    {
        $y = self::PAGE_HEIGHT - $top - $h;
        $content .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n", $w, $h, $x, $y, $name);
    }

    private function ellipseTop(string &$content, float $x, float $top, float $w, float $h): void
    {
        $this->ellipse($content, $x, self::PAGE_HEIGHT - $top - $h, $w, $h, false);
    }

    private function filledEllipseTop(string &$content, float $x, float $top, float $w, float $h, float $gray): void
    {
        $content .= sprintf("%.2F %.2F %.2F rg ", $gray, $gray, $gray);
        $this->ellipse($content, $x, self::PAGE_HEIGHT - $top - $h, $w, $h, true);
        $content .= "0 g\n";
    }

    private function ellipse(string &$content, float $x, float $y, float $w, float $h, bool $fill): void
    {
        $k = 0.5522847498;
        $ox = ($w / 2) * $k;
        $oy = ($h / 2) * $k;
        $xe = $x + $w;
        $ye = $y + $h;
        $xm = $x + ($w / 2);
        $ym = $y + ($h / 2);

        $content .= sprintf(
            "%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c ".
            "%.2F %.2F %.2F %.2F %.2F %.2F c %.2F %.2F %.2F %.2F %.2F %.2F c %s\n",
            $x, $ym,
            $x, $ym + $oy, $xm - $ox, $ye, $xm, $ye,
            $xm + $ox, $ye, $xe, $ym + $oy, $xe, $ym,
            $xe, $ym - $oy, $xm + $ox, $y, $xm, $y,
            $xm - $ox, $y, $x, $ym - $oy, $x, $ym,
            $fill ? 'f' : 'S'
        );
    }

    private function textTop(
        string &$content,
        string $text,
        float $x,
        float $top,
        int $size = 10,
        string $font = 'F1',
        string $align = 'left',
        array $rgb = [0, 0, 0],
    ): void {
        if ($align !== 'left') {
            $x -= $this->textWidth($text, $size) / ($align === 'center' ? 2 : 1);
        }

        $y = self::PAGE_HEIGHT - $top - $size;
        $content .= sprintf(
            "%.2F %.2F %.2F rg BT /%s %d Tf %.2F %.2F Td (%s) Tj ET\n0 g\n",
            $rgb[0],
            $rgb[1],
            $rgb[2],
            $font,
            $size,
            $x,
            $y,
            $this->pdfText($text)
        );
    }

    private function multilineTextTop(
        string &$content,
        string $text,
        float $x,
        float $top,
        float $maxWidth,
        int $size = 9,
        string $font = 'F1',
        float $lineHeight = 11,
    ): void {
        $cursor = $top;

        foreach ($this->wrap($text, $maxWidth, $size) as $line) {
            $this->textTop($content, $line, $x, $cursor, $size, $font);
            $cursor += $lineHeight;
        }
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, float $maxWidth, int $size): array
    {
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');

        if ($text === '') {
            return ['-'];
        }

        $words = preg_split('/\s+/', $text) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : $line.' '.$word;

            if ($this->textWidth($candidate, $size) <= $maxWidth) {
                $line = $candidate;
                continue;
            }

            if ($line !== '') {
                $lines[] = $line;
            }

            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * @param array<int, string> $pages
     * @return array{width: int, height: int, data: string}|null
     */
    private function simplePdf(array $pages, ?array $logo = null): string
    {
        $pageCount = count($pages);
        $font1 = 3 + $pageCount;
        $font2 = $font1 + 1;
        $font3 = $font2 + 1;
        $logoId = $logo === null ? null : $font3 + 1;
        $contentStart = ($logoId ?? $font3) + 1;
        $objects = [
            1 => "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            2 => '2 0 obj << /Type /Pages /Kids ['.implode(' ', array_map(fn (int $i): string => (3 + $i).' 0 R', array_keys($pages)))."] /Count {$pageCount} >> endobj\n",
            $font1 => "{$font1} 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> endobj\n",
            $font2 => "{$font2} 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> endobj\n",
            $font3 => "{$font3} 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >> endobj\n",
        ];

        if ($logoId !== null) {
            $objects[$logoId] = $logoId.' 0 obj << /Type /XObject /Subtype /Image /Width '.$logo['width'].' /Height '.$logo['height'].' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length '.strlen($logo['data'])." >> stream\n".$logo['data']."\nendstream\nendobj\n";
        }

        foreach ($pages as $i => $content) {
            $pageId = 3 + $i;
            $contentId = $contentStart + $i;
            $xObject = $logoId === null ? '' : " /XObject << /Im1 {$logoId} 0 R >>";
            $objects[$pageId] = "{$pageId} 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 ".self::PAGE_WIDTH.' '.self::PAGE_HEIGHT."] /Resources << /Font << /F1 {$font1} 0 R /F2 {$font2} 0 R /F3 {$font3} 0 R >>{$xObject} >> /Contents {$contentId} 0 R >> endobj\n";
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

    private function textWidth(string $text, int $size): float
    {
        return mb_strlen($text) * $size * 0.47;
    }

    private function pdfText(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
        $text = $encoded === false ? Str::ascii($text) : $encoded;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    /**
     * @return array{width: int, height: int, data: string}|null
     */
    private function logoAsset(): ?array
    {
        $path = public_path('brand/nere-capital-rgb.jpg');

        if (! is_file($path)) {
            return null;
        }

        $size = getimagesize($path);
        $raw = file_get_contents($path);

        if ($size === false || $raw === false) {
            return null;
        }

        return ['width' => $size[0], 'height' => $size[1], 'data' => $raw];
    }

    /**
     * @return array<int, array<int, bool>>
     */
    private function qrMatrix(string $value): array
    {
        $matrix = Encoder::encode($value, ErrorCorrectionLevel::M())->getMatrix();
        $padding = 2;
        $rows = [];

        for ($y = -$padding; $y < $matrix->getHeight() + $padding; $y++) {
            $row = [];

            for ($x = -$padding; $x < $matrix->getWidth() + $padding; $x++) {
                $row[] = $x >= 0
                    && $y >= 0
                    && $x < $matrix->getWidth()
                    && $y < $matrix->getHeight()
                    && $matrix->get($x, $y) === 1;
            }

            $rows[] = $row;
        }

        return $rows;
    }
}
