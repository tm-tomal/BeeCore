<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">ISP onboarding</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Register a new ISP with company, owner, contact, and business profile, then provision its initial plan and admin account in one step.</p>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            {{ session('message') }}
        </div>
    @endif

    @if($onboardedTenant)
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            <span><span class="font-semibold">{{ $onboardedTenant->name }}</span> was onboarded on the trial plan.</span>
            <a href="{{ route('tenant-details', $onboardedTenant) }}" class="ml-auto shrink-0 font-medium text-success-700 underline transition hover:text-success-800 dark:text-success-400 dark:hover:text-success-300">View tenant →</a>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
        <form wire:submit="register" class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company information</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">ISP name</label>
                        <input id="onb-name" wire:model.live="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('name')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-slug" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Slug</label>
                        <input id="onb-slug" wire:model="slug" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('slug')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-legal" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Legal / registered name</label>
                        <input id="onb-legal" wire:model="companyLegalName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('companyLegalName')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Business type</label>
                        <input id="onb-type" wire:model="businessType" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="e.g. Sole proprietorship">
                        @error('businessType')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Owner information</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-owner-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Owner name</label>
                        <input id="onb-owner-name" wire:model="ownerName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('ownerName')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-owner-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Owner email</label>
                        <input id="onb-owner-email" wire:model="ownerEmail" type="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('ownerEmail')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-owner-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Owner phone</label>
                        <input id="onb-owner-phone" wire:model="ownerPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('ownerPhone')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Contact information</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-contact-phone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Contact phone</label>
                        <input id="onb-contact-phone" wire:model="contactPhone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('contactPhone')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-contact-address" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Contact address</label>
                        <input id="onb-contact-address" wire:model="contactAddress" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('contactAddress')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Locale &amp; billing configuration</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-currency" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Default currency</label>
                        <input id="onb-currency" wire:model="currency" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('currency')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-timezone" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Timezone</label>
                        <input id="onb-timezone" wire:model="timezone" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('timezone')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Domain / subdomain setup</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-subdomain" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Subdomain</label>
                        <input id="onb-subdomain" wire:model="subdomain" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="tenant-slug">
                        @error('subdomain')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-domain" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Custom domain (optional)</label>
                        <input id="onb-domain" wire:model="customDomain" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="isp.example.com">
                        @error('customDomain')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Initial package setup</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-plan" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">SaaS plan</label>
                        <select id="onb-plan" wire:model="planId" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="">Select a plan</option>
                            @foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->trial_days }}d trial)</option>@endforeach
                        </select>
                        @error('planId')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-cycle" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Billing cycle</label>
                        <select id="onb-cycle" wire:model="billingCycle" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
                <h2 class="text-theme-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Initial admin account</h2>
                <div class="mt-4 grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="onb-admin-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Admin name</label>
                        <input id="onb-admin-name" wire:model="adminName" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('adminName')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-admin-email" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Admin email</label>
                        <input id="onb-admin-email" wire:model="adminEmail" type="email" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('adminEmail')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="onb-admin-password" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Admin password</label>
                        <input id="onb-admin-password" wire:model="adminPassword" type="password" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('adminPassword')<p class="mt-1 block text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">Complete onboarding</button>
        </form>

        <aside class="h-fit space-y-3 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <h2 class="text-theme-sm font-semibold text-gray-800 dark:text-white/90">Onboarding checklist</h2>
            <ul class="space-y-2 text-theme-sm">
                @foreach($this->checklist as $step => $done)
                    <li class="flex items-center gap-2 {{ $done ? 'font-medium text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                        <span aria-hidden="true">{{ $done ? '✓' : '○' }}</span><span>{{ $step }}</span>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</div>
