<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Reports &amp; analytics</h1>
            <p class="mt-2 text-sm text-slate-500">Growth, revenue, distribution, and conversion reporting across the platform.</p>
        </div>
        <button wire:click="exportCsv" class="bc-secondary">Export CSV</button>
    </header>

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Active ISPs</p><p class="mt-2 text-xl font-black text-white">{{ number_format($activeTenants) }}</p><p class="mt-1 text-xs text-rose-300">{{ $suspendedTenants }} suspended</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">MRR / ARR</p><p class="mt-2 text-xl font-black text-white">৳{{ number_format($mrr, 2) }}</p><p class="mt-1 text-xs text-teal-300">৳{{ number_format($arr, 2) }} ARR</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Collected this month</p><p class="mt-2 text-xl font-black text-emerald-300">৳{{ number_format($collectedThisMonth, 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Add-on revenue</p><p class="mt-2 text-xl font-black text-white">৳{{ number_format($addonRevenue, 2) }}</p></div>
    </section>

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">SMS revenue (30d)</p><p class="mt-2 text-xl font-black text-white">৳{{ number_format($smsRevenue, 2) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Email sent (30d)</p><p class="mt-2 text-xl font-black text-white">{{ number_format($emailSent30d) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">API requests (30d)</p><p class="mt-2 text-xl font-black text-white">{{ number_format($apiRequests30d) }}</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Storage used</p><p class="mt-2 text-xl font-black text-white">{{ number_format($storageUsedGb) }} GB</p></div>
    </section>

    <section class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Trial conversion</p><p class="mt-2 text-xl font-black text-emerald-300">{{ $trialConversionRate }}%</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Churn rate (month)</p><p class="mt-2 text-xl font-black text-rose-300">{{ $churnRate }}%</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Payment success rate</p><p class="mt-2 text-xl font-black text-emerald-300">{{ $paymentSuccessRate }}%</p></div>
        <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Payment failure rate</p><p class="mt-2 text-xl font-black text-rose-300">{{ $paymentFailureRate }}%</p></div>
    </section>

    <section class="grid gap-5 lg:grid-cols-2">
        <div class="bc-panel p-5" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">ISP growth (6 months)</h2>
            <ul class="space-y-1 text-sm text-slate-300">@forelse($ispGrowth as $month => $count)<li class="flex justify-between"><span>{{ $month }}</span><span class="font-bold text-white">{{ $count }}</span></li>@empty<li class="text-slate-600">No new ISPs in this window.</li>@endforelse</ul>
        </div>
        <div class="bc-panel p-5" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">Customer growth (6 months)</h2>
            <ul class="space-y-1 text-sm text-slate-300">@forelse($customerGrowth as $month => $count)<li class="flex justify-between"><span>{{ $month }}</span><span class="font-bold text-white">{{ $count }}</span></li>@empty<li class="text-slate-600">No new customers in this window.</li>@endforelse</ul>
        </div>
        <div class="bc-panel p-5" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">Subscription status distribution</h2>
            <ul class="space-y-1 text-sm text-slate-300">@forelse($subscriptionsByStatus as $status => $count)<li class="flex justify-between capitalize"><span>{{ str_replace('_', ' ', $status) }}</span><span class="font-bold text-white">{{ $count }}</span></li>@empty<li class="text-slate-600">No subscriptions yet.</li>@endforelse</ul>
        </div>
        <div class="bc-panel p-5" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">Plan distribution</h2>
            <ul class="space-y-1 text-sm text-slate-300">@forelse($planDistribution as $plan => $count)<li class="flex justify-between"><span>{{ $plan }}</span><span class="font-bold text-white">{{ $count }}</span></li>@empty<li class="text-slate-600">No plans assigned yet.</li>@endforelse</ul>
        </div>
        <div class="bc-panel p-5 lg:col-span-2" style="border-radius:8px">
            <h2 class="mb-3 font-bold text-white">Customer distribution by tenant (top 10)</h2>
            <ul class="space-y-1 text-sm text-slate-300">@forelse($customerDistribution as $tenant => $count)<li class="flex justify-between"><span>{{ $tenant }}</span><span class="font-bold text-white">{{ $count }}</span></li>@empty<li class="text-slate-600">No customers yet.</li>@endforelse</ul>
        </div>
    </section>
</div>
