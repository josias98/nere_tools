<?php

namespace App\Services\Leaves;

use App\Models\Employee;
use App\Models\LeaveRequestDraft;
use App\Models\User;
use Illuminate\Support\Str;

class LeaveRequestDraftService
{
    public function save(User $user, ?string $uuid, array $payload, int $ttlMinutes = 120, ?Employee $employee = null, int $step = 1): LeaveRequestDraft
    {
        $draft = $uuid
            ? LeaveRequestDraft::query()->where('uuid', $uuid)->where('user_id', $user->id)->first()
            : null;

        $draft ??= new LeaveRequestDraft([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        $draft->forceFill([
            'employee_id' => $employee?->id ?? $draft->employee_id,
            'step' => max(1, min(5, $step)),
            'payload' => $this->safePayload($payload),
            'submitted_at' => null,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ])->save();

        $this->purgeExpired();

        return $draft;
    }

    public function latestFor(User $user, Employee $employee): ?LeaveRequestDraft
    {
        $this->purgeExpired();

        return LeaveRequestDraft::query()
            ->where('user_id', $user->id)
            ->where('employee_id', $employee->id)
            ->whereNull('submitted_at')
            ->where('expires_at', '>', now())
            ->latest('updated_at')
            ->first();
    }

    public function restore(User $user, string $uuid): ?LeaveRequestDraft
    {
        $draft = LeaveRequestDraft::query()
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->whereNull('submitted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $draft) {
            return null;
        }

        $draft->forceFill(['expires_at' => now()->addMinutes(120)])->save();

        return $draft;
    }

    public function discard(User $user, ?string $uuid): void
    {
        if (! $uuid) {
            return;
        }

        LeaveRequestDraft::query()->where('uuid', $uuid)->where('user_id', $user->id)->delete();
    }

    public function markSubmitted(User $user, ?string $uuid): void
    {
        if (! $uuid) {
            return;
        }

        LeaveRequestDraft::query()
            ->where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->update(['submitted_at' => now(), 'expires_at' => now()]);
    }

    private function safePayload(array $payload): array
    {
        unset($payload['attachments']);

        return $payload;
    }

    private function purgeExpired(): void
    {
        LeaveRequestDraft::query()->where('expires_at', '<', now())->delete();
    }
}
