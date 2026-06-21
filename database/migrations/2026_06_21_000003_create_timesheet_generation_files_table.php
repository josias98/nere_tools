<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_generation_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('timesheet_generation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained();
            $table->integer('month');
            $table->integer('year');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('status')->default('generated');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_generation_files');
    }
};
