@php
    $user = auth()->user();
    $hasTenantContext = $user?->tenant_id || session('impersonated_tenant_id');
@endphp

<aside :class="navigationOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-white/10 bg-[#0a1413] p-4 transition-transform duration-200 lg:translate-x-0">
    <div class="mb-7 flex h-12 items-center justify-between px-2">
        <div><div class="text-xl font-black text-teal-300">BeeCore</div><div class="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-600">Operations console</div></div>
        <button type="button" @click="navigationOpen = false" aria-label="Close navigation" class="grid h-9 w-9 place-items-center text-slate-400 hover:bg-white/5 hover:text-white lg:hidden" style="border-radius: 6px"><svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
    </div>

    @if(session('impersonated_tenant_id'))
        <div class="mb-5 border border-amber-400/25 bg-amber-400/10 p-3" style="border-radius: 6px">
            <div class="mb-1 text-[10px] font-bold uppercase text-amber-300">Viewing tenant</div>
            <div class="font-bold text-slate-200">{{ session('impersonated_tenant_name') }}</div>
            <a href="{{ route('leave-impersonation') }}" class="mt-3 block w-full bg-amber-300 px-3 py-2 text-center text-xs font-bold text-[#07100f]" style="border-radius: 5px">Exit workspace</a>
        </div>
    @endif

    <nav aria-label="Primary navigation" class="flex-1 space-y-1 overflow-y-auto text-sm text-slate-400">
        <a href="{{ route('dashboard') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('dashboard') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Dashboard</a>

        @if($user?->isSuperAdmin() && !session('impersonated_tenant_id'))
            @foreach(config('super_admin_menu', []) as $menuGroup)
                <div class="px-3 pb-2 pt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-600">{{ $menuGroup['group'] }}</div>
                @foreach($menuGroup['items'] as $menuItem)
                    @php
                        $menuActive = isset($menuItem['route'])
                            ? request()->routeIs($menuItem['route']) || ($menuItem['route'] === 'tenants' && request()->routeIs('tenant-details'))
                            : request()->routeIs('super-admin.coming-soon') && request()->route('slug') === $menuItem['slug'];
                        $menuHref = isset($menuItem['route']) ? route($menuItem['route']) : route('super-admin.coming-soon', $menuItem['slug']);
                    @endphp
                    <a href="{{ $menuHref }}" @click="navigationOpen = false" class="flex items-center justify-between px-3 py-2.5 font-semibold {{ $menuActive ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">
                        <span>{{ $menuItem['label'] }}</span>
                        @if(!isset($menuItem['route']))
                            <span class="text-[9px] font-bold uppercase tracking-wide text-amber-400/80">Soon</span>
                        @endif
                    </a>
                @endforeach
            @endforeach
        @endif

        @if($hasTenantContext)
            <div class="px-3 pb-2 pt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-600">Tenant workspace</div>

            @if(in_array($user->role, [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_SUPPORT, \App\Models\User::ROLE_NETWORK_ENGINEER], true))
                <a href="{{ route('customers') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('customers') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Customers</a>
            @endif
            @if(in_array($user->role, [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN], true))
                <a href="{{ route('packages') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('packages') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Packages</a>
            @endif
            @if(in_array($user->role, [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE], true))
                <a href="{{ route('billing') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('billing') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Billing</a>
                <a href="{{ route('payments') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('payments') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Payments</a>
            @endif
            @if(in_array($user->role, [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_NETWORK_ENGINEER], true))
                <a href="{{ route('network') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('network') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Network</a>
            @endif
            @if(in_array($user->role, [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN], true))
                <a href="{{ route('resellers') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('resellers') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Resellers</a>
            @endif
            @if(in_array($user->role, [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE, \App\Models\User::ROLE_SUPPORT, \App\Models\User::ROLE_NETWORK_ENGINEER], true))
                <a href="{{ route('reports') }}" @click="navigationOpen = false" class="block px-3 py-2.5 font-semibold {{ request()->routeIs('reports') ? 'bg-teal-300/10 text-teal-300' : 'hover:bg-white/5 hover:text-white' }}" style="border-radius: 6px">Reports</a>
            @endif
        @endif
    </nav>

    <div class="mt-4 border-t border-white/10 pt-4">
        <div class="mb-3 flex items-center gap-3 px-2"><div class="grid h-9 w-9 place-items-center bg-emerald-400/15 text-sm font-bold text-emerald-300" style="border-radius: 6px">{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</div><div class="min-w-0"><div class="truncate text-sm font-bold text-white">{{ $user->name }}</div><div class="truncate text-xs capitalize text-slate-600">{{ str_replace('_', ' ', $user->role) }}</div></div></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center justify-between px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-white/5 hover:text-rose-300" style="border-radius: 6px">
                <span>Sign out</span><span aria-hidden="true">→</span>
            </button>
        </form>
    </div>
</aside>
