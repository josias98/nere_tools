<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_documents', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('file_name');
            $table->string('original_path');
            $table->string('original_sha256', 64);
            $table->unsignedInteger('original_size');
            $table->unsignedSmallInteger('page_count');
            $table->string('signed_path')->nullable();
            $table->string('signed_sha256', 64)->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('signature_mode', 20)->nullable();
            $table->string('certificate_subject')->nullable();
            $table->string('certificate_issuer')->nullable();
            $table->string('certificate_sha256', 64)->nullable();
            $table->date('certificate_valid_until')->nullable();
            $table->text('reason')->nullable();
            $table->string('consent_version', 40)->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
        DB::table('tools')->insertOrIgnore([
            'slug' => 'signatures', 'name' => 'Signature PDF', 'route' => '/signatures',
            'description' => 'Relisez et signez vos PDF avec le certificat de Néré, puis conservez les documents signés.',
            'status' => 'active', 'required_role' => null, 'display_order' => 30,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_documents');
        DB::table('tools')->where('slug', 'signatures')->delete();
    }
};
