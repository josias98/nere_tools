<?php

namespace App\Http\Controllers\Leaves\Admin;

use App\Actions\Settings\UpdateLeaveSettings;
use App\Http\Controllers\Controller;
use App\Http\Requests\Leaves\UpdateLeaveSettingsRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveHoliday;
use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use App\Models\LeaveValidator;
use App\Models\NotificationLog;
use App\Services\Leaves\LeaveBalanceService;
use App\Services\Leaves\LeaveNotificationService;
use App\Services\Leaves\LeaveReportQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveAdminController extends Controller
{
    public function index(Request $request, LeaveReportQuery $reports, LeaveBalanceService $balances): View
    {
        $filters = [
            'status' => (string) $request->string('status'),
            'step' => (string) $request->string('step'),
            'department_id' => (string) $request->string('department_id'),
            'employee_id' => (string) $request->string('employee_id'),
            'from' => (string) $request->string('from'),
            'to' => (string) $request->string('to'),
        ];

        $requests = $reports->requests($filters)
            ->paginate(15)
            ->withQueryString();

        $today = now()->toDateString();
        $summary = [
            'absent_today' => LeaveRequest::query()->where('status', 'approved')->whereDate('start_date', '<=', $today)->whereDate('end_date', '>=', $today)->count(),
            'next_30_days' => LeaveRequest::query()->where('status', 'approved')->whereBetween('start_date', [now()->addDay(), now()->addDays(30)])->count(),
            'pending' => LeaveRequest::query()->whereIn('status', ['pending_supervisor', 'pending_hr', 'pending_dg'])->count(),
            'missing_attachments' => LeaveRequest::query()->whereHas('leaveType', fn ($q) => $q->where('requires_attachment', true))->doesntHave('attachments')->count(),
        ];

        return view('leaves.admin.index', [
            'employees' => Employee::query()->with('department')->orderBy('display_name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'settings' => LeaveSetting::query()->orderBy('key')->get()->keyBy('key'),
            'validators' => LeaveValidator::query()->with(['employee', 'department', 'targetEmployee'])->latest()->get(),
            'requests' => $requests,
            'filters' => $filters,
            'signatureExists' => is_file(storage_path('app/signatures/dg-signature.png')) || is_file(public_path('brand/dg-signature.png')),
            'summary' => $summary,
            'personnel' => $reports->employees($filters)->paginate(20, ['*'], 'personnel_page')->withQueryString()->through(fn ($employee) => ['employee' => $employee, 'balance' => $balances->getBalance($employee)]),
            'leaveTypes' => LeaveType::query()->with('rules')->orderBy('name')->get(),
            'holidays' => LeaveHoliday::query()->orderBy('date')->get(),
        ]);
    }

    public function notifications(Request $request): View
    {
        $filters = [
            'status' => (string) $request->string('status'),
            'event' => (string) $request->string('event'),
            'search' => trim((string) $request->string('search')),
        ];

        $notifications = NotificationLog::query()
            ->with('related')
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['event'] !== '', fn ($query) => $query->where('event', $filters['event']))
            ->when($filters['search'] !== '', function ($query) use ($filters): void {
                $query->where(function ($query) use ($filters): void {
                    $like = '%'.$filters['search'].'%';
                    $query->where('subject', 'like', $like)
                        ->orWhere('event', 'like', $like)
                        ->orWhere('error_message', 'like', $like);
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = NotificationLog::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('leaves.admin.notifications', [
            'notifications' => $notifications,
            'filters' => $filters,
            'counts' => $counts,
            'events' => NotificationLog::query()
                ->whereNotNull('event')
                ->distinct()
                ->orderBy('event')
                ->pluck('event'),
        ]);
    }

    public function retryNotification(
        NotificationLog $notificationLog,
        LeaveNotificationService $notificationService,
    ): RedirectResponse {
        if (! in_array($notificationLog->status, ['failed', 'skipped'], true)) {
            return back()->withErrors([
                'notification' => 'Seules les notifications en echec ou ignorees peuvent etre relancees.',
            ]);
        }

        if ($notificationLog->related_type !== LeaveRequest::class || ! $notificationLog->related_id) {
            return back()->withErrors([
                'notification' => 'Cette notification ne peut pas etre regeneree en toute securite.',
            ]);
        }

        $leaveRequest = LeaveRequest::query()->find($notificationLog->related_id);

        if (! $leaveRequest) {
            return back()->withErrors([
                'notification' => 'La demande de conge liee est introuvable.',
            ]);
        }

        $event = $notificationLog->event;

        if ($event === 'leave.submitted' || str_starts_with((string) $event, 'leave.pending_')) {
            if (! in_array($leaveRequest->status, ['pending_supervisor', 'pending_hr', 'pending_dg'], true)) {
                return back()->withErrors([
                    'notification' => "La demande n'est plus en attente: relance bloquee pour eviter un envoi obsolete.",
                ]);
            }

            $notificationService->notifyCurrentStepValidators($leaveRequest);
        } elseif ($event === 'leave.approved' || $event === 'leave.rejected') {
            if ($leaveRequest->status !== str_replace('leave.', '', $event)) {
                return back()->withErrors([
                    'notification' => "L'etat actuel de la demande ne correspond plus a cette notification.",
                ]);
            }

            $notificationService->requestDecided($leaveRequest);
        } else {
            return back()->withErrors([
                'notification' => 'La relance web est reservee aux notifications du module Conges.',
            ]);
        }

        return back()->with('success', 'Notification relancee.');
    }

    public function updateEmployee(Request $request, Employee $employee): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'unique:employees,email,'.$employee->id],
            'hire_date' => ['nullable', 'date'],
            'leave_eligible' => ['nullable', 'boolean'],
        ]);

        $data['leave_eligible'] = $request->boolean('leave_eligible');
        $employee->update($data);

        return back()->with('success', 'Collaborateur mis à jour.');
    }

    public function updateSettings(UpdateLeaveSettingsRequest $request, UpdateLeaveSettings $action): RedirectResponse
    {
        $data = $request->validated();
        $expectedUpdatedAt = $data['settings_updated_at'] ?? null;
        $reason = $data['change_reason'] ?? null;
        unset($data['settings_updated_at'], $data['change_reason']);

        $action->execute($data, $request->user(), $expectedUpdatedAt, $reason, $request->ip());

        return back()->with('success', 'Paramètres enregistrés.');
    }
}
