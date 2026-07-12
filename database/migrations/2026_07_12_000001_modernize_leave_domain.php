<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table): void {
            $table->string('category')->default('leave');
            $table->string('unit')->default('calendar_day');
            $table->decimal('quota', 10, 2)->nullable();
            $table->decimal('maximum_duration', 10, 2)->nullable();
            $table->unsignedInteger('notice_hours')->default(0);
            $table->unsignedTinyInteger('maximum_renewals')->default(0);
            $table->string('requester_scope')->default('employee');
            $table->text('eligibility_rules')->nullable();
            $table->string('legal_reference')->nullable();
            $table->date('effective_from')->nullable();
            $table->json('conditional_fields')->nullable();
            $table->unsignedBigInteger('canonical_leave_type_id')->nullable()->index();
        });

        Schema::create('leave_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->json('configuration');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['leave_type_id', 'version']);
        });

        Schema::create('leave_holidays', function (Blueprint $table): void {
            $table->id();
            $table->date('date')->unique();
            $table->string('name');
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
        });

        Schema::create('leave_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('sha256', 64);
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->decimal('requested_duration', 10, 2)->nullable();
            $table->string('duration_unit')->default('calendar_day');
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('effective_return_at')->nullable();
            $table->json('rule_snapshot')->nullable();
            $table->string('relationship')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('replacement_needed')->default(false);
            $table->unsignedBigInteger('replacement_employee_id')->nullable()->index();
            $table->string('location')->nullable();
            $table->string('contact')->nullable();
            $table->string('salary_impact')->nullable();
            $table->text('hr_override_reason')->nullable();
        });

        DB::table('leave_requests')->update([
            'requested_duration' => DB::raw('requested_days'),
            'start_at' => DB::raw('start_date'),
            'end_at' => DB::raw('end_date'),
        ]);

        DB::table('leave_requests')->orderBy('id')->each(function ($request): void {
            $type = DB::table('leave_types')->find($request->leave_type_id);
            DB::table('leave_requests')->where('id', $request->id)->update([
                'effective_return_at' => Carbon::parse($request->end_date)->addDay()->startOfDay(),
                'rule_snapshot' => json_encode(['type' => ['id' => $type?->id, 'name' => $type?->name, 'slug' => $type?->slug, 'unit' => $type?->unit ?? 'calendar_day', 'counts_against_balance' => (bool) ($type?->counts_against_balance ?? true)], 'rule_version' => null, 'captured_at' => Carbon::parse($request->created_at ?? now())->toIso8601String()], JSON_THROW_ON_ERROR),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_attachments');
        Schema::dropIfExists('leave_holidays');
        Schema::dropIfExists('leave_rules');
        Schema::table('leave_requests', function (Blueprint $table): void {
            $table->dropIndex(['replacement_employee_id']);
            $table->dropColumn(['requested_duration', 'duration_unit', 'start_at', 'end_at', 'effective_return_at', 'rule_snapshot', 'relationship', 'reason', 'replacement_needed', 'replacement_employee_id', 'location', 'contact', 'salary_impact', 'hr_override_reason']);
        });
        Schema::table('leave_types', function (Blueprint $table): void {
            $table->dropIndex(['canonical_leave_type_id']);
            $table->dropColumn(['category', 'unit', 'quota', 'maximum_duration', 'notice_hours', 'maximum_renewals', 'requester_scope', 'eligibility_rules', 'legal_reference', 'effective_from', 'conditional_fields', 'canonical_leave_type_id']);
        });
    }
};
