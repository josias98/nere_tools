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
            ->where('status', Tool::STATUS_ACTIVE)
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
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function accessibleRoutes(): array
    {
        return [
            '/timesheets' => [User::ROLE_ADMIN, User::ROLE_FINANCE, User::ROLE_DIRECTION],
            '/conges' => [User::ROLE_ADMIN, User::ROLE_FINANCE, User::ROLE_DIRECTION, User::ROLE_MANAGER, User::ROLE_USER],
            '/admin' => [User::ROLE_ADMIN],
        ];
    }
}
