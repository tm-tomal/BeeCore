<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Platform analytics</h1>
            <p class="mt-2 text-sm text-slate-500">Deeper platform-wide metrics with point-in-time snapshot history.</p>
        </div>
        <button wire:click="recordSnapshotNow" class="bc-primary">Record snapshot now</button>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Total tenants</p><p class="mt-2 text-xl font-black text-white">{{ number_format($totalTenants) }}</p><p class="mt-1 text-xs text-slate-500">{{ $activeTenants }} active · {{ $trialTenants }} trial · {{ $suspendedTenants }} suspended</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Total customers</p><p class="mt-2 text-xl font-black text-white">{{ number_format($totalCustomers) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Total resellers</p><p class="mt-2 text-xl font-black text-white">{{ number_format($totalResellers) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Total revenue</p><p class="mt-2 text-xl font-black text-emerald-300">৳{{ number_format($totalRevenue, 2) }}</p></div>
    </section>

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">MRR / ARR</p><p class="mt-2 text-xl font-black text-white">৳{{ number_format($mrr, 2) }}</p><p class="mt-1 text-xs text-teal-300">৳{{ number_format($arr, 2) }} ARR</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">ARPU</p><p class="mt-2 text-xl font-black text-white">৳{{ number_format($arpu, 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Churn rate (month)</p><p class="mt-2 text-xl font-black text-rose-300">{{ $churnRate }}%</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Add-on growth (6mo)</p><p class="mt-2 text-xl font-black text-white">{{ $addonGrowth->sum() }}</p></div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2">
        <div class="bc-panel p-5" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">Add-on assignment growth</h2>
            <ul class="space-y-1 text-sm text-slate-300">@forelse($addonGrowth as $month => $count)<li class="flex justify-between"><span>{{ $month }}</span><span class="font-bold text-white">{{ $count }}</span></li>@empty<li class="text-slate-600">No add-ons assigned in this window.</li>@endforelse</ul>
        </div>

        <div class="bc-panel p-5" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">Analytics history</h2>
            <div class="bc-table-wrap">
                <table class="bc-table text-xs">
                    <thead><tr><th>Recorded</th><th>Tenants</th><th>MRR</th><th>ARPU</th><th>Churn</th></tr></thead>
                    <tbody>
                        @forelse($history as $snapshot)
                            <tr>
                                <td>{{ $snapshot->recorded_at->format('d M Y, H:i') }}</td>
                                <td>{{ $snapshot->active_tenants }}/{{ $snapshot->total_tenants }}</td>
                                <td>৳{{ number_format($snapshot->mrr, 2) }}</td>
                                <td>৳{{ number_format($snapshot->arpu, 2) }}</td>
                                <td>{{ $snapshot->churn_rate }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-slate-600">No snapshots recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
