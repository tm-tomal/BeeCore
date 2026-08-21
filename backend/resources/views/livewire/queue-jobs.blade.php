<div>
    @php use Illuminate\Support\Str; @endphp
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Queue &amp; jobs</h1>
            <p class="mt-2 text-sm text-slate-500">Pending, running, and failed background jobs with retry/cancel controls.</p>
        </div>
        @if($failed->isNotEmpty())
            <div class="flex gap-3">
                <button wire:click="retryAll" wire:confirm="Retry all failed jobs?" class="bc-secondary">Retry all</button>
                <button wire:click="clearAllFailed" wire:confirm="Delete all failed jobs?" class="bc-secondary">Clear all failed</button>
            </div>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-3">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Pending</p><p class="mt-2 text-2xl font-black text-white">{{ $pending->count() }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Running</p><p class="mt-2 text-2xl font-black text-amber-300">{{ $runningCount }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Failed</p><p class="mt-2 text-2xl font-black text-rose-300">{{ $failed->count() }}</p></div>
    </section>

    @if($categoryCounts->isNotEmpty())
        <div class="mb-6 flex flex-wrap gap-2 text-xs">
            @foreach($categoryCounts as $category => $count)
                <span class="border border-white/10 px-3 py-1 text-slate-300" style="border-radius:999px">{{ $category }} · {{ $count }}</span>
            @endforeach
        </div>
    @endif

    <div class="mb-5 flex gap-2">
        <button wire:click="$set('tab', 'pending')" class="px-4 py-2 text-sm font-bold {{ $tab === 'pending' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Pending jobs</button>
        <button wire:click="$set('tab', 'failed')" class="px-4 py-2 text-sm font-bold {{ $tab === 'failed' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Failed jobs</button>
    </div>

    @if($tab === 'pending')
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Job</th><th>Category</th><th>Queue</th><th>Attempts</th><th>Queued at</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($pending as $job)
                        <tr>
                            <td><code class="text-slate-300">{{ class_basename($job->job_class) }}</code></td>
                            <td>{{ $job->category }}</td>
                            <td>{{ $job->queue }}</td>
                            <td>{{ $job->attempts }}</td>
                            <td class="text-xs text-slate-500">{{ $job->created_at->format('d M Y, H:i') }}</td>
                            <td><span class="font-semibold {{ $job->is_reserved ? 'text-amber-300' : 'text-slate-400' }}">{{ $job->is_reserved ? 'Running' : 'Pending' }}</span></td>
                            <td class="text-right"><button wire:click="cancelJob({{ $job->id }})" wire:confirm="Cancel this job?" class="font-semibold text-rose-300">Cancel</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-slate-600">No pending jobs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Job</th><th>Category</th><th>Queue</th><th>Failed at</th><th>Exception</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($failed as $job)
                        <tr>
                            <td><code class="text-slate-300">{{ class_basename($job->job_class) }}</code></td>
                            <td>{{ $job->category }}</td>
                            <td>{{ $job->queue }}</td>
                            <td class="text-xs text-slate-500">{{ $job->failed_at }}</td>
                            <td class="max-w-xs truncate text-rose-300" title="{{ $job->exception }}">{{ Str::limit($job->exception, 80) }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-3">
                                    <button wire:click="retryJob('{{ $job->uuid }}')" class="font-semibold text-teal-300">Retry</button>
                                    <button wire:click="deleteFailedJob('{{ $job->uuid }}')" wire:confirm="Delete this failed job?" class="font-semibold text-rose-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No failed jobs.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
