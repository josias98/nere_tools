<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Administratif et Finance', 'slug' => 'administratif-finance'],
            ['name' => 'Conseil', 'slug' => 'conseil'],
            ['name' => 'Equity', 'slug' => 'equity'],
            ['name' => 'Accélération', 'slug' => 'acceleration'],
        ];

        foreach ($departments as $department) {
            Department::query()->updateOrCreate(
                ['slug' => $department['slug']],
                $department
            );
        }
    }
}
