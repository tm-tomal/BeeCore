<?php

namespace Tests\Feature;

use App\Livewire\QueueJobs;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class QueueJobsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function insertPendingJob(string $displayName = 'App\\Jobs\\SendSmsJob'): int
    {
        return DB::table('jobs')->insertGetId([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => $displayName]),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ]);
    }

    private function insertFailedJob(string $displayName = 'App\\Jobs\\SendEmailJob'): string
    {
        $uuid = (string) Str::uuid();
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => $displayName]),
            'exception' => 'Something went wrong',
            'failed_at' => now(),
        ]);

        return $uuid;
    }

    public function test_pending_and_failed_jobs_are_listed_with_categories(): void
    {
        $admin = User::factory()->create();
        $this->insertPendingJob('App\\Jobs\\SendSmsJob');
        $this->insertFailedJob('App\\Jobs\\SendEmailJob');

        Livewire::actingAs($admin)->test(QueueJobs::class)
            ->assertSee('SendSmsJob')
            ->assertSee('SMS')
            ->set('tab', 'failed')
            ->assertSee('SendEmailJob')
            ->assertSee('Email');
    }

    public function test_super_admin_can_cancel_a_pending_job(): void
    {
        $admin = User::factory()->create();
        $jobId = $this->insertPendingJob();

        Livewire::actingAs($admin)->test(QueueJobs::class)
            ->call('cancelJob', $jobId)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('jobs', ['id' => $jobId]);
    }

    public function test_super_admin_can_delete_a_failed_job(): void
    {
        $admin = User::factory()->create();
        $uuid = $this->insertFailedJob();

        Livewire::actingAs($admin)->test(QueueJobs::class)
            ->call('deleteFailedJob', $uuid)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $uuid]);
    }

    public function test_super_admin_can_clear_all_failed_jobs(): void
    {
        $admin = User::factory()->create();
        $this->insertFailedJob();
        $this->insertFailedJob('App\\Jobs\\SendPaymentReceiptJob');

        Livewire::actingAs($admin)->test(QueueJobs::class)
            ->call('clearAllFailed')
            ->assertHasNoErrors();

        $this->assertSame(0, DB::table('failed_jobs')->count());
    }
}
