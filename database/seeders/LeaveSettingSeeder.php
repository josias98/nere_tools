<?php

namespace Database\Seeders;

use App\Models\LeaveSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'monthly_accrual_days', 'value' => '2.5', 'value_type' => 'decimal'],
            ['key' => 'day_counting_method', 'value' => 'calendar_days', 'value_type' => 'string'],
            ['key' => 'accrual_policy', 'value' => 'end_of_month', 'value_type' => 'string'],
            ['key' => 'allow_negative_balance_override', 'value' => 'false', 'value_type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            LeaveSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
