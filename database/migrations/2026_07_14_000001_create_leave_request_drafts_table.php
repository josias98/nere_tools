<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_request_drafts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('step')->default(1);
            $table->json('payload');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['user_id', 'employee_id', 'submitted_at', 'expires_at'], 'leave_request_drafts_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_request_drafts');
    }
};
