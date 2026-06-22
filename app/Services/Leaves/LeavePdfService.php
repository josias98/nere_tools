<?php

namespace App\Services\Leaves;

use App\Models\LeaveDocument;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeavePdfService
{
    public function generate(LeaveRequest $request): LeaveDocument
    {
        $request->loadMissing(['employee', 'leaveType']);

        $employee = $request->employee?->name() ?? 'Collaborateur';
        $fileName = sprintf(
            'Demande_conge_%s_%s_%s.pdf',
            $request->start_date->format('Ymd'),
            $request->end_date->format('Ymd'),
            Str::of($employee)->ascii()->replaceMatches('/[^A-Za-z0-9]+/', '_')->trim('_')
        );
        $path = 'leaves/'.$request->start_date->format('Y').'/'.$fileName;

        Storage::disk('local')->put($path, $this->pdf([
            'Nere Tools - Demande de conge',
            'Collaborateur: '.$employee,
            'Type: '.($request->leaveType?->name ?? 'Conge'),
            'Periode: '.$request->start_date->format('d/m/Y').' au '.$request->end_date->format('d/m/Y'),
            'Jours calendaires: '.$request->requested_days,
            'Statut: '.$request->status,
            'Decision: '.now()->format('d/m/Y H:i'),
            'Commentaire: '.($request->reviewer_comment ?: '-'),
        ]));

        return LeaveDocument::query()->updateOrCreate(
            ['leave_request_id' => $request->id],
            [
                'file_name' => $fileName,
                'local_path' => $path,
                'status' => 'generated',
                'generated_at' => now(),
            ]
        );
    }

    /**
     * @param array<int, string> $lines
     */
    private function pdf(array $lines): string
    {
        $text = "BT\n/F1 14 Tf\n72 760 Td\n";

        foreach ($lines as $index => $line) {
            $text .= ($index ? "0 -24 Td\n" : '').'('.$this->escape($line).") Tj\n";
        }

        $text .= "ET";
        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj\n",
            "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
            "5 0 obj << /Length ".strlen($text)." >> stream\n".$text."\nendstream endobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach (array_slice($offsets, 1) as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer << /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }

    private function escape(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], Str::ascii($text));
    }
}
