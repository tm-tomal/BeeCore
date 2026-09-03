<?php

namespace Tests\Feature;

use App\Livewire\SystemHealth;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class SystemHealthConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_healthy_database_and_cache_status(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SystemHealth::class)
            ->assertSee('Database')
            ->assertSee('Ok');
    }

    public function test_queue_status_reflects_failed_jobs(): void
    {
        $admin = User::factory()->create();

        DB::table('failed_jobs')->insert([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'Test failure',
            'failed_at' => now(),
        ]);

        Livewire::actingAs($admin)->test(SystemHealth::class)
            ->assertSee('Degraded')
            ->assertSee('1 failed job');
    }

    public function test_running_the_heartbeat_updates_scheduler_status(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(SystemHealth::class)
            ->call('runHeartbeatNow')
            ->assertHasNoErrors();

        $this->assertNotNull(SystemSetting::get('scheduler_last_ran_at'));
    }
}
