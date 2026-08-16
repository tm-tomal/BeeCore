<aside class="fixed inset-y-0 left-0 w-72 border-r border-slate-800 bg-slate-900/95 p-6 flex flex-col">
    <div class="mb-10">
        <div class="text-2xl font-black tracking-tight text-cyan-400">BeeCore</div>
        <div class="mt-1 text-xs uppercase tracking-[0.22em] text-slate-500">SaaS Control</div>
    </div>

    <nav class="space-y-2 text-sm text-slate-300 flex-1">
        <a href="{{ route('dashboard') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('dashboard') ? 'bg-cyan-500/15 text-cyan-300' : 'hover:bg-slate-800' }}"><span>Overview</span><span>●</span></a>
        <a href="{{ route('tenants') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('tenants') ? 'bg-emerald-500/15 text-emerald-300' : 'hover:bg-slate-800' }}"><span>Tenants (ISPs)</span><span>🏢</span></a>
        <a href="{{ route('packages') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('packages') ? 'bg-violet-500/15 text-violet-300' : 'hover:bg-slate-800' }}"><span>Packages & IPs</span><span>📦</span></a>
        <a href="{{ route('customers') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('customers') ? 'bg-cyan-500/15 text-cyan-300' : 'hover:bg-slate-800' }}"><span>Customers</span><span></span></a>
        <a href="{{ route('billing') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('billing') ? 'bg-cyan-500/15 text-cyan-300' : 'hover:bg-slate-800' }}"><span>Billing</span><span>৳</span></a>
        <a href="{{ route('payments') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('payments') ? 'bg-emerald-500/15 text-emerald-400' : 'hover:bg-slate-800' }}"><span>Payments</span><span>↗</span></a>
        <a href="{{ route('network') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('network') ? 'bg-cyan-500/15 text-cyan-300' : 'hover:bg-slate-800' }}"><span>Network</span><span>◉</span></a>
        <a href="{{ route('resellers') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('resellers') ? 'bg-amber-500/15 text-amber-300' : 'hover:bg-slate-800' }}"><span>Resellers</span><span>◎</span></a>
        <a href="{{ route('reports') }}" class="flex items-center justify-between rounded-xl px-3 py-2 {{ request()->routeIs('reports') ? 'bg-rose-500/15 text-rose-300' : 'hover:bg-slate-800' }}"><span>Reports</span><span>▣</span></a>
    </nav>

    <div class="mt-auto border-t border-slate-800 pt-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-sm text-rose-400 hover:bg-slate-800">
                <span>Logout</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
            </button>
        </form>
    </div>
</aside>
