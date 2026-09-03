<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealthChecker
{
    /**
     * @return array<string, array{status: string, detail: string}>
     */
    public function check(): array
    {
        $results = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue' => $this->checkQueue(),
            'scheduler' => $this->checkScheduler(),
            'storage' => $this->checkStorage(),
        ];

        return $results;
    }

    /**
     * @return array<int, string>
     */
    public function alerts(array $results): array
    {
        return collect($results)
            ->filter(fn ($result) => $result['status'] !== 'ok')
            ->map(fn ($result, $key) => ucfirst($key).': '.$result['detail'])
            ->values()
            ->all();
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok', 'detail' => 'Connected ('.DB::connection()->getDriverName().').'];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'detail' => 'Connection failed: '.$e->getMessage()];
        }
    }

    private function checkCache(): array
    {
        try {
            $key = 'system_health.probe';
            Cache::put($key, true, 5);
            $ok = Cache::get($key) === true;
            Cache::forget($key);

            return $ok
                ? ['status' => 'ok', 'detail' => 'Cache read/write succeeded.']
                : ['status' => 'degraded', 'detail' => 'Cache write did not round-trip.'];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'detail' => 'Cache error: '.$e->getMessage()];
        }
    }

    private function checkQueue(): array
    {
        try {
            $pending = DB::table('jobs')->count();
            $failed = DB::table('failed_jobs')->count();

            $status = $failed > 0 ? 'degraded' : 'ok';

            return ['status' => $status, 'detail' => "{$pending} pending job(s), {$failed} failed job(s)."];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'detail' => 'Queue tables unreachable: '.$e->getMessage()];
        }
    }

    private function checkScheduler(): array
    {
        $lastRan = SystemSetting::get('scheduler_last_ran_at');

        if (!$lastRan) {
            return ['status' => 'degraded', 'detail' => 'No scheduler heartbeat recorded yet.'];
        }

        $minutesAgo = now()->diffInMinutes(\Carbon\Carbon::parse($lastRan));

        return $minutesAgo <= 5
            ? ['status' => 'ok', 'detail' => "Last heartbeat {$minutesAgo} minute(s) ago."]
            : ['status' => 'degraded', 'detail' => "Last heartbeat {$minutesAgo} minute(s) ago (expected every minute)."];
    }

    private function checkStorage(): array
    {
        $path = storage_path('app');

        if (!is_dir($path) || !is_writable($path)) {
            return ['status' => 'down', 'detail' => 'Storage path is not writable.'];
        }

        $free = @disk_free_space($path);
        $total = @disk_total_space($path);

        if ($free === false || $total === false || $total === 0) {
            return ['status' => 'degraded', 'detail' => 'Unable to determine disk usage.'];
        }

        $usedPercent = round((1 - $free / $total) * 100);

        return [
            'status' => $usedPercent >= 90 ? 'degraded' : 'ok',
            'detail' => $usedPercent.'% disk used, '.round($free / 1024 / 1024 / 1024, 1).' GB free.',
        ];
    }
}
