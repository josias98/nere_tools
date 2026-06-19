<?php

namespace Database\Seeders;

use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Tool::query()->updateOrCreate(
            ['slug' => 'timesheets'],
            [
                'name' => 'Feuilles de temps',
                'description' => 'Generation automatique des feuilles mensuelles en PDF.',
                'route' => '/timesheets',
                'status' => Tool::STATUS_ACTIVE,
                'required_role' => User::ROLE_FINANCE,
                'display_order' => 10,
            ],
        );

        Tool::query()->updateOrCreate(
            ['slug' => 'documents'],
            [
                'name' => 'Generateur de documents',
                'description' => 'Preparation de documents internes standardises.',
                'route' => '/documents',
                'status' => Tool::STATUS_COMING_SOON,
                'required_role' => null,
                'display_order' => 20,
            ],
        );

        Tool::query()->updateOrCreate(
            ['slug' => 'reporting'],
            [
                'name' => 'Reporting portefeuille',
                'description' => 'Suivi et restitution des indicateurs portefeuille.',
                'route' => '/reporting',
                'status' => Tool::STATUS_COMING_SOON,
                'required_role' => null,
                'display_order' => 30,
            ],
        );

        if ($email = env('INITIAL_ADMIN_EMAIL')) {
            User::query()->updateOrCreate(
                ['email' => strtolower($email)],
                [
                    'name' => env('INITIAL_ADMIN_NAME', 'Administrateur Nere Tools'),
                    'role' => User::ROLE_ADMIN,
                    'is_active' => true,
                ],
            );
        }
    }
}
