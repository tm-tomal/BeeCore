<?php

namespace Tests\Feature;

use App\Livewire\Announcements;
use App\Livewire\Dashboard;
use App\Models\Announcement;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AnnouncementsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Announcement ISP', 'slug' => 'announcement-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_publish_a_global_announcement_immediately(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(Announcements::class)
            ->call('create')
            ->set('title', 'Scheduled maintenance tonight')
            ->set('body', 'The platform will be briefly unavailable at midnight.')
            ->set('type', 'maintenance')
            ->call('publishNow')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', ['title' => 'Scheduled maintenance tonight', 'status' => 'published']);
        $announcement = Announcement::where('title', 'Scheduled maintenance tonight')->firstOrFail();
        $this->assertNotNull($announcement->published_at);
    }

    public function test_super_admin_can_schedule_an_announcement_for_the_future(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(Announcements::class)
            ->call('create')
            ->set('title', 'New billing feature')
            ->set('body', 'Prorated upgrades are now available.')
            ->set('type', 'feature')
            ->set('publishAt', now()->addDay()->format('Y-m-d\TH:i'))
            ->call('schedule')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('announcements', ['title' => 'New billing feature', 'status' => 'scheduled']);
    }

    public function test_published_global_announcement_appears_on_the_dashboard(): void
    {
        $admin = User::factory()->create();
        Announcement::create([
            'title' => 'Platform notice', 'body' => 'Everything is running smoothly.',
            'type' => 'system', 'status' => 'published', 'dashboard_channel' => true,
            'published_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(Dashboard::class)
            ->assertSee('Platform notice');
    }

    public function test_tenant_specific_announcement_does_not_appear_for_other_tenants(): void
    {
        $tenantA = $this->tenant();
        $tenantB = $this->tenant();
        Announcement::create([
            'title' => 'Tenant A only notice', 'body' => 'Only for tenant A.',
            'type' => 'general', 'status' => 'published', 'dashboard_channel' => true,
            'tenant_id' => $tenantA->id, 'published_at' => now(),
        ]);

        $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => \App\Models\User::ROLE_TENANT_ADMIN]);

        Livewire::actingAs($userB)->test(Dashboard::class)
            ->assertDontSee('Tenant A only notice');
    }
}
