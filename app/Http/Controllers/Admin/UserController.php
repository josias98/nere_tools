<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with(['employee.department', 'tools'])->orderBy('name')->get(),
            'tools' => $this->tools(),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'is_active' => ['nullable', 'boolean'],
            'tool_ids' => ['array'],
            'tool_ids.*' => ['exists:tools,id'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->syncTools($user, $data['tool_ids'] ?? []);

        return redirect()->route('admin.users.edit', $user)->with('success', 'Utilisateur créé.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'managedUser' => $user->load(['employee.department', 'tools']),
            'tools' => $this->tools(),
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'is_active' => ['nullable', 'boolean'],
            'tool_ids' => ['array'],
            'tool_ids.*' => ['exists:tools,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ]);
        $this->syncTools($user, $data['tool_ids'] ?? []);

        return back()->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->forceFill(['is_active' => false])->save();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur désactivé.');
    }

    /**
     * @return array<string, string>
     */
    private function roles(): array
    {
        return [
            User::ROLE_ADMIN => 'Super admin',
            User::ROLE_FINANCE => 'Finance',
            User::ROLE_DIRECTION => 'Direction',
            User::ROLE_MANAGER => 'Manager',
            User::ROLE_USER => 'Utilisateur',
        ];
    }

    /**
     * @return Collection<int, Tool>
     */
    private function tools()
    {
        return Tool::query()->where('status', Tool::STATUS_ACTIVE)->orderBy('display_order')->get();
    }

    /**
     * @param  array<int, int|string>  $toolIds
     */
    private function syncTools(User $user, array $toolIds): void
    {
        $activeToolIds = $this->tools()->pluck('id')->map(fn ($id): int => (int) $id)->all();
        $allowedIds = array_map('intval', $toolIds);
        $sync = [];

        foreach ($activeToolIds as $toolId) {
            $sync[$toolId] = ['can_access' => in_array($toolId, $allowedIds, true)];
        }

        $user->tools()->sync($sync);
    }
}
