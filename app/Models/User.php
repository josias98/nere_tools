<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'microsoft_id', 'role', 'is_active', 'last_login_at'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_FINANCE = 'finance';

    public const ROLE_DIRECTION = 'direction';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_USER = 'user';

    /**
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'email', 'email');
    }

    public function tools(): BelongsToMany
    {
        return $this->belongsToMany(Tool::class, 'tool_user_access')
            ->withPivot('can_access')
            ->withTimestamps();
    }

    public function canAccessAdmin(): bool
    {
        if ($this->hasRole(self::ROLE_ADMIN)) {
            return true;
        }

        return $this->employee?->department?->slug === 'administratif-finance';
    }

    public function canAccessTool(string $slug): bool
    {
        if ($this->hasRole(self::ROLE_ADMIN)) {
            return true;
        }

        $tool = Tool::query()->where('slug', $slug)->where('status', Tool::STATUS_ACTIVE)->first();

        if (! $tool) {
            return match ($slug) {
                'timesheets' => $this->hasAnyRole([self::ROLE_ADMIN, self::ROLE_FINANCE, self::ROLE_DIRECTION]),
                'conges' => $this->is_active,
                default => false,
            };
        }

        $access = ToolUserAccess::query()
            ->where('user_id', $this->id)
            ->where('tool_id', $tool->id)
            ->first();

        if ($access) {
            return $access->can_access;
        }

        return $tool->required_role ? $this->hasRole($tool->required_role) : true;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
