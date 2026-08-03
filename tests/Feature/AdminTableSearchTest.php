<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTableSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_admin_index_table_exposes_a_working_search_input(): void
    {
        $admin = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.test',
            'password' => 'secret-password',
            'role' => 'admin',
            'auth_provider' => 'local',
            'active' => true,
        ]);

        $routes = [
            'admin.posts.index',
            'admin.pages.index',
            'admin.staff.index',
            'admin.simpeg.index',
            'admin.galleries.index',
            'admin.banners.index',
            'admin.links.index',
            'admin.infographics.index',
            'admin.events.index',
            'admin.users.index',
            'admin.messages.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($admin)
                ->get(route($route, ['q' => 'kata-yang-dicari']))
                ->assertOk()
                ->assertSee('name="q"', false);
        }
    }
}
