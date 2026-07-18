<?php

namespace App\Services\Admin;

use App\Enums\ConfigurationHealth;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Models\TimesheetGeneration;
use Illuminate\Support\Collection;

class ConfigurationDiagnosticService
{
    /** @return Collection<int, array<string, mixed>> */
    public function issues(): Collection
    {
        return collect([
            LeaveValidator::query()->where('is_active', true)->exists() ? null : $this->issue(
                'conges', 'critical', 'Aucun validateur actif',
                'Les nouvelles demandes ne peuvent pas terminer leur circuit de validation.',
                route('admin.leaves.index', ['section' => 'workflow'])
            ),
            LeaveType::query()->where('is_active', true)->exists() ? null : $this->issue(
                'conges', 'error', 'Aucun type de congé actif',
                'Les collaborateurs ne peuvent pas créer de demande exploitable.',
                route('admin.leaves.index', ['section' => 'rules'])
            ),
            NotificationLog::query()->whereIn('status', ['failed', 'skipped'])->exists() ? $this->issue(
                'conges', 'warning', 'Des notifications sont en échec',
                'Vérifiez les destinataires et la disponibilité de Microsoft 365.',
                route('admin.leaves.notifications.index')
            ) : null,
            TimesheetGeneration::query()->exists() ? null : $this->issue(
                'timesheets', 'info', 'Aucune feuille de temps générée',
                "Le module est disponible, mais aucun parcours complet n'a encore été enregistré.",
                route('timesheets.index')
            ),
        ])->filter()->values();
    }

    /** @return array<string, array<string, mixed>> */
    public function modules(Collection $issues): array
    {
        return [
            'conges' => $this->module('Congés', 'calendar-range', route('admin.leaves.index'), $issues->where('module', 'conges')),
            'timesheets' => $this->module('Feuilles de temps', 'file-text', route('timesheets.index'), $issues->where('module', 'timesheets')),
        ];
    }

    /** @param Collection<int, array<string, mixed>> $issues
     * @return array<string, mixed>
     */
    private function module(string $name, string $icon, string $url, Collection $issues): array
    {
        $health = match (true) {
            $issues->contains('severity', 'critical') => ConfigurationHealth::Critical,
            $issues->contains('severity', 'error') => ConfigurationHealth::Incomplete,
            $issues->isNotEmpty() => ConfigurationHealth::Attention,
            default => ConfigurationHealth::Configured,
        };

        return compact('name', 'icon', 'url', 'health', 'issues');
    }

    /** @return array<string, string> */
    private function issue(string $module, string $severity, string $title, string $description, string $url): array
    {
        return compact('module', 'severity', 'title', 'description', 'url');
    }
}
