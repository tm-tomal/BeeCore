<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SecurityCenter extends Component
{
    use WithPagination;

    public string $tab = 'sessions';
    public string $loginStatusFilter = '';

    public string $blockIpAddress = '';
    public string $blockReason = '';

    public function forceLogoutUser(int $userId): void
    {
        $this->assertSuperAdmin();
        DB::table('sessions')->where('user_id', $userId)->delete();
        AuditLog::record('security.force_logout', User::find($userId));
        session()->flash('message', 'All sessions for this user were terminated.');
    }

    public function forceLogoutSession(string $sessionId): void
    {
        $this->assertSuperAdmin();
        DB::table('sessions')->where('id', $sessionId)->delete();
        AuditLog::record('security.session_terminated', null, ['session_id' => $sessionId]);
        session()->flash('message', 'Session terminated.');
    }

    public function blockIp(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'blockIpAddress' => ['required', 'ip'],
            'blockReason' => ['nullable', 'string', 'max:255'],
        ]);

        $blocked = BlockedIp::firstOrCreate(['ip_address' => $data['blockIpAddress']], [
            'reason' => $data['blockReason'] ?: null,
            'blocked_by' => auth()->id(),
            'blocked_at' => now(),
        ]);

        AuditLog::record('security.ip_blocked', $blocked, ['ip' => $blocked->ip_address]);
        $this->reset(['blockIpAddress', 'blockReason']);
        session()->flash('message', 'IP address blocked.');
    }

    public function unblockIp(int $id): void
    {
        $this->assertSuperAdmin();
        $blocked = BlockedIp::findOrFail($id);
        $blocked->delete();
        AuditLog::record('security.ip_unblocked', null, ['ip' => $blocked->ip_address]);
        session()->flash('message', 'IP address unblocked.');
    }

    public function updatedLoginStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $sessionRows = DB::table('sessions')
            ->orderByDesc('last_activity')
            ->limit(50)
            ->get();

        $userIds = $sessionRows->pluck('user_id')->filter()->unique();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $since24h = now()->subDay();
        $suspiciousIps = LoginAttempt::query()
            ->where('successful', false)
            ->where('created_at', '>=', $since24h)
            ->selectRaw('ip_address, count(*) as total')
            ->groupBy('ip_address')
            ->having('total', '>=', 5)
            ->pluck('total', 'ip_address');

        return view('livewire.security-center', [
            'sessionRows' => $sessionRows,
            'sessionUsers' => $users,
            'loginAttempts' => LoginAttempt::query()
                ->when($this->loginStatusFilter === 'failed', fn ($q) => $q->where('successful', false))
                ->when($this->loginStatusFilter === 'success', fn ($q) => $q->where('successful', true))
                ->latest('id')->paginate(15),
            'suspiciousIps' => $suspiciousIps,
            'blockedIps' => BlockedIp::query()->with('blockedBy')->latest('blocked_at')->get(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
