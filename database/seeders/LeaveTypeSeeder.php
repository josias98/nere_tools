<?php

namespace Database\Seeders;

use App\Models\LeaveRule;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['annual_leave', 'Congé annuel', 'leave', 'calendar_day', true, true, false, 30, null, 0, 'Code du travail burkinabè', ['monthly_accrual' => 2.5, 'normal_use_after_months' => 12, 'seniority_bonuses' => ['20' => 2, '25' => 4, '30' => 6], 'minor_worker_extra_unpaid_days' => 30, 'under_22_child_bonus_working_days' => 2]],
            ['family_event', 'Événement familial', 'permission', 'working_day', true, false, true, 15, 15, 0, 'Plafond commun annuel configurable', ['event_durations' => []]],
            ['maternity', 'Maternité', 'leave', 'week', true, false, true, null, 17, 0, 'Durée légale : 14 semaines, extension médicale maximale : 3 semaines', ['base_weeks' => 14, 'medical_extension_weeks' => 3, 'breastfeeding_hours_per_day' => 1.5, 'breastfeeding_months_after_return' => 15]],
            ['internal_paternity', 'Paternité (dispositif interne)', 'permission', 'working_day', true, false, true, null, null, 0, 'Avantage interne configurable — ne constitue pas un droit légal autonome', ['internal_policy' => true]],
            ['non_occupational_illness', 'Maladie non professionnelle', 'leave', 'calendar_day', true, false, true, null, null, 0, 'Maintien de salaire selon ancienneté', ['salary_caps_months' => [['max_years' => 0, 'full' => 1, 'half' => 1], ['max_years' => 5, 'full' => 1, 'half' => 3], ['max_years' => 10, 'full' => 3, 'half' => 3], ['max_years' => 15, 'full' => 4, 'half' => 4], ['max_years' => null, 'full' => 5, 'half' => 5]]]],
            ['occupational_injury', 'Accident du travail ou maladie professionnelle', 'leave', 'calendar_day', true, false, true, null, null, 0, null, []],
            ['child_care', 'Entretien d’un enfant', 'leave', 'month', false, false, true, null, 6, 1, null, []],
            ['seriously_ill_child', 'Maladie grave d’un enfant', 'leave', 'month', false, false, true, null, 12, 1, null, []],
            ['sick_spouse_assistance', 'Assistance au conjoint malade', 'leave', 'month', false, false, true, null, 3, 1, null, []],
            ['union_authorization', 'Autorisation syndicale', 'authorization', 'working_day', true, false, false, 20, null, 72, null, []],
            ['training_representation', 'Formation ou représentation nationale', 'permission', 'working_day', false, false, true, 15, null, 0, null, []],
            ['availability', 'Mise en disponibilité', 'suspension', 'month', false, false, true, null, null, 0, null, []],
            ['special_leave', 'Congé spécial', 'leave', 'working_day', true, false, true, null, null, 0, null, []],
            ['other_absence', 'Autre absence configurable', 'leave', 'working_day', false, false, false, null, null, 0, null, []],
        ];

        foreach ($types as [$slug, $name, $category, $unit, $paid, $balance, $attachment, $quota, $maximum, $notice, $reference, $configuration]) {
            $type = LeaveType::query()->updateOrCreate(['slug' => $slug], compact('name', 'category', 'unit') + [
                'is_paid' => $paid, 'counts_against_balance' => $balance, 'requires_attachment' => $attachment,
                'quota' => $quota, 'maximum_duration' => $maximum, 'notice_hours' => $notice,
                'maximum_renewals' => in_array($slug, ['child_care', 'seriously_ill_child', 'sick_spouse_assistance'], true) ? 1 : 0,
                'legal_reference' => $reference, 'effective_from' => '2026-01-01', 'is_active' => true,
            ]);
            LeaveRule::query()->updateOrCreate(['leave_type_id' => $type->id, 'version' => 1], [
                'effective_from' => '2026-01-01', 'configuration' => $configuration, 'is_active' => true,
            ]);
        }

        $annual = LeaveType::query()->where('slug', 'annual_leave')->value('id');
        LeaveType::query()->whereIn('slug', ['administrative_leave', 'conge_administratif'])->update(['is_active' => false, 'canonical_leave_type_id' => $annual]);
    }
}
