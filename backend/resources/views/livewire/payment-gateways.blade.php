<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Payment gateways</h1>
            <p class="mt-2 text-sm text-slate-500">Register gateway credentials, webhook configuration, and monitor connection health.</p>
        </div>
        <button wire:click="create" class="bc-primary">Add gateway</button>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Gateway</th><th>Provider</th><th>Mode</th><th>Status</th><th>Transactions</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($gateways as $gateway)
                    @php $total = $gateway->success_count + $gateway->failed_count; @endphp
                    <tr>
                        <td><div class="font-bold text-white">{{ $gateway->name }}</div><div class="text-xs text-slate-600">{{ $gateway->slug }}</div></td>
                        <td class="capitalize">{{ $gateway->provider }}</td>
                        <td class="capitalize">{{ $gateway->mode }}</td>
                        <td><span class="font-semibold {{ $gateway->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $gateway->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-xs">{{ $total }} total · <span class="text-emerald-300">{{ $gateway->success_count }} ok</span> · <span class="text-rose-300">{{ $gateway->failed_count }} failed</span>@if($total)<div class="text-slate-600">{{ round($gateway->success_count / $total * 100) }}% success rate</div>@endif</td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-3">
                                <button wire:click="testConnection({{ $gateway->id }})" class="font-semibold text-slate-300">Test</button>
                                <button wire:click="viewLogs({{ $gateway->id }})" class="font-semibold text-slate-300">Logs</button>
                                <button wire:click="toggleActive({{ $gateway->id }})" class="font-semibold {{ $gateway->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $gateway->is_active ? 'Deactivate' : 'Activate' }}</button>
                                <button wire:click="edit({{ $gateway->id }})" class="font-semibold text-teal-300">Edit</button>
                                <button wire:click="archive({{ $gateway->id }})" wire:confirm="Archive this gateway?" class="font-semibold text-rose-300">Archive</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-slate-600">No payment gateways configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancel"></div>
            <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $gatewayId ? 'Edit gateway' : 'Add gateway' }}</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="gw-name">Name</label><input id="gw-name" wire:model.live="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="gw-slug">Slug</label><input id="gw-slug" wire:model="slug" class="bc-field">@error('slug')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="gw-provider">Provider</label><input id="gw-provider" wire:model="provider" class="bc-field" placeholder="stripe, bkash, nagad, sslcommerz, manual">@error('provider')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="gw-mode">Mode</label><select id="gw-mode" wire:model="mode" class="bc-field"><option value="sandbox">Sandbox</option><option value="live">Live</option></select></div>
                    </div>
                    <div><label class="bc-label" for="gw-webhook">Webhook URL</label><input id="gw-webhook" wire:model="webhookUrl" class="bc-field" placeholder="https://...">@error('webhookUrl')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="gw-secret">Webhook secret {{ $gatewayId ? '(leave blank to keep current)' : '' }}</label><input id="gw-secret" wire:model="webhookSecret" type="password" class="bc-field"></div>
                    <div><label class="bc-label" for="gw-credentials">Credentials (one <code>key=value</code> per line, encrypted at rest)</label><textarea id="gw-credentials" wire:model="credentialsText" rows="4" class="bc-field" placeholder="api_key=...&#10;api_secret=..."></textarea></div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancel" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save gateway</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($logsForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeLogs"></div>
            <div class="bc-panel relative max-h-[80vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Gateway logs</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @forelse($logs as $log)
                        <li class="border-b border-white/10 pb-3">
                            <div class="flex items-center justify-between"><span class="font-semibold {{ $log->status === 'success' ? 'text-emerald-300' : 'text-rose-300' }}">{{ $log->event }}</span><span class="text-xs text-slate-600">{{ $log->created_at->format('d M Y, H:i') }}</span></div>
                            <div class="text-xs uppercase text-slate-500">{{ $log->status }}</div>
                        </li>
                    @empty
                        <li class="py-6 text-center text-slate-600">No logs recorded yet.</li>
                    @endforelse
                </ul>
                <div class="mt-5 flex justify-end"><button wire:click="closeLogs" class="bc-secondary">Close</button></div>
            </div>
        </div>
    @endif
</div>
