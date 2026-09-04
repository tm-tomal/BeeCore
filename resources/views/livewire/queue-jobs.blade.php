<div class="space-y-6">
    @php
        $jobIcon = fn (string $category): string => match (strtolower($category)) {
            'sms', 'email', 'notification' => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
            'billing', 'invoice' => '<rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>',
            default => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        };
    @endphp

    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Queue &amp; jobs</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Pending, running, and failed background jobs with retry/cancel controls.</p>
        </div>
        @if($failed->isNotEmpty())
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <button type="button" wire:click="retryAll" wire:confirm="Retry all failed jobs?" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-brand-200 bg-brand-50 px-4 py-2.5 text-theme-sm font-medium text-brand-600 shadow-theme-xs transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    Retry all
                </button>
                <button type="button" wire:click="clearAllFailed" wire:confirm="Delete all failed jobs?" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-error-200 bg-error-50 px-4 py-2.5 text-theme-sm font-medium text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                    <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    Clear all failed
                </button>
            </div>
        @endif
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <!-- Queue summary -->
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-warning-500/10 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-gray-800 dark:text-white/90">{{ $pending->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Pending jobs</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-success-500/10 text-success-600 dark:bg-success-500/15 dark:text-success-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $runningCount }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Running</p>
            </div>
        </div>
        <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                <svg class="size-5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </span>
            <div>
                <p class="text-2xl font-bold {{ $failed->count() > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-800 dark:text-white/90' }}">{{ $failed->count() }}</p>
                <p class="text-theme-xs font-medium text-gray-500 dark:text-gray-400">Failed jobs</p>
            </div>
        </div>
    </section>

    @if($categoryCounts->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-theme-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">By category</p>
            @foreach($categoryCounts as $category => $count)
                <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-theme-xs font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-400">
                    <span class="size-1.5 rounded-full bg-brand-500"></span>{{ $category }} · {{ $count }}
                </span>
            @endforeach
        </div>
    @endif

    <!-- Tabs -->
    <div class="inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <button type="button" wire:click="$set('tab', 'pending')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'pending' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            Pending jobs
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'pending' ? 'bg-white/25 text-white' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400' }}">{{ $pending->count() }}</span>
        </button>
        <button type="button" wire:click="$set('tab', 'failed')" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-theme-sm font-medium transition {{ $tab === 'failed' ? 'bg-brand-500 text-white shadow-theme-xs' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200' }}">
            Failed jobs
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $tab === 'failed' ? 'bg-white/25 text-white' : ($failed->count() ? 'bg-error-50 text-error-600 dark:bg-error-500/20 dark:text-error-400' : 'bg-gray-100 text-gray-500 dark:bg-white/[0.06] dark:text-gray-400') }}">{{ $failed->count() }}</span>
        </button>
    </div>

    @if($tab === 'pending')
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Job</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Queue</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Attempts</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Queued at</th>
                            <th class="px-5 py-3.5 text-center text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($pending as $job)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-500/10 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $jobIcon($job->category) !!}</svg>
                                        </span>
                                        <code class="rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-gray-800 dark:bg-white/[0.05] dark:text-white/90">{{ $job->job_class }}</code>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $job->category }}</td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $job->queue }}</code></td>
                                <td class="px-5 py-4 text-center"><span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-theme-xs font-bold text-gray-600 dark:bg-white/[0.06] dark:text-gray-300">{{ $job->attempts }}</span></td>
                                <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $job->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-4 text-center">
                                    @if($job->is_reserved)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-theme-xs font-semibold text-success-600 dark:bg-success-500/15 dark:text-success-500"><span class="size-1.5 animate-pulse rounded-full bg-success-500"></span>Running</span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-warning-50 px-2.5 py-1 text-theme-xs font-semibold text-warning-600 dark:bg-warning-500/15 dark:text-warning-500"><span class="size-1.5 rounded-full bg-warning-500"></span>Pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end">
                                        <button type="button" wire:click="cancelJob({{ $job->id }})" wire:confirm="Cancel this job?" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-error-200 bg-error-50 px-3 py-2 text-theme-xs font-semibold text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                            Cancel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-success-50 text-success-500 dark:bg-success-500/15">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">Queue is clear</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">No pending background jobs right now.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="w-full overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Job</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Queue</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Failed at</th>
                            <th class="px-5 py-3.5 text-left text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Exception</th>
                            <th class="px-5 py-3.5 text-right text-theme-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($failed as $job)
                            <tr class="transition-colors hover:bg-gray-50/70 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-error-500/10 text-error-600 dark:bg-error-500/15 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $jobIcon($job->category) !!}</svg>
                                        </span>
                                        <code class="rounded-md bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs font-semibold text-gray-800 dark:bg-white/[0.05] dark:text-white/90">{{ $job->job_class }}</code>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-theme-sm text-gray-600 dark:text-gray-400">{{ $job->category }}</td>
                                <td class="px-5 py-4"><code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-theme-xs text-gray-500 dark:bg-white/[0.05] dark:text-gray-400">{{ $job->queue }}</code></td>
                                <td class="px-5 py-4 text-theme-xs text-gray-500 dark:text-gray-400">{{ $job->failed_at }}</td>
                                <td class="max-w-xs truncate px-5 py-4" title="{{ $job->exception }}"><code class="font-mono text-theme-xs text-error-600 dark:text-error-400">{{ Str::limit($job->exception, 80) }}</code></td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" wire:click="retryJob('{{ $job->uuid }}')" class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-brand-200 bg-brand-50 px-3 py-2 text-theme-xs font-semibold text-brand-600 transition hover:border-brand-300 hover:bg-brand-100 dark:border-brand-500/25 dark:bg-brand-500/10 dark:text-brand-400">
                                            <svg class="size-3.5 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                                            Retry
                                        </button>
                                        <button type="button" wire:click="deleteFailedJob('{{ $job->uuid }}')" wire:confirm="Delete this failed job?" class="grid h-8 w-8 place-items-center rounded-lg border border-error-200 bg-error-50 text-error-600 transition hover:border-error-300 hover:bg-error-100 dark:border-error-500/25 dark:bg-error-500/10 dark:text-error-400">
                                            <svg class="size-4 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div class="mx-auto max-w-xs">
                                        <span class="mx-auto grid size-12 place-items-center rounded-full bg-success-50 text-success-500 dark:bg-success-500/15">
                                            <svg class="size-6 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        </span>
                                        <p class="mt-4 text-theme-sm font-medium text-gray-700 dark:text-gray-300">No failed jobs</p>
                                        <p class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">Everything processed cleanly. Nice.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
