<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\Admin\ConfigurationDiagnosticService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, ConfigurationDiagnosticService $diagnostics): View
    {
        $issues = $diagnostics->issues();

        if ($module = $request->string('module')->toString()) {
            $issues = $issues->where('module', $module);
        }

        if ($status = $request->string('status')->toString()) {
            $issues = $issues->filter(fn (array $issue): bool => match ($status) {
                'error' => in_array($issue['severity'], ['critical', 'error'], true),
                default => $issue['severity'] === $status,
            });
        }

        return view('admin.index', [
            'modules' => $diagnostics->modules($diagnostics->issues()),
            'issues' => $issues,
            'activeUsers' => User::query()->where('is_active', true)->count(),
            'recentChanges' => AuditLog::query()->latest()->limit(8)->get(),
            'breadcrumbs' => [
                ['label' => 'Accueil', 'url' => route('dashboard')],
                ['label' => 'Administration'],
            ],
        ]);
    }
}
