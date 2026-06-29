<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->unsignedTinyInteger('step_order');
            $table->string('step_key');
            $table->string('step_label');
            $table->foreignId('validator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('validator_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->text('comment')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->unique(['leave_request_id', 'step_key']);
            $table->index(['leave_request_id', 'status', 'step_order'], 'leave_approvals_current_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_approvals');
    }
};
