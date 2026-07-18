<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $catalog = [
            'annual_leave' => 'Congé administratif',
            'maternity' => 'Congé de maternité',
            'internal_paternity' => 'Congé de paternité',
            'family_event' => 'Décès (préciser le lien de parenté)',
            'non_occupational_illness' => 'Congé de maladie',
            'occupational_injury' => 'Accident de travail',
            'other_absence' => 'Autre (motif à préciser)',
        ];

        foreach ($catalog as $slug => $name) {
            DB::table('leave_types')->where('slug', $slug)->update([
                'name' => $name,
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        DB::table('leave_types')->whereNotIn('slug', array_keys($catalog))->update([
            'is_active' => false,
            'canonical_leave_type_id' => null,
            'updated_at' => now(),
        ]);

        $annualId = DB::table('leave_types')->where('slug', 'annual_leave')->value('id');
        DB::table('leave_types')->whereIn('slug', ['administrative_leave', 'conge_administratif'])->update([
            'canonical_leave_type_id' => $annualId,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        $previousCatalog = [
            'annual_leave' => 'Congé annuel',
            'maternity' => 'Maternité',
            'internal_paternity' => 'Paternité',
            'family_event' => 'Événement familial',
            'non_occupational_illness' => 'Maladie non professionnelle',
            'occupational_injury' => 'Accident du travail ou maladie professionnelle',
            'child_care' => 'Entretien d’un enfant',
            'seriously_ill_child' => 'Maladie grave d’un enfant',
            'sick_spouse_assistance' => 'Assistance au conjoint malade',
            'union_authorization' => 'Autorisation syndicale',
            'training_representation' => 'Formation ou représentation nationale',
            'availability' => 'Mise en disponibilité',
            'special_leave' => 'Congé spécial',
            'other_absence' => 'Autre absence configurable',
        ];

        foreach ($previousCatalog as $slug => $name) {
            DB::table('leave_types')->where('slug', $slug)->update([
                'name' => $name,
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }
    }
};
