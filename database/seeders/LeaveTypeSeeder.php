<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Congé annuel',
                'slug' => 'annual_leave',
                'is_paid' => true,
                'counts_against_balance' => true,
                'requires_attachment' => false,
                'is_active' => true,
            ]
        ];

        foreach ($types as $type) {
            LeaveType::query()->updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
