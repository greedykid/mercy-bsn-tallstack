<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_render_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        file_put_contents(storage_path('dashboard_dump.html'), $response->getContent());
        $response->assertSee('Dasbor Pemantauan Akreditasi');
    }

    public function test_authenticated_pic_can_render_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'pic',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dasbor Pemantauan Akreditasi');
    }
}
