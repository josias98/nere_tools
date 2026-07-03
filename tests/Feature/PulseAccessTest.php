<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PulseAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_pulse(): void
    {
        $this->get('/pulse')->assertRedirect('/login');
    }

    public function test_unauthorized_user_cannot_access_pulse(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($user)
            ->get('/pulse')
            ->assertForbidden();
    }

    public function test_admin_can_access_pulse(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get('/pulse')
            ->assertOk();
    }

    public function test_finance_user_can_access_pulse(): void
    {
        $finance = User::factory()->create(['role' => User::ROLE_FINANCE]);

        $this->actingAs($finance)
            ->get('/pulse')
            ->assertOk();
    }
}
