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
        Schema::create('leave_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_request_id')->constrained('leave_requests')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('local_path')->nullable();
            $table->string('sharepoint_drive_id')->nullable();
            $table->string('sharepoint_item_id')->nullable();
            $table->text('sharepoint_web_url')->nullable();
            $table->string('status')->default('generated');
            $table->text('error_message')->nullable();
            $table->dateTime('generated_at')->nullable();
            $table->dateTime('uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_documents');
    }
};
