<?php

namespace Database\Seeders;

use App\Models\LeaveSetting;
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
            ['key' => 'annual_entitlement_days', 'value' => '30', 'value_type' => 'decimal'],
            ['key' => 'normal_use_after_months', 'value' => '12', 'value_type' => 'integer'],
            ['key' => 'working_hours_per_day', 'value' => '8', 'value_type' => 'decimal'],
            ['key' => 'working_weekdays', 'value' => '[1,2,3,4,5]', 'value_type' => 'json'],
            ['key' => 'LEAVE_CERTIFICATE_SIGNATORY_NAME', 'value' => config('leaves.certificate_signatory_name'), 'value_type' => 'string'],
            ['key' => 'LEAVE_CERTIFICATE_SIGNATORY_TITLE', 'value' => config('leaves.certificate_signatory_title'), 'value_type' => 'string'],
        ];

        foreach ($settings as $setting) {
            LeaveSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
