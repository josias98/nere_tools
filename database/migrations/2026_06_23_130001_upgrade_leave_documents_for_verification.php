<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_documents', function (Blueprint $table): void {
            $table->string('document_reference')->nullable()->after('leave_request_id');
            $table->string('file_path')->nullable()->after('file_name');
            $table->string('disk')->default('local')->after('file_path');
            $table->string('sha256_hash', 64)->nullable()->after('disk');
            $table->string('verification_token', 96)->nullable()->after('sha256_hash');
            $table->text('verification_url')->nullable()->after('verification_token');
            $table->foreignId('replaced_by_document_id')->nullable()->after('status')->constrained('leave_documents')->nullOnDelete();
            $table->text('status_reason')->nullable()->after('replaced_by_document_id');
            $table->foreignId('generated_by')->nullable()->after('generated_at')->constrained('users')->nullOnDelete();
            $table->foreignId('signed_by')->nullable()->after('generated_by')->constrained('users')->nullOnDelete();
            $table->dateTime('signed_at')->nullable()->after('signed_by');
        });

        DB::table('leave_documents')
            ->where('status', 'generated')
            ->update(['status' => 'active']);

        DB::table('leave_documents')
            ->whereNull('file_path')
            ->update(['file_path' => DB::raw('local_path')]);

        Schema::table('leave_documents', function (Blueprint $table): void {
            $table->unique('document_reference');
            $table->unique('verification_token');
            $table->index('sha256_hash');
            $table->index(['leave_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('leave_documents', function (Blueprint $table): void {
            $table->dropUnique(['document_reference']);
            $table->dropUnique(['verification_token']);
            $table->dropIndex(['sha256_hash']);
            $table->dropIndex(['leave_request_id', 'status']);
            $table->dropForeign(['replaced_by_document_id']);
            $table->dropForeign(['generated_by']);
            $table->dropForeign(['signed_by']);
            $table->dropColumn([
                'document_reference',
                'file_path',
                'disk',
                'sha256_hash',
                'verification_token',
                'verification_url',
                'replaced_by_document_id',
                'status_reason',
                'generated_by',
                'signed_by',
                'signed_at',
            ]);
        });
    }
};
