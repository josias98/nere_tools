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
        $signedByLabel = $data['signed_by_label'] ?? 'Néré Capital';
        $signedByRole = $data['signed_by_role'] ?? null;
        $types = $data['type_choices'] ?? [];
        $qrMatrix = $this->qrMatrix((string) $data['verification_url']);
        $content = "0.35 0.33 0.31 RG\n0.55 w\n";

        if ($hasLogo) {
            $this->drawImageTop($content, 'Im1', 55, 40, 152, 80);
        } else {
            $this->textTop($content, 'NERE CAPITAL', 58, 72, 28, 'F2', 'left', [0.34, 0.15, 0.04]);
        }

        $this->fillRectTop($content, 233, 42, 300, 70, 0.90);
        $this->rectTop($content, 233, 42, 300, 70);
        $this->textTop($content, 'DEMANDE DE CONGÉ OU', 383, 62, 18, 'F2', 'center');
        $this->textTop($content, "D'AUTORISATION D'ABSENCE", 383, 88, 18, 'F2', 'center');

        $this->row($content, 55, 126, [82, 184, 60, 159], [
            ['Nom', 'F2', 'left'],
            [$employee?->last_name ?: '-', 'F1', 'left'],
            ['Prénom', 'F2', 'left'],
            [$employee?->first_name ?: $employee?->name() ?: '-', 'F1', 'left'],
        ], 28);
        $this->row($content, 55, 154, [82, 403], [
            ['Poste', 'F2', 'left'],
            [$employee?->job_title ?: '-', 'F1', 'left'],
        ], 30);
        $this->row($content, 55, 184, [82, 403], [
            ['Département', 'F2', 'left'],
            [$department, 'F1', 'left'],
        ], 30);

        $this->rectTop($content, 55, 214, 485, 124);
        $this->textTop($content, 'TYPE DE CONGÉ', 297.5, 234, 12, 'F2', 'center');
        $this->drawTypeColumn($content, 68, 254, 186, array_slice($types, 0, 4));
        $this->drawTypeColumn($content, 302, 254, 184, array_slice($types, 4), $requesterComment);
        $this->lineTop($content, 55, 338, 540, 338);

        $this->rectTop($content, 55, 338, 485, 100);
        $this->lineTop($content, 303, 338, 303, 438);
        $this->lineTop($content, 406, 338, 406, 438);
        $this->textTop($content, 'DURÉE DU CONGÉ', 66, 354, 11, 'F2');
        $this->textTop($content, 'DÉBUT', 66, 373, 9, 'F2');
        $this->textTop($content, $request->start_date->format('d/m/Y'), 66, 388, 10);
        $this->textTop($content, 'FIN', 66, 406, 9, 'F2');
        $this->textTop($content, $request->end_date->format('d/m/Y'), 66, 421, 10);
        $this->textTop($content, 'LIEU', 168, 373, 9, 'F2');
        $this->multilineTextTop($content, $employee?->location ?: '-', 168, 388, 122, 10, 'F1', 11, 2);
        $this->textTop($content, 'CONTACT', 168, 406, 9, 'F2');
        $this->multilineTextTop($content, $contact, 168, 421, 122, 9, 'F1', 10, 2);
        $this->textTop($content, 'Nombre de jours', 315, 356, 9, 'F2');
        $this->textTop($content, rtrim(rtrim(number_format((float) $request->requested_days, 2, '.', ''), '0'), '.'), 315, 376, 13, 'F2');
        $this->textTop($content, 'Date de la demande', 418, 356, 9, 'F2');
        $this->textTop($content, $request->submitted_at?->format('d/m/Y') ?: '-', 418, 376, 11, 'F2');
        $this->textTop($content, 'Signature du demandeur', 418, 404, 9, 'F2');

        $this->rectTop($content, 55, 438, 485, 238);
        $this->lineTop($content, 299, 438, 299, 676);
        $this->decisionBlock(
            $content,
            55,
            438,
            244,
            238,
            'AVIS DU :',
            [
                ['SUPÉRIEUR HIÉRARCHIQUE DIRECT', false],
                ['AVIS FAVORABLE', true],
                ['AVIS DÉFAVORABLE', false],
            ],
            $reviewerComment !== '' ? $reviewerComment : 'Validation électronique interne via Néré Tools.',
            $request->reviewed_at?->format('d/m/Y H:i') ?: '-',
            true
        );
        $this->decisionBlock(
            $content,
            299,
            438,
            241,
            238,
            'DÉCISION DU :',
            [
                ['DIRECTEUR GÉNÉRAL', true],
                ['AUTORISATION ACCORDÉE', true],
                ['AUTORISATION REFUSÉE', false],
            ],
            'Validé par '.$signedByLabel.($signedByRole ? ', '.$signedByRole : '.'),
            $request->reviewed_at?->format('d/m/Y H:i') ?: '-',
            false
        );

        $this->rectTop($content, 55, 676, 485, 70);
        $this->lineTop($content, 165, 676, 165, 746);
        $this->lineTop($content, 306, 676, 306, 746);
        $this->lineTop($content, 385, 708, 385, 746);
        $this->lineTop($content, 463, 676, 463, 746);
        $this->lineTop($content, 306, 721, 463, 721);
        $this->fittedMultilineTextTop($content, 'RÉSERVÉ AU SERVICE ADMINISTRATIF', 68, 684, 84, 54, 8.5, 6.5, 'F2', 'center', 'middle', 3);
        $this->textTop($content, 'Nom du remplaçant', 176, 692, 9);
        $this->textTop($content, 'Cumul des congés', 384, 690, 8, 'F2', 'center');
        $this->textTop($content, "des 12 mois précédents", 384, 704, 8, 'F2', 'center');
        $this->multilineTextTop($content, "Autorisation d'absence", 314, 724, 60, 7, 'F3', 8, 2);
        $this->multilineTextTop($content, 'Congé administratif', 393, 724, 60, 7, 'F3', 8, 2);
        $this->fittedMultilineTextTop($content, 'Incidence sur le traitement exprimée en jours', 472, 690, 54, 28, 7, 6, 'F1', 'left', 'top', 4);

        $this->rectTop($content, 55, 750, 485, 74);
        $this->lineTop($content, 452, 750, 452, 824);
        $this->textTop($content, 'Visa électronique interne', 66, 764, 10, 'F2');
        $this->textTop($content, 'Référence : '.($data['document_reference'] ?? '-'), 66, 779, 8);
        $this->fittedMultilineTextTop(
            $content,
            'Vérification : scanner le QR code ou consulter la page de vérification Néré Tools.',
            66,
            790,
            172,
            18,
            8,
            6,
            'F1',
            'left',
            'top',
            2
        );
        $this->fittedMultilineTextTop(
            $content,
            'Validateur : '.$signedByLabel.($signedByRole ? ', '.$signedByRole : ''),
            246,
            779,
            190,
            18,
            8,
            6,
            'F1',
            'left',
            'top',
            2
        );
        $this->textTop($content, 'Date de validation : '.($request->reviewed_at?->format('d/m/Y H:i') ?: '-'), 246, 799, 8);
        $this->fittedMultilineTextTop(
            $content,
            'Ce document a été généré et validé électroniquement via Néré Tools. Son authenticité peut être vérifiée en scannant le QR code.',
            66,
            807,
            370,
            14,
            7,
            6,
            'F1',
            'left',
            'top',
            2
        );
        $this->drawQrMatrix($content, $qrMatrix, 468, 758, 1.45);

        return $content;
    }

    /**
     * @param array<int, array{label: string, checked: bool, note: ?string}> $choices
     */
    private function drawTypeColumn(string &$content, float $x, float $top, float $labelWidth, array $choices, ?string $otherNote = null): void
    {
        $cursor = $top;

        foreach ($choices as $choice) {
            $label = Str::upper($choice['label']);
            $labelLines = $this->wrap($label, $labelWidth, 9);
            $this->checkbox($content, $x, $cursor + 1, (bool) $choice['checked']);
            $this->multilineTextTop($content, $label, $x + 18, $cursor, $labelWidth, 9, 'F2', 10, 2);
            $cursor += max(18, (count($labelLines) * 10) + 2);

            if (! empty($choice['note'])) {
                $this->dottedLine($content, $x + 18, $cursor + 8, $labelWidth - 8);
                $this->multilineTextTop($content, (string) $choice['note'], $x + 18, $cursor, $labelWidth - 8, 8, 'F1', 9, 2);
                $cursor += 16;
            } elseif (Str::contains(Str::lower($choice['label']), 'autre')) {
                $this->dottedLine($content, $x + 18, $cursor + 8, $labelWidth - 8);
                if ($otherNote) {
                    $this->multilineTextTop($content, $otherNote, $x + 18, $cursor, $labelWidth - 8, 8, 'F1', 9, 2);
                }
                $cursor += 16;
            }

            $cursor += 7;
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
        float $height,
        string $title,
        array $choices,
        string $observation,
        string $date,
        bool $showReplacement,
    ): void {
        $this->textTop($content, $title, $x + 12, $top + 16, 11, 'F2');
        $this->lineTop($content, $x + 10, $top + 28, $x + $width - 10, $top + 28);
        $this->radioOption($content, $x + 14, $top + 44, $choices[0][0], $choices[0][1], $width - 42);
        $this->radioOption($content, $x + 14, $top + 76, $choices[1][0], $choices[1][1], $width - 42);

        if ($showReplacement) {
            $this->textTop($content, 'Remplacement :', $x + 12, $top + 108, 9, 'F2');
            foreach (['Inutile', 'Souhaitable', 'Nécessaire'] as $index => $replacement) {
                $offset = $index * 72;
                $this->radio($content, $x + 14 + $offset, $top + 118, false);
                $this->textTop($content, $replacement, $x + 34 + $offset, $top + 120, 8);
            }
        } else {
            $this->radio($content, $x + 54, $top + 104, false);
            $this->textTop($content, 'Avec remplacement', $x + 74, $top + 106, 8);
            $this->radio($content, $x + 54, $top + 126, true);
            $this->textTop($content, 'Sans remplacement', $x + 74, $top + 128, 8);
        }

        $this->radioOption($content, $x + 14, $top + 146, $choices[2][0], $choices[2][1], $width - 42);
        $this->textTop($content, 'Observations :', $x + 12, $top + 172, 9, 'F2');
        $this->rectTop($content, $x + 12, $top + 182, $width - 24, 28);
        $this->multilineTextTop($content, $observation, $x + 18, $top + 190, $width - 36, 8, 'F1', 9, 3);
        $this->textTop($content, 'Date et signature : '.$date, $x + 12, $top + $height - 14, 9);
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
            if ($align === 'center') {
                $this->textTop(
                    $content,
                    $text,
                    $cursor + ($widths[$index] / 2),
                    $top + 17,
                    10,
                    $font,
                    $align
                );
            } else {
                $this->fittedMultilineTextTop(
                    $content,
                    $text,
                    $cursor + 10,
                    $top + 9,
                    $widths[$index] - 18,
                    $height - 12,
                    10,
                    8,
                    $font,
                    'left',
                    'top',
                    2
                );
            }
            $cursor += $widths[$index];
        }
    }

    private function radioOption(
        string &$content,
        float $x,
        float $top,
        string $label,
        bool $checked,
        float $labelWidth,
    ): void {
        $this->radio($content, $x, $top, $checked);
        $this->multilineTextTop($content, $label, $x + 22, $top + 1, $labelWidth, 10, 'F2', 11, 2);
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
            $this->pdfText($text)
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
        ?int $maxLines = null,
    ): void {
        $cursor = $top;
        $lines = $this->wrap($text, $maxWidth, $size);

        if ($maxLines !== null) {
            $lines = $this->limitLines($lines, $maxWidth, $size, $maxLines);
        }

        foreach ($lines as $line) {
            $this->textTop($content, $line, $x, $cursor, $size, $font);
            $cursor += $lineHeight;
        }
    }

    private function fittedMultilineTextTop(
        string &$content,
        string $text,
        float $x,
        float $top,
        float $maxWidth,
        float $maxHeight,
        float $preferredSize = 9,
        float $minSize = 6,
        string $font = 'F1',
        string $align = 'left',
        string $verticalAlign = 'top',
        ?int $maxLines = null,
        float $lineHeightRatio = 1.12,
    ): void {
        [$lines, $size, $lineHeight] = $this->fitTextBox(
            $text,
            $maxWidth,
            $maxHeight,
            $preferredSize,
            $minSize,
            $maxLines,
            $lineHeightRatio,
        );

        $contentHeight = count($lines) * $lineHeight;
        $startTop = $verticalAlign === 'middle'
            ? $top + max(0, ($maxHeight - $contentHeight) / 2)
            : $top;

        foreach ($lines as $index => $line) {
            $drawX = $align === 'center' ? $x + ($maxWidth / 2) : $x;
            $this->textTop($content, $line, $drawX, $startTop + ($index * $lineHeight), $size, $font, $align);
        }
    }

    /**
     * @return array<int, string>
     */
    private function wrap(string $text, float $maxWidth, float $size): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));

        if ($text === '') {
            return ['-'];
        }

        $lines = [];
        $paragraphs = preg_split('/\n+/', $text) ?: [];

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim(preg_replace('/[ \t]+/', ' ', $paragraph) ?? '');

            if ($paragraph === '') {
                continue;
            }

            $line = '';
            $words = preg_split('/\s+/', $paragraph) ?: [];

            foreach ($words as $word) {
                if ($this->textWidth($word, $size) > $maxWidth) {
                    if ($line !== '') {
                        $lines[] = $line;
                        $line = '';
                    }

                    array_push($lines, ...$this->splitWord($word, $maxWidth, $size));
                    continue;
                }

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
        }

        return $lines === [] ? ['-'] : $lines;
    }

    /**
     * @param array<int, string> $lines
     * @return array<int, string>
     */
    private function limitLines(array $lines, float $maxWidth, float $size, int $maxLines): array
    {
        if (count($lines) <= $maxLines) {
            return $lines;
        }

        $visible = array_slice($lines, 0, $maxLines);
        $visible[$maxLines - 1] = $this->truncateToWidth($visible[$maxLines - 1].'...', $maxWidth, $size);

        return $visible;
    }

    /**
     * @return array<int, string>
     */
    private function splitWord(string $word, float $maxWidth, float $size): array
    {
        $parts = [];
        $buffer = '';
        $characters = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($characters as $character) {
            $candidate = $buffer.$character;

            if ($buffer !== '' && $this->textWidth($candidate, $size) > $maxWidth) {
                $parts[] = $buffer;
                $buffer = $character;
                continue;
            }

            $buffer = $candidate;
        }

        if ($buffer !== '') {
            $parts[] = $buffer;
        }

        return $parts === [] ? [$word] : $parts;
    }

    private function truncateToWidth(string $line, float $maxWidth, float $size): string
    {
        $line = rtrim($line);

        if ($this->textWidth($line, $size) <= $maxWidth) {
            return $line;
        }

        while ($line !== '' && $this->textWidth($line.'...', $size) > $maxWidth) {
            $line = rtrim(mb_substr($line, 0, mb_strlen($line) - 1));
        }

        return $line === '' ? '...' : $line.'...';
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

    private function textWidth(string $text, float $size): float
    {
        $width = 0.0;
        $characters = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($characters as $character) {
            $width += match (true) {
                preg_match('/\s/u', $character) === 1 => $size * 0.28,
                preg_match('/[ilI\.,:;!\']/', $character) === 1 => $size * 0.24,
                preg_match('/[mwMW@#%&QGO0-9]/u', $character) === 1 => $size * 0.62,
                preg_match('/[A-ZÀ-Ý]/u', $character) === 1 => $size * 0.58,
                default => $size * 0.49,
            };
        }

        return $width;
    }

    /**
     * @return array{0: array<int, string>, 1: float, 2: float}
     */
    private function fitTextBox(
        string $text,
        float $maxWidth,
        float $maxHeight,
        float $preferredSize,
        float $minSize,
        ?int $maxLines = null,
        float $lineHeightRatio = 1.12,
    ): array {
        $size = $preferredSize;

        while ($size >= $minSize) {
            $lines = $this->wrap($text, $maxWidth, $size);

            if ($maxLines !== null && count($lines) > $maxLines) {
                $lines = $this->limitLines($lines, $maxWidth, $size, $maxLines);
            }

            $lineHeight = max($size + 0.8, $size * $lineHeightRatio);

            if ((count($lines) * $lineHeight) <= ($maxHeight + 0.1)) {
                return [$lines, $size, $lineHeight];
            }

            $size -= 0.5;
        }

        $size = $minSize;
        $lineHeight = max($size + 0.8, $size * $lineHeightRatio);
        $lines = $this->wrap($text, $maxWidth, $size);
        $allowedLines = max(1, (int) floor($maxHeight / $lineHeight));

        if ($maxLines !== null) {
            $allowedLines = min($allowedLines, $maxLines);
        }

        $lines = $this->limitLines($lines, $maxWidth, $size, max(1, $allowedLines));

        return [$lines, $size, $lineHeight];
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
