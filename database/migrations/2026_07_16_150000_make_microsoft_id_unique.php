<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->select('microsoft_id')
            ->whereNotNull('microsoft_id')
            ->groupBy('microsoft_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('microsoft_id')
            ->each(function (string $microsoftId): void {
                $duplicateIds = DB::table('users')
                    ->where('microsoft_id', $microsoftId)
                    ->orderBy('id')
                    ->pluck('id')
                    ->slice(1);

                DB::table('users')->whereIn('id', $duplicateIds)->update(['microsoft_id' => null]);
            });

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('microsoft_id', 'users_microsoft_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_microsoft_id_unique');
        });
    }
};
