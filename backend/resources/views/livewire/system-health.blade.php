<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">System health</h1>
            <p class="mt-2 text-sm text-slate-500">Live status of the database, cache, queue, scheduler, and storage.</p>
        </div>
        <button wire:click="runHeartbeatNow" class="bc-secondary">Refresh scheduler heartbeat</button>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    @if(count($alerts))
        <div class="mb-5 border border-rose-400/25 bg-rose-400/10 p-4" style="border-radius:6px">
            <p class="text-xs font-bold uppercase text-rose-300">System alerts</p>
            <ul class="mt-2 space-y-1 text-sm text-rose-200">@foreach($alerts as $alert)<li>{{ $alert }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($results as $service => $result)
            <div class="bc-panel p-5" style="border-radius:8px">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase text-slate-500">{{ ucfirst(str_replace('_', ' ', $service)) }}</p>
                    <span class="h-2.5 w-2.5 rounded-full {{ match($result['status']) { 'ok' => 'bg-emerald-400', 'degraded' => 'bg-amber-400', default => 'bg-rose-500' } }}"></span>
                </div>
                <p class="mt-3 font-bold {{ match($result['status']) { 'ok' => 'text-emerald-300', 'degraded' => 'text-amber-300', default => 'text-rose-300' } }}">{{ ucfirst($result['status']) }}</p>
                <p class="mt-2 text-sm text-slate-400">{{ $result['detail'] }}</p>
            </div>
        @endforeach
    </section>
</div>
