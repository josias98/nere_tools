<?php

namespace Tests\Feature;

use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\LeaveTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTypeCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_keeps_only_the_seven_approved_leave_types_active(): void
    {
        $legacyType = LeaveType::query()->create(['slug' => 'legacy', 'name' => 'Ancien type']);

        $this->seed(LeaveTypeSeeder::class);

        $this->assertSame([
            'Congé administratif',
            'Congé de maternité',
            'Congé de paternité',
            'Décès (préciser le lien de parenté)',
            'Congé de maladie',
            'Accident de travail',
            'Autre (motif à préciser)',
        ], LeaveType::query()->where('is_active', true)->orderBy('id')->pluck('name')->all());
        $this->assertFalse($legacyType->fresh()->is_active);
    }

    public function test_admin_only_lists_the_approved_leave_types(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        LeaveType::query()->create(['slug' => 'legacy', 'name' => 'Ancien type']);
        $this->seed(LeaveTypeSeeder::class);
        LeaveType::query()->where('slug', 'annual_leave')->update(['is_active' => false]);

        $response = $this->actingAs($admin)->get(route('admin.leaves.index', ['section' => 'rules']))->assertOk();

        foreach (LeaveType::query()->whereIn('slug', LeaveType::CATALOG_SLUGS)->pluck('name') as $name) {
            $response->assertSee($name);
        }
        $response->assertDontSee('Ancien type');
    }

    public function test_catalog_migration_updates_an_existing_installation(): void
    {
        foreach (LeaveType::CATALOG_SLUGS as $slug) {
            LeaveType::query()->create(['slug' => $slug, 'name' => $slug]);
        }
        $legacyType = LeaveType::query()->create(['slug' => 'legacy', 'name' => 'Ancien type']);

        $migration = require database_path('migrations/2026_07_18_000001_simplify_leave_type_catalog.php');
        $migration->up();

        $this->assertSame([
            'Congé administratif',
            'Congé de maternité',
            'Congé de paternité',
            'Décès (préciser le lien de parenté)',
            'Congé de maladie',
            'Accident de travail',
            'Autre (motif à préciser)',
        ], LeaveType::query()->where('is_active', true)->orderBy('id')->pluck('name')->all());
        $this->assertFalse($legacyType->fresh()->is_active);
    }
}
