<?php

namespace App\Actions\Settings;

use App\Models\AuditLog;
use App\Models\LeaveSetting;
use App\Models\SettingVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateLeaveSettings
{
    public function execute(array $values, User $user, ?string $expectedUpdatedAt, ?string $reason, ?string $ip): void
    {
        DB::transaction(function () use ($values, $user, $expectedUpdatedAt, $reason, $ip): void {
            $current = LeaveSetting::query()->lockForUpdate()->get()->keyBy('key');
            $watched = $current->get('monthly_accrual_days');
            if ($expectedUpdatedAt && $watched && ! $watched->updated_at->equalTo($expectedUpdatedAt)) {
                throw ValidationException::withMessages(['settings' => 'Ces paramètres ont été modifiés par une autre personne. Rechargez la page avant de réessayer.']);
            }
            foreach ($values as $key => $value) {
                $old = $current->get($key);
                $newValue = (string) ($value ?? '');
                if ($old?->value === $newValue) {
                    continue;
                }
                $setting = LeaveSetting::query()->updateOrCreate(['key' => $key], ['value' => $newValue, 'value_type' => $old?->value_type ?? 'string']);
                SettingVersion::query()->create(['module' => 'conges', 'key' => $key, 'old_value' => $old?->value, 'new_value' => $newValue, 'value_type' => $setting->value_type, 'changed_by' => $user->id, 'reason' => $reason, 'effect' => 'future_operations', 'ip_address' => $ip]);
            }
            AuditLog::query()->create(['user_id' => $user->id, 'action' => 'settings.updated', 'auditable_type' => LeaveSetting::class, 'metadata' => ['module' => 'conges', 'keys' => array_keys($values), 'reason' => $reason, 'ip' => $ip, 'effect' => 'future_operations']]);
        });
    }
}
