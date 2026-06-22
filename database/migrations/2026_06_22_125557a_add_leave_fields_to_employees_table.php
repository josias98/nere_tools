<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('display_name');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete()->after('email');
            $table->date('hire_date')->nullable()->after('department_id');
            $table->boolean('leave_eligible')->default(true)->after('hire_date');
            $table->date('leave_reference_date')->nullable()->after('leave_eligible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn([
                'email',
                'department_id',
                'hire_date',
                'leave_eligible',
                'leave_reference_date',
            ]);
        });
    }
};
