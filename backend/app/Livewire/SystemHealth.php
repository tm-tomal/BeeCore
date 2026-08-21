<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Services\SystemHealthChecker;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class SystemHealth extends Component
{
    public function runHeartbeatNow(): void
    {
        $this->assertSuperAdmin();
        Artisan::call('system:heartbeat');
        AuditLog::record('system.heartbeat_recorded');
        session()->flash('message', 'Scheduler heartbeat recorded.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $checker = app(SystemHealthChecker::class);
        $results = $checker->check();

        return view('livewire.system-health', [
            'results' => $results,
            'alerts' => $checker->alerts($results),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
