@php
    $user = auth()->user();
    $isSaasAdmin = $user?->isSuperAdmin() && !session('impersonated_tenant_id');
    $hasTenantContext = (bool) ($user?->tenant_id || session('impersonated_tenant_id'));

    // Feather-style stroke icon set (24x24)
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9" rx="1.2"/><rect x="14" y="3" width="7" height="5" rx="1.2"/><rect x="14" y="12" width="7" height="9" rx="1.2"/><rect x="3" y="16" width="7" height="5" rx="1.2"/>',
        'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'onboarding' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 8v8M8 12h8"/>',
        'layers' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        'repeat' => '<polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>',
        'card' => '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
        'dollar' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        'box' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="3" width="7" height="7" rx="1.2"/><rect x="14" y="14" width="7" height="7" rx="1.2"/><rect x="3" y="14" width="7" height="7" rx="1.2"/>',
        'globe' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'banknote' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
        'pen' => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/>',
        'phone' => '<rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
        'monitor' => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/><polygon points="10 8 16 11 10 14"/>',
        'chat' => '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>',
        'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'bell' => '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>',
        'megaphone' => '<path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'zap' => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        'code' => '<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>',
        'sliders' => '<line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/>',
        'activity' => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
        'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'help' => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'chart' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'server' => '<rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><line x1="6" y1="6" x2="6.01" y2="6"/><line x1="6" y1="18" x2="6.01" y2="18"/>',
        'share' => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
        'receipt' => '<path d="M6 2h12a2 2 0 0 1 2 2v16l-4-2-4 2-4-2-4 2V4a2 2 0 0 1 2-2z"/><line x1="9" y1="9" x2="15" y2="9"/><line x1="9" y1="13" x2="15" y2="13"/>',
        'wifi' => '<path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/>',
        'map' => '<polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/>',
        'flag' => '<path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>',
        'user-plus' => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>',
        'shopping-bag' => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
    ];
    $iconFor = fn (string $key): string => $icons[$key] ?? '<circle cx="12" cy="12" r="3"/>';

    $saasIcons = [
        'tenants' => 'briefcase', 'isp-onboarding' => 'onboarding', 'saas-plans' => 'layers',
        'subscriptions' => 'repeat', 'saas-billing' => 'card',
        'payment-gateways' => 'card', 'add-ons' => 'box', 'feature-modules' => 'grid',
        'multi-language' => 'globe', 'multi-currency' => 'banknote', 'white-label' => 'pen',
        'customer-app' => 'phone', 'media-server' => 'monitor', 'sms-management' => 'chat',
        'email-management' => 'mail', 'notifications' => 'bell', 'announcements' => 'megaphone',
        'network-integrations' => 'zap', 'api-management' => 'code', 'system-settings' => 'sliders',
        'system-health' => 'activity', 'queue-jobs' => 'clock', 'data-management' => 'database',
        'support-tickets' => 'help', 'reports-analytics' => 'chart', 'platform-analytics' => 'chart',
        'platform-users' => 'users', 'roles-permissions' => 'shield', 'security-center' => 'lock',
        'audit-activity' => 'file', 'my-profile' => 'user',
    ];

    $navIcon = fn (string $key): string => '<svg class="shrink-0 stroke-current" style="width:20px;height:20px" viewBox="0 0 24 24" fill="none" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'.$iconFor($key).'</svg>';

    $isRouteActive = function (string $routeName) use ($user): bool {
        $current = request()->route()?->getName();
        if ($current === $routeName) {
            return true;
        }
        if ($routeName === 'tenants' && in_array($current, ['tenant-details', 'isp-onboarding'], true)) {
            return true;
        }
        return false;
    };
@endphp

<aside
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'sidebar-collapsed': sidebarCollapsed,
    }"
    class="sidebar app-sidebar fixed left-0 top-0 z-[60] flex h-screen w-[280px] flex-col overflow-y-hidden border-r border-gray-200 bg-white px-5 dark:border-gray-800 dark:bg-gray-900 lg:static lg:z-auto lg:translate-x-0"
    aria-label="Primary navigation"
>
    <!-- Sidebar header -->
    <div class="flex items-center justify-between pb-6 pt-7">
        <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5">
            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-500 text-base font-bold text-white">B</span>
            <span class="sidebar-brand-text text-lg font-bold text-gray-800 dark:text-white">BeeCore</span>
        </a>
        <button type="button" @click="sidebarOpen = false" aria-label="Close navigation" class="grid h-9 w-9 place-items-center rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden dark:text-gray-400 dark:hover:bg-gray-800">
            <svg class="stroke-current" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </div>

    <div class="no-scrollbar flex flex-col overflow-y-auto pb-6 duration-300 ease-linear">
        <nav class="space-y-6">
            @if(! $hasTenantContext)
                <!-- Main group (platform-only) -->
                <div>
                    <h3 class="sidebar-heading mb-3 text-xs font-semibold uppercase leading-5 tracking-wide text-gray-400 dark:text-gray-500">{{ __('Menu') }}</h3>
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('dashboard') }}" @click="sidebarOpen = false" class="menu-item {{ $isRouteActive('dashboard') ? 'menu-item-active' : 'menu-item-inactive' }}">
                                {!! $navIcon('dashboard') !!}
                                <span class="sidebar-label">{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            @if($isSaasAdmin)
                @foreach(config('super_admin_menu', []) as $menuGroup)
                    <div>
                        <h3 class="sidebar-heading mb-3 text-xs font-semibold uppercase leading-5 tracking-wide text-gray-400 dark:text-gray-500">{{ __($menuGroup['group']) }}</h3>
                        <ul class="space-y-1">
                            @foreach($menuGroup['items'] as $menuItem)
                                @php
                                    $active = isset($menuItem['route']) && $isRouteActive($menuItem['route']);
                                @endphp
                                <li>
                                    @if(isset($menuItem['route']))
                                        <a href="{{ route($menuItem['route']) }}" @click="sidebarOpen = false" class="menu-item {{ $active ? 'menu-item-active' : 'menu-item-inactive' }}">
                                            {!! $navIcon($saasIcons[$menuItem['route']] ?? 'dashboard') !!}
                                            <span class="sidebar-label">{{ __($menuItem['label']) }}</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif

            @if($hasTenantContext)
                @php
                    $workspaceTenant = session('impersonated_tenant_id')
                        ? \App\Models\Tenant::query()->whereKey(session('impersonated_tenant_id'))->first()
                        : $user?->tenant()->where('status', 'active')->first();
                    $workspaceAutomatic = $workspaceTenant?->isAutomatic() ?? true;

                    // Tenant workspace menu grouped by job focus: the day-to-day
                    // operations come first, then money, growth and workspace.
                    $allStaffRoles = [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE, \App\Models\User::ROLE_SUPPORT, \App\Models\User::ROLE_NETWORK_ENGINEER];
                    $ownerRoles = [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN];

                    $tenantGroups = [
                        [
                            'heading' => 'Operations',
                            'items' => [
                                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'roles' => $allStaffRoles],
                                ['route' => 'customers', 'label' => 'Subscribers', 'icon' => 'users', 'roles' => $allStaffRoles],
                                ['route' => 'packages', 'label' => 'Service plans', 'icon' => 'wifi', 'roles' => $ownerRoles],
                                ['route' => 'network', 'label' => 'Network', 'icon' => 'server', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_NETWORK_ENGINEER], 'automaticOnly' => true],
                                ['route' => 'cable-map', 'label' => 'Cable & fiber map', 'icon' => 'map', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_NETWORK_ENGINEER]],
                                ['route' => 'issues', 'label' => 'Issues', 'icon' => 'flag', 'roles' => $allStaffRoles],
                            ],
                        ],
                        [
                            'heading' => 'Money',
                            'items' => [
                                ['route' => 'billing', 'label' => 'Bills & invoices', 'icon' => 'receipt', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE]],
                                ['route' => 'payments', 'label' => 'Collections', 'icon' => 'dollar', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_FINANCE]],
                                ['route' => 'isp-gateway', 'label' => 'Payment methods', 'icon' => 'card', 'roles' => $ownerRoles],
                            ],
                        ],
                        [
                            'heading' => 'Growth',
                            'items' => [
                                ['route' => 'resellers', 'label' => 'Reseller partners', 'icon' => 'briefcase', 'roles' => $ownerRoles],
                                ['route' => 'reports', 'label' => 'Reports & insights', 'icon' => 'chart', 'roles' => $allStaffRoles],
                            ],
                        ],
                        [
                            'heading' => 'Workspace',
                            'items' => [
                                ['route' => 'isp-team', 'label' => 'Team & staff', 'icon' => 'user-plus', 'roles' => $ownerRoles],
                                ['route' => 'isp-addons', 'label' => 'Add-on store', 'icon' => 'shopping-bag', 'roles' => $ownerRoles],
                                ['route' => 'isp-subscription', 'label' => 'My BeeCore plan', 'icon' => 'repeat', 'roles' => $ownerRoles],
                                ['route' => 'support', 'label' => 'Support', 'icon' => 'help', 'roles' => [\App\Models\User::ROLE_SUPER_ADMIN, \App\Models\User::ROLE_TENANT_ADMIN, \App\Models\User::ROLE_SUPPORT]],
                                ['route' => 'isp-settings', 'label' => 'Workspace settings', 'icon' => 'settings', 'roles' => $ownerRoles],
                            ],
                        ],
                    ];
                @endphp
                @foreach($tenantGroups as $tenantGroup)
                     @php
                         $visibleLinks = array_values(array_filter(
                             $tenantGroup['items'],
                             fn ($link) => in_array($user->role, $link['roles'], true)
                                 && ! (($link['automaticOnly'] ?? false) && ! $workspaceAutomatic)
                         ));
                     @endphp
                     @if(count($visibleLinks) > 0)
                         <div>
                             <h3 class="sidebar-heading mb-3 text-xs font-semibold uppercase leading-5 tracking-wide text-gray-400 dark:text-gray-500">{{ __($tenantGroup['heading']) }}</h3>
                             <ul class="space-y-1">
                                 @foreach($visibleLinks as $link)
                                     <li>
                                         <a href="{{ route($link['route']) }}" @click="sidebarOpen = false" class="menu-item {{ $isRouteActive($link['route']) ? 'menu-item-active' : 'menu-item-inactive' }}">
                                             {!! $navIcon($link['icon']) !!}
                                             <span class="sidebar-label">{{ __($link['label']) }}</span>
                                         </a>
                                     </li>
                                 @endforeach
                             </ul>
                         </div>
                     @endif
                 @endforeach
            @endif
        </nav>
    </div>
</aside>
