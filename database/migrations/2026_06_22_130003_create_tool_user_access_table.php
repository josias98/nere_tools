<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_user_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_access')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'tool_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_user_access');
    }
};
