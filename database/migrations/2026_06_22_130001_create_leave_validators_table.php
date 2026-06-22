<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_validators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('scope')->default('global');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('target_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->boolean('notify_by_email')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['employee_id', 'scope', 'department_id', 'target_employee_id'], 'leave_validators_unique_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_validators');
    }
};
