<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_validators', function (Blueprint $table): void {
            $table->string('step_key')->default('supervisor')->after('employee_id');
            $table->dropUnique('leave_validators_unique_scope');
            $table->unique(['employee_id', 'step_key', 'scope', 'department_id', 'target_employee_id'], 'leave_validators_unique_scope');
        });
    }

    public function down(): void
    {
        Schema::table('leave_validators', function (Blueprint $table): void {
            $table->dropUnique('leave_validators_unique_scope');
            $table->unique(['employee_id', 'scope', 'department_id', 'target_employee_id'], 'leave_validators_unique_scope');
            $table->dropColumn('step_key');
        });
    }
};
