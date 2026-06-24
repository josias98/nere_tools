<?php

use App\Models\LeaveSetting;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Models\TimesheetGeneration;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Leaves\Admin\LeaveAdminController;
use App\Http\Controllers\Leaves\Admin\LeaveDocumentAdminController;
use App\Http\Controllers\Leaves\Admin\LeaveImportController;
use App\Http\Controllers\Leaves\Admin\LeaveValidatorController;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin-area'])->group(function (): void {
    Route::get('/admin', function () {
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');

        $roleLabels = [
            User::ROLE_ADMIN => 'Super admin',
            User::ROLE_FINANCE => 'Finance',
            User::ROLE_DIRECTION => 'Direction',
            User::ROLE_MANAGER => 'Manager',
            User::ROLE_USER => 'Utilisateur',
        ];

        $roleSummaries = [
            User::ROLE_ADMIN => "Acces complet aux reglages sensibles et aux arbitrages d'acces.",
            User::ROLE_FINANCE => 'Prioritaire pour les modules de production et les suivis financiers.',
            User::ROLE_DIRECTION => 'Vue metier transversale pour piloter et valider rapidement.',
            User::ROLE_MANAGER => "Supervision d'equipe et circuits de validation metier.",
            User::ROLE_USER => 'Acces au portail et aux modules explicitement autorises.',
        ];

        $moduleCards = Tool::query()
            ->where('status', Tool::STATUS_ACTIVE)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->map(function (Tool $tool) use ($roleLabels): array {
                return match ($tool->slug) {
                    'conges' => [
                        'name' => $tool->name,
                        'icon' => 'calendar-range',
                        'summary' => 'Reglez les droits RH, les validateurs, les regles de solde et les emails metier depuis un seul espace.',
                        'action_label' => 'Ouvrir les reglages du module',
                        'action_url' => route('admin.leaves.index'),
                        'secondary_label' => 'Voir les notifications',
                        'secondary_url' => route('admin.leaves.notifications.index'),
                        'facts' => [
                            'Regles actives' => (string) LeaveSetting::query()->count(),
                            'Validateurs actifs' => (string) LeaveValidator::query()->where('is_active', true)->count(),
                            'Alertes email a suivre' => (string) NotificationLog::query()->whereIn('status', ['failed', 'skipped'])->count(),
                        ],
                    ],
                    'timesheets' => [
                        'name' => $tool->name,
                        'icon' => 'file-text',
                        'summary' => "Les droits d'acces se gerent globalement. Les options de production restent reglees directement dans le module au moment de la generation.",
                        'action_label' => 'Ouvrir le module',
                        'action_url' => $tool->route,
                        'secondary_label' => "Voir l'historique",
                        'secondary_url' => route('timesheets.history'),
                        'facts' => [
                            'Back-office dedie' => 'Pas encore',
                            'Lots deja generes' => (string) TimesheetGeneration::query()->count(),
                            'Cadre recommande' => 'Finance et direction',
                        ],
                    ],
                    default => [
                        'name' => $tool->name,
                        'icon' => 'layout-grid',
                        'summary' => $tool->description ?: 'Module actif dans le portail.',
                        'action_label' => 'Ouvrir le module',
                        'action_url' => $tool->route,
                        'secondary_label' => null,
                        'secondary_url' => null,
                        'facts' => [
                            'Reglages dedies' => 'A definir',
                            'Role minimum' => $tool->required_role ? ($roleLabels[$tool->required_role] ?? $tool->required_role) : 'Aucun role impose',
                            'Statut' => 'Actif',
                        ],
                    ],
                };
            });

        return view('admin.index', [
            'userStats' => [
                [
                    'label' => 'Comptes actifs',
                    'value' => (string) User::query()->where('is_active', true)->count(),
                    'icon' => 'check-circle-2',
                    'helper' => 'Utilisateurs qui peuvent encore se connecter au portail.',
                ],
                [
                    'label' => 'Roles utilises',
                    'value' => (string) $roleCounts->count(),
                    'icon' => 'shield-check',
                    'helper' => 'Niveaux de droits actuellement attribues dans la base.',
                ],
                [
                    'label' => 'Modules actifs',
                    'value' => (string) $moduleCards->count(),
                    'icon' => 'layout-grid',
                    'helper' => 'Espaces visibles depuis le dashboard et administres ici quand necessaire.',
                ],
            ],
            'roleCards' => collect($roleLabels)->map(
                fn (string $label, string $role): array => [
                    'label' => $label,
                    'count' => (int) ($roleCounts[$role] ?? 0),
                    'summary' => $roleSummaries[$role],
                    'icon' => match ($role) {
                        User::ROLE_ADMIN => 'shield-check',
                        User::ROLE_FINANCE => 'wallet',
                        User::ROLE_DIRECTION => 'briefcase',
                        User::ROLE_MANAGER => 'users',
                        default => 'user',
                    },
                ]
            )->values(),
            'moduleCards' => $moduleCards,
        ]);
    })->name('admin.index');

    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/conges', [LeaveAdminController::class, 'index'])->name('admin.leaves.index');
    Route::get('/admin/conges/notifications', [LeaveAdminController::class, 'notifications'])->name('admin.leaves.notifications.index');
    Route::post('/admin/conges/notifications/{notificationLog}/retry', [LeaveAdminController::class, 'retryNotification'])->name('admin.leaves.notifications.retry');
    Route::post('/admin/conges/documents/{document}/revoke', [LeaveDocumentAdminController::class, 'revoke'])->name('admin.leaves.documents.revoke');
    Route::post('/admin/conges/{leaveRequest:uuid}/regenerate-document', [LeaveDocumentAdminController::class, 'regenerate'])->name('admin.leaves.documents.regenerate');
    Route::put('/admin/conges/collaborateurs/{employee}', [LeaveAdminController::class, 'updateEmployee'])->name('admin.leaves.employees.update');
    Route::put('/admin/conges/parametres', [LeaveAdminController::class, 'updateSettings'])->name('admin.leaves.settings.update');
    Route::post('/admin/conges/validateurs', [LeaveValidatorController::class, 'store'])->name('admin.leaves.validators.store');
    Route::put('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'update'])->name('admin.leaves.validators.update');
    Route::delete('/admin/conges/validateurs/{validator}', [LeaveValidatorController::class, 'destroy'])->name('admin.leaves.validators.destroy');
    Route::post('/admin/conges/import', LeaveImportController::class)->name('admin.leaves.import');
});
