<?php

namespace App\Modules\Timesheets\Support;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TimesheetPdfRenderer
{
    /**
     * @return array<int, array{label: string, start: Carbon, end: Carbon}>
     */
    public function weeks(int $year, int $month): array
    {
        $cursor = Carbon::create($year, $month, 1);
        $end = $cursor->copy()->endOfMonth();
        $weeks = [];

        while ($cursor->lte($end)) {
            $weekEnd = $cursor->copy()->next(Carbon::SUNDAY);
            if ($weekEnd->gt($end) || $cursor->isSunday()) {
                $weekEnd = $cursor->isSunday() ? $cursor->copy() : $end->copy();
            }

            $weeks[] = [
                'label' => 'Semaine '.(count($weeks) + 1),
                'start' => $cursor->copy(),
                'end' => $weekEnd->copy(),
            ];

            $cursor = $weekEnd->copy()->addDay();
        }

        return $weeks;
    }

    public function signatureDate(int $year, int $month, array $excludedDates = []): Carbon
    {
        $date = Carbon::create($year, $month, 1)->endOfMonth()->addDay();

        while ($date->isWeekend() || in_array($date->format('Y-m-d'), $excludedDates, true)) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    public function columns(Employee $employee, array $options = []): array
    {
        $columns = [
            ['label' => 'IPAS', 'value' => $this->rateValue($options, 'ipas_rate', $employee->ipas_rate)],
            ['label' => "CATAL1,5\u{00B0}T", 'value' => $this->rateValue($options, 'catal_rate', $employee->catal_rate)],
            ['label' => 'IPDE', 'value' => $this->rateValue($options, 'ipde_rate', $employee->ipde_rate)],
        ];

        $otherProjectsRate = $this->rateValue($options, 'other_projects_rate');
        if ($otherProjectsRate !== null) {
            $columns[] = ['label' => 'Autres projets', 'value' => $otherProjectsRate];
        } elseif ($employee->requires_other_projects) {
            $columns[] = ['label' => 'Autres projets', 'value' => 100 - array_sum(array_column($columns, 'value'))];
        }

        return $columns;
    }

    /**
     * @param  array<int, array{year: int, month: int}>  $periods
     * @param  array<string, mixed>  $options
     */
    public function render(Employee $employee, array $periods, array $options): string
    {
        $logo = $this->logoAsset();

        return $this->simplePdf(array_map(
            fn (array $period): string => $this->pdfPage($employee, $period['year'], $period['month'], $options, $logo !== null),
            $periods
        ), $logo);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function pdfPage(Employee $employee, int $year, int $month, array $options, bool $hasLogo): string
    {
        $x = 28.2;
        $top = 718.0;
        $width = 539.0;
        $columns = $this->columns($employee, $options);
        $includeComments = (bool) ($options['include_comments'] ?? true);
        $signatureDate = filled($options['signature_date'] ?? null)
            ? Carbon::parse($options['signature_date'])
            : $this->signatureDate($year, $month, $options['excluded_signature_dates'] ?? []);
        $employeeName = $this->employeeDisplayName($employee, $options);
        $signatoryName = trim((string) ($options['signatory_name'] ?? '')) ?: $employee->signatory_name;
        $entity = trim((string) ($options['entity_name'] ?? $options['entity_label'] ?? '')) ?: $employee->entity;
        $location = trim((string) ($options['location'] ?? '')) ?: $employee->location;
        $jobTitle = trim((string) ($options['function_title'] ?? '')) ?: $employee->job_title;
        $analyticCode = trim((string) ($options['analytic_code'] ?? '')) ?: $employee->analytic_code;
        $signatureTitle = trim((string) ($options['signature_title'] ?? '')) ?: ($employee->signature_title ?: 'Signature du responsable hierarchique');
        $commentsLabel = trim((string) ($options['comments_label'] ?? '')) ?: 'Commentaires / Details';
        $content = "0.55 0.55 0.55 RG\n0.45 w\n";

        if ($hasLogo) {
            $this->drawImage($content, 'Im1', 28.2, 736, 198, 81.78);
        } else {
            $this->text($content, 'I&P', 48, 775, 26, 'F2', 'left', [0.34, 0.15, 0.04]);
            $this->text($content, 'INVESTISSEURS', 78, 773, 8, 'F2', 'left', [0.34, 0.15, 0.04]);
            $this->text($content, '& PARTENAIRES', 78, 763, 8, 'F2', 'left', [0.34, 0.15, 0.04]);
        }
        $this->text($content, 'FEUILLE DE TEMPS', 297.5, 733, 18, 'F2', 'center');

        $rowY = $top;
        foreach ([
            ['Entite : ', $entity],
            ['Nom et prenom du salarie : ', $employeeName],
            ['Lieu : ', $location],
            ['Intitule du poste : ', $jobTitle],
            ['Code analytique : ', $analyticCode],
        ] as [$label, $value]) {
            $this->rect($content, $x, $rowY - 17, $width, 17);
            $this->labelValue($content, $x + 3, $rowY - 12, $label, (string) $value);
            $rowY -= 17;
        }

        $this->fillRect($content, $x, $rowY - 23, $width, 23, 0.78);
        $this->rect($content, $x, $rowY - 23, $width, 23);
        $rowY -= 23;

        $this->rect($content, $x, $rowY - 51, $width, 51);
        $this->line($content, $x + 216, $rowY, $x + 216, $rowY - 51);
        $this->labelValue($content, $x + 3, $rowY - 29, 'Mois : ', $this->monthName($month));
        $this->labelValue($content, $x + 219, $rowY - 29, 'Annee : ', (string) $year);
        $rowY -= 51;

        $weeks = $this->weeks($year, $month);
        $tableWidths = $this->tableWidths(count($columns), $includeComments);
        $headers = array_merge(['Semaine', 'Date de debut', 'Date de fin'], array_column($columns, 'label'));
        if ($includeComments) {
            $headers[] = $commentsLabel;
        }

        $this->drawGridRow($content, $x, $rowY, $tableWidths, 28);
        $cx = $x;
        foreach ($headers as $i => $header) {
            if ($includeComments && $i === count($headers) - 1) {
                $parts = array_map('trim', explode('/', $header, 2));
                if (count($parts) === 2) {
                    $this->text($content, $parts[0].' /', $cx + ($tableWidths[$i] / 2), $rowY - 13, 9, 'F2', 'center');
                    $this->text($content, $parts[1], $cx + ($tableWidths[$i] / 2), $rowY - 23, 9, 'F2', 'center');
                } else {
                    $this->text($content, $header, $cx + ($tableWidths[$i] / 2), $rowY - 17, 9, 'F2', 'center');
                }
            } else {
                $this->text($content, $header, $cx + ($tableWidths[$i] / 2), $rowY - 17, 9, 'F2', 'center');
            }
            $cx += $tableWidths[$i];
        }
        $rowY -= 28;

        foreach ($weeks as $i => $week) {
            $this->drawGridRow($content, $x, $rowY, $tableWidths, 17);
            $values = [
                (string) ($i + 1),
                $week['start']->format('d/m/Y'),
                $week['end']->format('d/m/Y'),
                ...array_map(fn (array $column): string => $this->percent($column['value']), $columns),
            ];
            if ($includeComments) {
                $values[] = '';
            }

            $cx = $x;
            foreach ($values as $j => $value) {
                $this->text($content, $value, $cx + ($tableWidths[$j] / 2), $rowY - 12, 9, 'F1', 'center');
                $cx += $tableWidths[$j];
            }
            $rowY -= 17;
        }

        $this->drawGridRow($content, $x, $rowY, [$tableWidths[0] + $tableWidths[1] + $tableWidths[2], ...array_slice($tableWidths, 3)], 17);
        $this->text($content, 'Moyenne', $x + 3, $rowY - 12, 9, 'F2');
        $cx = $x + $tableWidths[0] + $tableWidths[1] + $tableWidths[2];
        foreach ($columns as $i => $column) {
            $this->text($content, $this->percent($column['value']), $cx + ($tableWidths[$i + 3] / 2), $rowY - 12, 9, 'F2', 'center');
            $cx += $tableWidths[$i + 3];
        }
        $rowY -= 17;

        $signatureHeight = 130;
        $splitX = $x + 287;
        $this->rect($content, $x, $rowY - $signatureHeight, $width, $signatureHeight);
        $this->line($content, $splitX, $rowY, $splitX, $rowY - $signatureHeight);
        $this->text($content, 'Signature du salarie :', $x + 3, $rowY - 12, 10, 'F2');
        $this->text($content, $signatureTitle.' :', $splitX + 3, $rowY - 12, 10, 'F2');
        $this->text($content, 'Date : '.$signatureDate->format('d/m/Y'), $x + 3, $rowY - 36, 10, 'F3');
        $this->text($content, 'Nom et Prenom : '.$employeeName, $x + 3, $rowY - 50, 10, 'F3');
        $this->text($content, 'Date : '.$signatureDate->format('d/m/Y'), $splitX + 3, $rowY - 36, 10, 'F3');
        $this->text($content, 'Nom et Prenom : '.$signatoryName, $splitX + 3, $rowY - 50, 10, 'F3');

        return $content;
    }

    /**
     * @param  array<int, string>  $pages
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
            $objects[$pageId] = "{$pageId} 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595.276 841.89] /Resources << /Font << /F1 {$font1} 0 R /F2 {$font2} 0 R /F3 {$font3} 0 R >>{$xObject} >> /Contents {$contentId} 0 R >> endobj\n";
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

    private function rect(string &$content, float $x, float $y, float $w, float $h): void
    {
        $content .= sprintf("%.2F %.2F %.2F %.2F re S\n", $x, $y, $w, $h);
    }

    private function fillRect(string &$content, float $x, float $y, float $w, float $h, float $gray): void
    {
        $content .= sprintf("%.2F %.2F %.2F rg %.2F %.2F %.2F %.2F re f\n0 g\n", $gray, $gray, $gray, $x, $y, $w, $h);
    }

    private function line(string &$content, float $x1, float $y1, float $x2, float $y2): void
    {
        $content .= sprintf("%.2F %.2F m %.2F %.2F l S\n", $x1, $y1, $x2, $y2);
    }

    private function drawImage(string &$content, string $name, float $x, float $y, float $w, float $h): void
    {
        $content .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n", $w, $h, $x, $y, $name);
    }

    private function text(string &$content, string $text, float $x, float $y, int $size = 10, string $font = 'F1', string $align = 'left', array $rgb = [0, 0, 0]): void
    {
        if ($align !== 'left') {
            $x -= $this->textWidth($text, $size) / ($align === 'center' ? 2 : 1);
        }

        $content .= sprintf("%.2F %.2F %.2F rg BT /%s %d Tf %.2F %.2F Td (%s) Tj ET\n0 g\n", $rgb[0], $rgb[1], $rgb[2], $font, $size, $x, $y, $this->pdfText($text));
    }

    private function labelValue(string &$content, float $x, float $y, string $label, string $value): void
    {
        $this->text($content, $label, $x, $y, 9, 'F2');
        $this->text($content, $value, $x + $this->textWidth($label, 9), $y, 9);
    }

    /**
     * @param  array<int, float>  $widths
     */
    private function drawGridRow(string &$content, float $x, float $top, array $widths, float $height): void
    {
        $this->rect($content, $x, $top - $height, array_sum($widths), $height);
        $cx = $x;
        foreach (array_slice($widths, 0, -1) as $width) {
            $cx += $width;
            $this->line($content, $cx, $top, $cx, $top - $height);
        }
    }

    /**
     * @return array<int, float>
     */
    private function tableWidths(int $projectColumnCount, bool $includeComments): array
    {
        if ($projectColumnCount === 3 && $includeComments) {
            return [51, 82, 83, 70, 85, 71, 97];
        }

        $fixed = [51, 82, 83];
        $comments = $includeComments ? 97 : 0;
        $projectWidth = (539 - array_sum($fixed) - $comments) / $projectColumnCount;

        return [...$fixed, ...array_fill(0, $projectColumnCount, $projectWidth), ...($includeComments ? [$comments] : [])];
    }

    private function textWidth(string $text, int $size): float
    {
        return mb_strlen($text) * $size * 0.48;
    }

    private function pdfText(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT', $text);
        $text = $encoded === false ? Str::ascii($text) : $encoded;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private function monthName(int $month): string
    {
        return [
            1 => 'Janvier',
            2 => 'Fevrier',
            3 => 'Mars',
            4 => 'Avril',
            5 => 'Mai',
            6 => 'Juin',
            7 => 'Juillet',
            8 => 'Aout',
            9 => 'Septembre',
            10 => 'Octobre',
            11 => 'Novembre',
            12 => 'Decembre',
        ][$month];
    }

    private function percent(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').'%';
    }

    private function rateValue(array $options, string $key, ?float $fallback = null): ?float
    {
        if (! array_key_exists($key, $options) || $options[$key] === '' || $options[$key] === null) {
            return $fallback;
        }

        return (float) $options[$key];
    }

    private function employeeDisplayName(Employee $employee, array $options): string
    {
        $name = trim((string) ($options['employee_signature_name'] ?? ''));
        if ($name !== '') {
            return $name;
        }

        $composed = trim((string) ($options['first_name'] ?? '').' '.(string) ($options['last_name'] ?? ''));

        return $composed !== '' ? $composed : $employee->name();
    }

    /**
     * @return array{width: int, height: int, data: string}|null
     */
    private function logoAsset(): ?array
    {
        $path = public_path('brand/ip-investisseurs-partenaires.jpg');
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
}
