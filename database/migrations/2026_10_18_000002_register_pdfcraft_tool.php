<?php

use App\Models\Tool;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Tool::query()->updateOrCreate(['slug' => 'pdfcraft'], [
            'name' => 'Outils PDF',
            'description' => 'Créer, convertir, organiser et sécuriser des documents PDF.',
            'route' => '/pdfcraft',
            'status' => Tool::STATUS_ACTIVE,
            'required_role' => null,
            'display_order' => 25,
        ]);
    }

    public function down(): void
    {
        Tool::query()->where('slug', 'pdfcraft')->delete();
    }
};
