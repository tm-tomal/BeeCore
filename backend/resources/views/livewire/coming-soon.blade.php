<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">{{ $label }}</h1>
        @if($description)
            <p class="mt-2 text-sm text-slate-500">{{ $description }}</p>
        @endif
    </header>

    <div class="max-w-2xl border border-white/10 bg-white/[0.03] p-6" style="border-radius: 8px">
        <div class="mb-4 inline-flex items-center gap-2 border border-amber-400/25 bg-amber-400/10 px-3 py-1 text-xs font-bold uppercase tracking-wide text-amber-300" style="border-radius: 999px">
            <span aria-hidden="true">🚧</span> Coming soon
        </div>
        <p class="mb-5 text-sm text-slate-400">This module is on the roadmap and is not implemented yet. Planned capabilities:</p>
        <ul class="space-y-2 text-sm text-slate-300">
            @foreach($features as $feature)
                <li class="flex items-start gap-2"><span class="mt-1 text-teal-300" aria-hidden="true">•</span><span>{{ $feature }}</span></li>
            @endforeach
        </ul>
    </div>
</div>
