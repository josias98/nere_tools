<?php

namespace App\Services\Leaves;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;

class LeaveExportService
{
    public function __construct(private LeaveReportQuery $reports, private LeaveBalanceService $balances) {}

    public function generate(array $filters): string
    {
        $path = tempnam(sys_get_temp_dir(), 'nere-leaves-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);

        $employees = $this->reports->employees($filters)->get();
        $sheet = $writer->getCurrentSheet()->setName('Synthèse personnel');
        $headers = ['Salarié', 'Poste', 'Département', 'Ancienneté', 'Solde initial', 'Acquis', 'Majorations', 'Consommé', 'Planifié', 'En attente', 'Disponible'];
        $writer->addRow(Row::fromValues($headers));
        foreach ($employees as $employee) {
            $balance = $this->balances->getBalance($employee);
            $writer->addRow(Row::fromValues([$employee->name(), $employee->job_title, $employee->department?->name, $employee->hire_date?->diffInYears(now()), $balance['initial_balance'], $balance['accrued'], $balance['bonuses'], $balance['consumed'], $balance['planned'], $balance['pending'], $balance['available']]));
        }
        $sheet->setAutoFilter(new AutoFilter(0, 1, count($headers) - 1, max(1, $employees->count() + 1)))->setSheetView((new SheetView)->withFreezeRow(2));
        $sheet->setColumnWidthForRange(18, 1, count($headers));

        $requests = $this->reports->requests($filters)->get();
        $sheet = $writer->addNewSheetAndMakeItCurrent()->setName('Détail des absences');
        $headers = ['Référence', 'Salarié', 'Département', 'Type', 'Catégorie', 'Unité', 'Durée', 'Début', 'Fin', 'Reprise', 'Statut', 'Étape', 'Validateurs', 'Règle appliquée'];
        $writer->addRow(Row::fromValues($headers));
        foreach ($requests as $request) {
            $writer->addRow(Row::fromValues([$request->uuid, $request->employee?->name(), $request->employee?->department?->name, $request->leaveType?->name, $request->leaveType?->category?->value, $request->duration_unit, $request->requested_duration ?? $request->requested_days, $request->startLabel(), $request->endLabel(), $request->effective_return_at?->format('d/m/Y H:i'), $request->statusLabel(), $request->currentApproval?->step_label, $request->approvals->pluck('validatorUser.name')->filter()->join(', '), 'v'.($request->rule_snapshot['rule_version'] ?? 'historique')]));
        }
        $sheet->setAutoFilter(new AutoFilter(0, 1, count($headers) - 1, max(1, $requests->count() + 1)))->setSheetView((new SheetView)->withFreezeRow(2));
        $sheet->setColumnWidthForRange(18, 1, count($headers));

        $writer->addNewSheetAndMakeItCurrent()->setName('Paramètres de export');
        $writer->addRow(Row::fromValues(['Filtre', 'Valeur']));
        foreach ($filters as $key => $value) {
            $writer->addRow(Row::fromValues([$key, $value ?: 'Tous']));
        }
        $writer->close();

        return $path;
    }
}
