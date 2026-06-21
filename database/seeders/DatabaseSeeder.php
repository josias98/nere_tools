<?php

namespace Database\Seeders;

use App\Models\Employee;
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

        foreach ($this->employees() as $employee) {
            Employee::query()->updateOrCreate(
                ['display_name' => $employee['display_name']],
                $employee,
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function employees(): array
    {
        return [
            [
                'first_name' => 'Josias',
                'last_name' => 'DIAMITANI',
                'display_name' => 'Josias Mansour DIAMITANI',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Charge de projet amorcage',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 100,
                'ipde_rate' => 0,
                'requires_other_projects' => false,
                'signature_title' => 'Signature du responsable hierarchique',
                'signatory_name' => 'Alida OUEDRAOGO',
                'is_active' => true,
            ],
            [
                'first_name' => 'Alida',
                'last_name' => 'OUEDRAOGO',
                'display_name' => 'Alida OUEDRAOGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Responsable de projet amorcage',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 96,
                'ipde_rate' => 4,
                'requires_other_projects' => false,
                'signature_title' => 'Signature du responsable hierarchique',
                'signatory_name' => 'ZONGO P. Job',
                'is_active' => true,
            ],
            [
                'first_name' => 'Job',
                'last_name' => 'ZONGO',
                'display_name' => 'Job ZONGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'DG Fonds',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 12,
                'ipde_rate' => 0,
                'requires_other_projects' => true,
                'signature_title' => 'Signature du DAF',
                'signatory_name' => 'BAKO/NAGALO A Germaine',
                'is_active' => true,
            ],
            [
                'first_name' => 'Germaine',
                'last_name' => 'BAKO/NAGALO',
                'display_name' => 'Germaine BAKO/NAGALO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'DAF',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 20,
                'ipde_rate' => 0,
                'requires_other_projects' => true,
                'signature_title' => 'Signature du responsable hierarchique',
                'signatory_name' => 'ZONGO P. Job',
                'is_active' => true,
            ],
        ];
    }
}
