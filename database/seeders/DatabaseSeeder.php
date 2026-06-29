<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveValidator;
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
        $this->call([
            DepartmentSeeder::class,
            LeaveSettingSeeder::class,
            LeaveTypeSeeder::class,
        ]);

        Tool::query()->updateOrCreate(
            ['slug' => 'timesheets'],
            [
                'name' => 'Feuilles de temps',
                'description' => 'Génération automatique des feuilles mensuelles en PDF.',
                'route' => '/timesheets',
                'status' => Tool::STATUS_ACTIVE,
                'required_role' => User::ROLE_FINANCE,
                'display_order' => 10,
            ],
        );

        Tool::query()->updateOrCreate(
            ['slug' => 'conges'],
            [
                'name' => 'Demandes de congé',
                'description' => 'Soumettre, suivre et valider les demandes de congé, avec calcul des soldes et notifications Office 365.',
                'route' => '/conges',
                'status' => Tool::STATUS_ACTIVE,
                'required_role' => User::ROLE_USER,
                'display_order' => 20,
            ],
        );

        Tool::query()->updateOrCreate(
            ['slug' => 'documents'],
            [
                'name' => 'Générateur de documents',
                'description' => 'Préparation de documents internes standardisés.',
                'route' => '/documents',
                'status' => Tool::STATUS_COMING_SOON,
                'required_role' => null,
                'display_order' => 30,
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
                'display_order' => 40,
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

        User::query()->updateOrCreate(
            ['email' => 'jdiamitani@nerecapital.com'],
            [
                'name' => 'Josias Mansour DIAMITANI',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ],
        );

        $departments = Department::all()->keyBy('slug');

        foreach ($this->employees($departments) as $employee) {
            Employee::query()->updateOrCreate(
                ['display_name' => $employee['display_name']],
                $employee,
            );
        }

        foreach (['Josias Mansour DIAMITANI', 'Germaine BAKO / NAGALO', 'Relwendé Gloria OUEDRAOGO', 'Job ZONGO'] as $validatorName) {
            $employee = Employee::query()->where('display_name', $validatorName)->first();

            if ($employee) {
                LeaveValidator::query()->updateOrCreate(
                    ['employee_id' => $employee->id, 'step_key' => $employee->display_name === 'Job ZONGO' ? 'dg' : 'hr', 'scope' => 'global'],
                    ['is_active' => true, 'notify_by_email' => true],
                );
            }
        }
    }

    /**
     * @param \Illuminate\Support\Collection $departments
     * @return array<int, array<string, mixed>>
     */
    private function employees($departments): array
    {
        return [
            [
                'first_name' => 'Josias',
                'last_name' => 'DIAMITANI',
                'display_name' => 'Josias Mansour DIAMITANI',
                'email' => 'jdiamitani@nerecapital.com',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Chargé de projet amorçage',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 100,
                'ipde_rate' => 0,
                'requires_other_projects' => false,
                'signature_title' => 'Signature du responsable hiérarchique',
                'signatory_name' => 'Alida OUEDRAOGO',
                'is_active' => true,
                'department_id' => $departments->get('acceleration')?->id,
            ],
            [
                'first_name' => 'Alida',
                'last_name' => 'OUEDRAOGO',
                'display_name' => 'Alida OUEDRAOGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Responsable d\'Amorçage',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 96,
                'ipde_rate' => 4,
                'requires_other_projects' => false,
                'signature_title' => 'Signature du responsable hiérarchique',
                'signatory_name' => 'ZONGO P. Job',
                'is_active' => true,
                'department_id' => $departments->get('acceleration')?->id,
            ],
            [
                'first_name' => 'Job',
                'last_name' => 'ZONGO',
                'display_name' => 'Job ZONGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Directeur général',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 12,
                'ipde_rate' => 0,
                'requires_other_projects' => true,
                'signature_title' => 'Signature du DAF',
                'signatory_name' => 'BAKO/NAGALO A Germaine',
                'is_active' => true,
                'department_id' => $departments->get('administratif-finance')?->id,
            ],
            [
                'first_name' => 'Germaine',
                'last_name' => 'BAKO / NAGALO',
                'display_name' => 'Germaine BAKO / NAGALO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Directrice Administrative et Financière',
                'analytic_code' => '1.1.1 Personnel technique',
                'ipas_rate' => 0,
                'catal_rate' => 20,
                'ipde_rate' => 0,
                'requires_other_projects' => true,
                'signature_title' => 'Signature du responsable hiérarchique',
                'signatory_name' => 'ZONGO P. Job',
                'is_active' => true,
                'department_id' => $departments->get('administratif-finance')?->id,
            ],
            [
                'first_name' => 'Aïcha',
                'last_name' => 'ZIO / SAVADOGO',
                'display_name' => 'Aïcha ZIO / SAVADOGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Directrice pôle études et conseils',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('conseil')?->id,
            ],
            [
                'first_name' => 'Aboubacar Sidiki',
                'last_name' => 'SANOU',
                'display_name' => 'Aboubacar Sidiki SANOU',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Responsable d\'investissement',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('equity')?->id,
            ],
            [
                'first_name' => 'Brice Gaël',
                'last_name' => 'SOUBEIGA',
                'display_name' => 'Brice Gaël SOUBEIGA',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Directeur d\'investissement',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('equity')?->id,
            ],
            [
                'first_name' => 'Samiratou Cyrielle',
                'last_name' => 'TRAORE',
                'display_name' => 'Samiratou Cyrielle TRAORE',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Chargée d\'investissement',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('equity')?->id,
            ],
            [
                'first_name' => 'Fabien',
                'last_name' => 'OUEDRAOGO',
                'display_name' => 'Fabien OUEDRAOGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Chauffeur-coursier',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('administratif-finance')?->id,
            ],
            [
                'first_name' => 'Relwendé Gloria',
                'last_name' => 'OUEDRAOGO',
                'display_name' => 'Relwendé Gloria OUEDRAOGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Assistante financière',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('administratif-finance')?->id,
            ],
            [
                'first_name' => 'Saint André',
                'last_name' => 'KOLAGBE',
                'display_name' => 'Saint André KOLAGBE',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Analyste financier',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('equity')?->id,
            ],
            [
                'first_name' => 'Samira',
                'last_name' => 'OUEDRAOGO',
                'display_name' => 'Samira OUEDRAOGO',
                'entity' => 'NERE CAPITAL PARTNERS',
                'location' => 'Ouagadougou',
                'job_title' => 'Analyste financier',
                'analytic_code' => '1.1.1 Personnel technique',
                'is_active' => true,
                'department_id' => $departments->get('conseil')?->id,
            ],
        ];
    }
}
