<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_can_render_deadlines_page(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('deadlines.index'));

        $response->assertOk();
        $response->assertSee('Notifikasi Deadline Siklus Akreditasi');
    }

    public function test_authenticated_pic_can_render_deadlines_page(): void
    {
        $user = User::factory()->create([
            'role' => 'pic',
        ]);

        $response = $this->actingAs($user)->get(route('deadlines.index'));

        $response->assertOk();
        $response->assertSee('Notifikasi Deadline Siklus Akreditasi');
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('deadlines.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_persistent_cycle_warning_banner_renders_across_menus_when_urgent(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        \App\Models\Lpk::create([
            'no_reg' => 'LP-TEST-001',
            'registration_number' => 'LP-TEST-001',
            'name' => 'Lab Uji Due Cycle',
            'schema' => \App\Enums\KanSchema::LP,
            'status' => \App\Enums\LpkStatus::ACTIVE,
            'certificate_date' => now()->subMonths(14)->toDateString(),
            'expired_at' => now()->addYears(3)->toDateString(),
        ]);

        // Banner should render on dashboard
        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Peringatan Siklus Pengawasan Akreditasi:')
            ->assertSee('Tinjau LP Jatuh Tempo');

        // Banner should also render on lpks.index
        $this->actingAs($user)->get(route('lpks.index'))
            ->assertOk()
            ->assertSee('Peringatan Siklus Pengawasan Akreditasi:')
            ->assertSee('Tinjau LP Jatuh Tempo');

        // Banner should also render on assessments.index
        $this->actingAs($user)->get(route('assessments.index'))
            ->assertOk()
            ->assertSee('Peringatan Siklus Pengawasan Akreditasi:')
            ->assertSee('Tinjau LP Jatuh Tempo');
    }

    public function test_notification_dropdown_renders_responsive_elements_and_backdrop(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Notifikasi Siklus Akreditasi');
        $response->assertSee('Buka Pusat Notifikasi Deadline');
        // Mobile backdrop and fixed responsive positioning
        $response->assertSee('fixed inset-0 bg-neutral-900/40 backdrop-blur-xs z-40 sm:hidden', false);
        $response->assertSee('fixed left-3 right-3 top-[4.25rem] sm:absolute', false);
    }
}

