<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leaves\UpdateLeaveTypeRequest;
use App\Models\AuditLog;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class LeaveTypeController extends Controller
{
    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): RedirectResponse
    {
        DB::transaction(function () use ($request, $leaveType): void {
            $before = $leaveType->toArray();
            $data = $request->validated();
            foreach (['is_paid', 'counts_against_balance', 'requires_attachment', 'is_active'] as $boolean) {
                $data[$boolean] = $request->boolean($boolean);
            }
            $effectiveFrom = Carbon::parse($data['effective_from']);
            $version = ((int) $leaveType->rules()->max('version')) + 1;
            $leaveType->rules()->whereNull('effective_until')->update(['effective_until' => $effectiveFrom->copy()->subDay()->toDateString()]);
            $configuration = $data;
            unset($configuration['effective_from']);
            $leaveType->rules()->create(['version' => $version, 'effective_from' => $effectiveFrom, 'configuration' => $configuration, 'is_active' => true, 'created_by_user_id' => $request->user()->id]);
            if ($effectiveFrom->isToday() || $effectiveFrom->isPast()) {
                $leaveType->update($data);
            }
            AuditLog::query()->create(['user_id' => $request->user()->id, 'action' => 'leave.rule_changed', 'auditable_type' => LeaveType::class, 'auditable_id' => $leaveType->id, 'metadata' => ['before' => $before, 'version' => $version]]);
        });

        return back()->with('success', 'Nouvelle version de règle enregistrée pour les futures demandes.');
    }
}
