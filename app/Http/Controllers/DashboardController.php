<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $tools = Tool::query()
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        if ($tools->isEmpty()) {
            $tools = $this->defaultTools();
        }

        return view('dashboard', [
            'tools' => $tools,
            'accessibleRoutes' => $this->accessibleRoutes(),
        ]);
    }

    /**
     * @return Collection<int, object>
     */
    private function defaultTools(): Collection
    {
        return collect([
            (object) [
                'name' => 'Feuilles de temps',
                'slug' => 'timesheets',
                'description' => 'Generation automatique des feuilles mensuelles en PDF.',
                'route' => '/timesheets',
                'status' => Tool::STATUS_ACTIVE,
            ],
            (object) [
                'name' => 'Generateur de documents',
                'slug' => 'documents',
                'description' => 'Preparation de documents internes standardises.',
                'route' => '/documents',
                'status' => Tool::STATUS_COMING_SOON,
            ],
            (object) [
                'name' => 'Reporting portefeuille',
                'slug' => 'reporting',
                'description' => 'Suivi et restitution des indicateurs portefeuille.',
                'route' => '/reporting',
                'status' => Tool::STATUS_COMING_SOON,
            ],
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function accessibleRoutes(): array
    {
        return [
            '/timesheets' => [User::ROLE_ADMIN, User::ROLE_FINANCE, User::ROLE_DIRECTION],
            '/admin' => [User::ROLE_ADMIN],
        ];
    }
}
