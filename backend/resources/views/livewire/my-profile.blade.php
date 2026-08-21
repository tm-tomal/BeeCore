<div>
    @php use Illuminate\Support\Str; @endphp
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">Account</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">My profile</h1>
        <p class="mt-2 text-sm text-slate-500">Profile information, security, notification preferences, language, and timezone.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    @if($issuedTwoFactorSecret)
        <div class="mb-5 border border-amber-400/25 bg-amber-400/10 p-4 text-sm text-amber-200" style="border-radius:6px">
            <p class="font-bold">Save this two-factor secret now — it will not be shown again.</p>
            <code class="mt-2 block break-all text-teal-300">{{ $issuedTwoFactorSecret }}</code>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <form wire:submit="saveProfile" class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Profile information</h2>
            <div><label class="bc-label" for="mp-name">Name</label><input id="mp-name" wire:model="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <div><label class="bc-label" for="mp-email">Email</label><input id="mp-email" wire:model="email" type="email" class="bc-field">@error('email')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label class="bc-label" for="mp-language">Language</label><select id="mp-language" wire:model="language" class="bc-field">@foreach($languages as $lang)<option value="{{ $lang->code }}">{{ $lang->name }}</option>@endforeach</select>@error('language')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                <div><label class="bc-label" for="mp-timezone">Timezone</label><input id="mp-timezone" wire:model="timezone" class="bc-field">@error('timezone')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            </div>
            <button type="submit" class="bc-primary">Save profile</button>
        </form>

        <form wire:submit="changePassword" class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Change password</h2>
            <div><label class="bc-label" for="mp-current-password">Current password</label><input id="mp-current-password" wire:model="currentPassword" type="password" class="bc-field">@error('currentPassword')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <div><label class="bc-label" for="mp-new-password">New password</label><input id="mp-new-password" wire:model="newPassword" type="password" class="bc-field">@error('newPassword')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <div><label class="bc-label" for="mp-new-password-confirm">Confirm new password</label><input id="mp-new-password-confirm" wire:model="newPasswordConfirmation" type="password" class="bc-field">@error('newPasswordConfirmation')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
            <button type="submit" class="bc-primary">Change password</button>
        </form>

        <div class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Two-factor authentication</h2>
            @if(auth()->user()->two_factor_enabled)
                <p class="text-sm text-emerald-300">Two-factor authentication is enabled.</p>
                <button wire:click="disableTwoFactor" wire:confirm="Disable two-factor authentication?" class="bc-secondary">Disable 2FA</button>
            @else
                <p class="text-sm text-slate-500">Two-factor authentication is currently disabled.</p>
                <button wire:click="enableTwoFactor" class="bc-primary">Enable 2FA</button>
            @endif
        </div>

        <form wire:submit="saveNotificationPreferences" class="bc-panel space-y-4 p-5" style="border-radius:8px">
            <h2 class="font-bold text-white">Notification preferences</h2>
            <label class="flex items-center gap-3 text-sm text-slate-300"><input wire:model="notifyEmail" type="checkbox">Email notifications</label>
            <label class="flex items-center gap-3 text-sm text-slate-300"><input wire:model="notifySms" type="checkbox">SMS notifications</label>
            <label class="flex items-center gap-3 text-sm text-slate-300"><input wire:model="notifyPush" type="checkbox">Push notifications</label>
            <button type="submit" class="bc-primary">Save preferences</button>
        </form>

        <div class="bc-panel space-y-4 p-5 lg:col-span-2" style="border-radius:8px">
            <div class="flex items-center justify-between"><h2 class="font-bold text-white">Active sessions</h2><button wire:click="terminateOtherSessions" class="bc-secondary">Terminate other sessions</button></div>
            <ul class="space-y-2 text-sm">
                @foreach($sessions as $s)
                    <li class="flex items-center justify-between border-b border-white/10 pb-2">
                        <div><code class="text-slate-400">{{ $s->ip_address }}</code> <span class="text-xs text-slate-600">{{ Str::limit($s->user_agent, 60) }}</span></div>
                        <div class="flex items-center gap-2">
                            @if($s->id === $currentSessionId)<span class="text-xs font-bold text-teal-300">This device</span>@endif
                            <span class="text-xs text-slate-600">{{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->format('d M Y, H:i') }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="bc-panel space-y-4 p-5 lg:col-span-2" style="border-radius:8px">
            <h2 class="font-bold text-white">Login history</h2>
            <ul class="space-y-2 text-sm">
                @forelse($loginHistory as $attempt)
                    <li class="flex items-center justify-between border-b border-white/10 pb-2">
                        <span class="font-semibold {{ $attempt->successful ? 'text-emerald-300' : 'text-rose-300' }}">{{ $attempt->successful ? 'Success' : 'Failed' }}</span>
                        <span class="text-xs text-slate-600">{{ $attempt->ip_address }} · {{ $attempt->created_at->format('d M Y, H:i') }}</span>
                    </li>
                @empty
                    <li class="py-4 text-center text-slate-600">No login history yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
