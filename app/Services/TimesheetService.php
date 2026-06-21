<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TimesheetGeneration;
use App\Models\TimesheetGenerationFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class TimesheetService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function generate(array $data, User $user): TimesheetGeneration
    {
        $employees = Employee::query()
            ->whereIn('id', $data['employee_ids'])
            ->where('is_active', true)
            ->orderBy('display_name')
            ->get();

        if ($employees->isEmpty()) {
            throw new RuntimeException('Veuillez selectionner au moins un collaborateur actif.');
        }

        $year = (int) $data['year'];
        $startMonth = (int) $data['start_month'];
        $endMonth = (int) $data['end_month'];

        $generation = TimesheetGeneration::query()->create([
            'uuid' => (string) Str::uuid(),
            'period_start' => Carbon::create($year, $startMonth, 1),
            'period_end' => Carbon::create($year, $endMonth, 1)->endOfMonth(),
            'year' => $year,
            'period_label' => $this->periodLabel($year, $startMonth, $endMonth),
            'generated_by_user_id' => $user->id,
            'employee_count' => $employees->count(),
            'pdf_count' => 0,
            'status' => 'completed',
        ]);

        foreach (range($startMonth, $endMonth) as $month) {
            foreach ($employees as $employee) {
                $this->createFile($generation, $employee, $year, $month, $data);
            }
        }

        $generation->update([
            'pdf_count' => $generation->files()->count(),
            'zip_path' => $this->zip($generation),
        ]);

        return $generation->refresh();
    }

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

    public function signatureDate(int $year, int $month): Carbon
    {
        $date = Carbon::create($year, $month, 1)->endOfMonth()->addDay();

        while ($date->isWeekend()) {
            $date->addDay();
        }

        return $date;
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    public function columns(Employee $employee): array
    {
        $columns = [
            ['label' => 'IPAS', 'value' => $employee->ipas_rate],
            ['label' => "CATAL1,5\u{00B0}T", 'value' => $employee->catal_rate],
            ['label' => 'IPDE', 'value' => $employee->ipde_rate],
        ];

        if ($employee->requires_other_projects) {
            $columns[] = ['label' => 'Autres projets', 'value' => 100 - array_sum(array_column($columns, 'value'))];
        }

        $total = array_sum(array_column($columns, 'value'));
        if (abs($total - 100) > 0.01) {
            throw new RuntimeException("Le total analytique de {$employee->name()} doit etre egal a 100%.");
        }

        return $columns;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function createFile(TimesheetGeneration $generation, Employee $employee, int $year, int $month, array $options): TimesheetGenerationFile
    {
        $safeName = Str::of($employee->name())->ascii()->replaceMatches('/[^A-Za-z0-9]+/', '_')->trim('_');
        $fileName = sprintf('Feuille_de_temps_%d_%02d_%s.pdf', $year, $month, $safeName);
        $path = sprintf('timesheets/%d/%02d/%s/%s', $year, $month, $safeName, $fileName);

        Storage::disk('local')->put($path, $this->pdf($employee, $year, $month, $options));

        return $generation->files()->create([
            'employee_id' => $employee->id,
            'month' => $month,
            'year' => $year,
            'file_name' => $fileName,
            'file_path' => $path,
            'status' => 'generated',
        ]);
    }

    private function zip(TimesheetGeneration $generation): string
    {
        $zipPath = "timesheets/zips/{$generation->uuid}.zip";
        Storage::disk('local')->makeDirectory('timesheets/zips');

        $zip = new ZipArchive;
        $absolutePath = Storage::disk('local')->path($zipPath);

        if ($zip->open($absolutePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Impossible de creer le fichier ZIP.');
        }

        foreach ($generation->files as $file) {
            $zip->addFile(Storage::disk('local')->path($file->file_path), $file->file_name);
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function pdf(Employee $employee, int $year, int $month, array $options): string
    {
        $x = 28.2;
        $top = 718.0;
        $width = 539.0;
        $columns = $this->columns($employee);
        $includeComments = (bool) ($options['include_comments'] ?? true);
        $signatureDate = filled($options['signature_date'] ?? null)
            ? Carbon::parse($options['signature_date'])
            : $this->signatureDate($year, $month);
        $signatoryName = trim((string) ($options['signatory_name'] ?? '')) ?: $employee->signatory_name;
        $entity = trim((string) ($options['entity_label'] ?? '')) ?: $employee->entity;
        $commentsLabel = trim((string) ($options['comments_label'] ?? '')) ?: 'Commentaires / Details';
        $content = "0.55 0.55 0.55 RG\n0.45 w\n";

        $this->text($content, 'I&P', 48, 775, 26, 'F2', 'left', [0.34, 0.15, 0.04]);
        $this->text($content, 'INVESTISSEURS', 78, 773, 8, 'F2', 'left', [0.34, 0.15, 0.04]);
        $this->text($content, '& PARTENAIRES', 78, 763, 8, 'F2', 'left', [0.34, 0.15, 0.04]);
        $this->text($content, 'FEUILLE DE TEMPS', 297.5, 733, 18, 'F2', 'center');

        $rowY = $top;
        foreach ([
            ['Entite : ', $entity],
            ['Nom et prenom du salarie : ', $employee->name()],
            ['Lieu : ', $employee->location],
            ['Intitule du poste : ', $employee->job_title],
            ['Code analytique : ', $employee->analytic_code],
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
        $this->text($content, ($employee->signature_title ?: 'Signature du responsable hierarchique').' :', $splitX + 3, $rowY - 12, 10, 'F2');
        $this->text($content, 'Date : '.$signatureDate->format('d/m/Y'), $x + 3, $rowY - 36, 10, 'F3');
        $this->text($content, 'Nom et Prenom : '.$employee->name(), $x + 3, $rowY - 50, 10, 'F3');
        $this->text($content, 'Date : '.$signatureDate->format('d/m/Y'), $splitX + 3, $rowY - 36, 10, 'F3');
        $this->text($content, 'Nom et Prenom : '.$signatoryName, $splitX + 3, $rowY - 50, 10, 'F3');

        return $this->simplePdf($content);
    }

    private function simplePdf(string $content): string
    {
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595.276 841.89] /Resources << /Font << /F1 4 0 R /F2 5 0 R /F3 6 0 R >> >> /Contents 7 0 R >> endobj\n",
            "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >> endobj\n",
            "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >> endobj\n",
            "6 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Oblique /Encoding /WinAnsiEncoding >> endobj\n",
            '7 0 obj << /Length '.strlen($content)." >> stream\n{$content}endstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 8\n0000000000 65535 f \n";
        for ($i = 1; $i <= 7; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        return $pdf."trailer << /Size 8 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
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

    private function periodLabel(int $year, int $startMonth, int $endMonth): string
    {
        if ($startMonth === 1 && $endMonth === 6) {
            return "S1 {$year}";
        }

        if ($startMonth === 7 && $endMonth === 12) {
            return "S2 {$year}";
        }

        return sprintf('%d_%02d_%02d', $year, $startMonth, $endMonth);
    }
}
