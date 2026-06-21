<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_generations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->date('period_start');
            $table->date('period_end');
            $table->integer('year');
            $table->string('period_label');
            $table->foreignId('generated_by_user_id')->constrained('users');
            $table->integer('employee_count')->default(0);
            $table->integer('pdf_count')->default(0);
            $table->string('zip_path')->nullable();
            $table->string('status')->default('completed');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_generations');
    }
};
