<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">ISP onboarding</h1>
        <p class="mt-2 text-sm text-slate-500">Register a new ISP with company, owner, contact, and business profile, then provision its initial plan and admin account in one step.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    @if($onboardedTenant)
        <div class="mb-5 border border-teal-300/25 bg-teal-300/10 p-4 text-sm text-teal-200" style="border-radius:6px">
            <span class="font-bold">{{ $onboardedTenant->name }}</span> was onboarded on the trial plan.
            <a href="{{ route('tenant-details', $onboardedTenant) }}" class="ml-2 font-semibold text-teal-300 underline">View tenant →</a>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_280px]">
        <form wire:submit="register" class="space-y-6">
            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Company information</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-name">ISP name</label><input id="onb-name" wire:model.live="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-slug">Slug</label><input id="onb-slug" wire:model="slug" class="bc-field">@error('slug')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-legal">Legal / registered name</label><input id="onb-legal" wire:model="companyLegalName" class="bc-field">@error('companyLegalName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-type">Business type</label><input id="onb-type" wire:model="businessType" class="bc-field" placeholder="e.g. Sole proprietorship">@error('businessType')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
            </fieldset>

            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Owner information</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-owner-name">Owner name</label><input id="onb-owner-name" wire:model="ownerName" class="bc-field">@error('ownerName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-owner-email">Owner email</label><input id="onb-owner-email" wire:model="ownerEmail" type="email" class="bc-field">@error('ownerEmail')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
                <div><label class="bc-label" for="onb-owner-phone">Owner phone</label><input id="onb-owner-phone" wire:model="ownerPhone" class="bc-field">@error('ownerPhone')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </fieldset>

            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Contact information</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-contact-phone">Contact phone</label><input id="onb-contact-phone" wire:model="contactPhone" class="bc-field">@error('contactPhone')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-contact-address">Contact address</label><input id="onb-contact-address" wire:model="contactAddress" class="bc-field">@error('contactAddress')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
            </fieldset>

            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Locale &amp; billing configuration</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-currency">Default currency</label><input id="onb-currency" wire:model="currency" class="bc-field">@error('currency')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-timezone">Timezone</label><input id="onb-timezone" wire:model="timezone" class="bc-field">@error('timezone')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
            </fieldset>

            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Domain / subdomain setup</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-subdomain">Subdomain</label><input id="onb-subdomain" wire:model="subdomain" class="bc-field" placeholder="tenant-slug">@error('subdomain')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-domain">Custom domain (optional)</label><input id="onb-domain" wire:model="customDomain" class="bc-field" placeholder="isp.example.com">@error('customDomain')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
            </fieldset>

            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Initial package setup</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-plan">SaaS plan</label><select id="onb-plan" wire:model="planId" class="bc-field"><option value="">Select a plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->trial_days }}d trial)</option>@endforeach</select>@error('planId')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-cycle">Billing cycle</label><select id="onb-cycle" wire:model="billingCycle" class="bc-field"><option value="monthly">Monthly</option><option value="yearly">Yearly</option></select></div>
                </div>
            </fieldset>

            <fieldset class="bc-panel space-y-4 p-5" style="border-radius:8px">
                <legend class="px-1 text-sm font-bold uppercase tracking-wide text-slate-400">Initial admin account</legend>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="bc-label" for="onb-admin-name">Admin name</label><input id="onb-admin-name" wire:model="adminName" class="bc-field">@error('adminName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="onb-admin-email">Admin email</label><input id="onb-admin-email" wire:model="adminEmail" type="email" class="bc-field">@error('adminEmail')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                </div>
                <div><label class="bc-label" for="onb-admin-password">Admin password</label><input id="onb-admin-password" wire:model="adminPassword" type="password" class="bc-field">@error('adminPassword')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </fieldset>

            <button type="submit" class="bc-primary">Complete onboarding</button>
        </form>

        <aside class="bc-panel h-fit space-y-3 p-5" style="border-radius:8px">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-400">Onboarding checklist</h2>
            <ul class="space-y-2 text-sm">
                @foreach($this->checklist as $step => $done)
                    <li class="flex items-center gap-2 {{ $done ? 'text-emerald-300' : 'text-slate-500' }}">
                        <span aria-hidden="true">{{ $done ? '✓' : '○' }}</span><span>{{ $step }}</span>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</div>
