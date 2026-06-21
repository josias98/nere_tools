<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (! Schema::hasColumn('employees', 'ipas_rate')) {
                $table->decimal('ipas_rate', 5, 2)->default(0)->after('analytic_code');
                $table->decimal('catal_rate', 5, 2)->default(0)->after('ipas_rate');
                $table->decimal('ipde_rate', 5, 2)->default(0)->after('catal_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            foreach (['ipas_rate', 'catal_rate', 'ipde_rate'] as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
