<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdfcraft_files', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('operation_id');
            $table->string('tool_slug', 100);
            $table->string('kind', 16);
            $table->string('original_name', 255);
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->char('sha256', 64);
            $table->string('file_path', 512);
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'operation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pdfcraft_files');
    }
};
