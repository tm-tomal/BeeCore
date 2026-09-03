<?php

namespace App\Livewire;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class QueueJobs extends Component
{
    public string $tab = 'pending';

    private const CATEGORY_KEYWORDS = [
        'Activation' => ['activat'],
        'Suspension' => ['suspen'],
        'SMS' => ['sms'],
        'Email' => ['email', 'mail'],
        'Payment' => ['payment', 'billing', 'invoice'],
        'Notification' => ['notif'],
        'Report' => ['report'],
    ];

    public function retryJob(string $uuid): void
    {
        $this->assertSuperAdmin();
        Artisan::call('queue:retry', ['id' => [$uuid]]);
        AuditLog::record('queue.job_retried', null, ['uuid' => $uuid]);
        session()->flash('message', 'Job queued for retry.');
    }

    public function retryAll(): void
    {
        $this->assertSuperAdmin();
        Artisan::call('queue:retry', ['id' => ['all']]);
        AuditLog::record('queue.all_jobs_retried');
        session()->flash('message', 'All failed jobs queued for retry.');
    }

    public function deleteFailedJob(string $uuid): void
    {
        $this->assertSuperAdmin();
        Artisan::call('queue:forget', ['id' => $uuid]);
        AuditLog::record('queue.failed_job_deleted', null, ['uuid' => $uuid]);
        session()->flash('message', 'Failed job deleted.');
    }

    public function clearAllFailed(): void
    {
        $this->assertSuperAdmin();
        Artisan::call('queue:flush');
        AuditLog::record('queue.all_failed_jobs_cleared');
        session()->flash('message', 'All failed jobs cleared.');
    }

    public function cancelJob(int $id): void
    {
        $this->assertSuperAdmin();
        DB::table('jobs')->where('id', $id)->delete();
        AuditLog::record('queue.job_cancelled', null, ['job_id' => $id]);
        session()->flash('message', 'Pending job cancelled.');
    }

    private function categorize(string $jobClass): string
    {
        $lower = strtolower($jobClass);

        foreach (self::CATEGORY_KEYWORDS as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    return $category;
                }
            }
        }

        return 'Other';
    }

    private function jobClassFromPayload(string $payload): string
    {
        $decoded = json_decode($payload, true);

        return $decoded['displayName'] ?? $decoded['data']['commandName'] ?? 'Unknown job';
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $pending = DB::table('jobs')->orderBy('created_at')->get()->map(function ($job) {
            $class = $this->jobClassFromPayload($job->payload);

            return (object) [
                'id' => $job->id,
                'queue' => $job->queue,
                'job_class' => $class,
                'category' => $this->categorize($class),
                'attempts' => $job->attempts,
                'created_at' => \Carbon\Carbon::createFromTimestamp($job->created_at),
                'is_reserved' => !is_null($job->reserved_at),
            ];
        });

        $failed = DB::table('failed_jobs')->orderByDesc('failed_at')->get()->map(function ($job) {
            $class = $this->jobClassFromPayload($job->payload);

            return (object) [
                'uuid' => $job->uuid,
                'queue' => $job->queue,
                'job_class' => $class,
                'category' => $this->categorize($class),
                'exception' => $job->exception,
                'failed_at' => $job->failed_at,
            ];
        });

        $categoryCounts = $pending->concat($failed)->groupBy('category')->map->count();

        return view('livewire.queue-jobs', [
            'pending' => $pending,
            'failed' => $failed,
            'runningCount' => $pending->where('is_reserved', true)->count(),
            'categoryCounts' => $categoryCounts,
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
