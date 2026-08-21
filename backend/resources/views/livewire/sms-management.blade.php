<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">SMS management</h1>
        <p class="mt-2 text-sm text-slate-500">Providers, sender IDs, tenant credit balances, delivery logs, and templates.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2">
        <button wire:click="$set('tab', 'providers')" class="px-4 py-2 text-sm font-bold {{ $tab === 'providers' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Providers</button>
        <button wire:click="$set('tab', 'balances')" class="px-4 py-2 text-sm font-bold {{ $tab === 'balances' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Tenant balances</button>
        <button wire:click="$set('tab', 'logs')" class="px-4 py-2 text-sm font-bold {{ $tab === 'logs' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Delivery logs</button>
        <button wire:click="$set('tab', 'templates')" class="px-4 py-2 text-sm font-bold {{ $tab === 'templates' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Templates</button>
    </div>

    @if($tab === 'providers')
        <div class="mb-5 flex justify-end"><button wire:click="createProvider" class="bc-primary">Add provider</button></div>
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Provider</th><th>Sender ID</th><th>Price/SMS</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($providers as $p)
                        <tr>
                            <td><div class="font-bold text-white">{{ $p->name }}</div><div class="text-xs text-slate-600">{{ $p->provider }}</div></td>
                            <td>{{ $p->sender_id ?? '—' }}</td>
                            <td>৳{{ number_format($p->price_per_sms, 4) }}</td>
                            <td><span class="font-semibold {{ $p->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="sendTestSms({{ $p->id }})" class="font-semibold text-slate-300">Test</button>
                                    <button wire:click="toggleProviderActive({{ $p->id }})" class="font-semibold {{ $p->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $p->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    <button wire:click="editProvider({{ $p->id }})" class="font-semibold text-teal-300">Edit</button>
                                    <button wire:click="archiveProvider({{ $p->id }})" wire:confirm="Archive this provider?" class="font-semibold text-rose-300">Archive</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No SMS providers configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($providerViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="cancelProviderForm"></div>
                <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">{{ $providerId ? 'Edit provider' : 'Add provider' }}</h2>
                    <form wire:submit="saveProvider" class="mt-5 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="bc-label" for="sms-name">Name</label><input id="sms-name" wire:model.live="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                            <div><label class="bc-label" for="sms-slug">Slug</label><input id="sms-slug" wire:model="slug" class="bc-field">@error('slug')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="bc-label" for="sms-provider">Provider</label><input id="sms-provider" wire:model="provider" class="bc-field" placeholder="twilio, banglalink, ssl_wireless">@error('provider')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                            <div><label class="bc-label" for="sms-sender">Sender ID</label><input id="sms-sender" wire:model="senderId" class="bc-field">@error('senderId')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        </div>
                        <div><label class="bc-label" for="sms-price">Price per SMS</label><input id="sms-price" wire:model="pricePerSms" type="number" step="0.0001" min="0" class="bc-field">@error('pricePerSms')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="sms-creds">Credentials (one <code>key=value</code> per line, encrypted at rest)</label><textarea id="sms-creds" wire:model="credentialsText" rows="4" class="bc-field"></textarea></div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelProviderForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save provider</button></div>
                    </form>
                </div>
            </div>
        @endif
    @elseif($tab === 'balances')
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <h2 class="mb-3 text-sm font-bold text-white">Add SMS credit</h2>
            <form wire:submit="addCredit" class="grid gap-3 sm:grid-cols-3">
                <select wire:model="creditTenantId" class="bc-field"><option value="">Select tenant</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>
                <input wire:model="creditAmount" type="number" min="1" class="bc-field" placeholder="Credits">
                <button type="submit" class="bc-primary">Add credit</button>
            </form>
            @error('creditTenantId')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Tenant</th><th>Balance</th></tr></thead>
                <tbody>
                    @forelse($balances as $balance)
                        <tr><td class="font-semibold text-white">{{ $balance->tenant->name }}</td><td>{{ number_format($balance->balance) }} credits</td></tr>
                    @empty
                        <tr><td colspan="2" class="py-12 text-center text-slate-600">No tenant SMS balances yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'logs')
        <section class="mb-5 grid gap-3 sm:grid-cols-3">
            <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Sent / delivered</p><p class="mt-2 text-xl font-black text-emerald-300">{{ $report['sent'] }}</p></div>
            <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Failed</p><p class="mt-2 text-xl font-black text-rose-300">{{ $report['failed'] }}</p></div>
            <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">SMS cost</p><p class="mt-2 text-xl font-black text-white">৳{{ number_format($report['cost'], 2) }}</p></div>
        </section>

        <div class="mb-5 max-w-xs"><label class="bc-label" for="sms-status-filter">Status</label><select id="sms-status-filter" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="queued">Queued</option><option value="sent">Sent</option><option value="delivered">Delivered</option><option value="failed">Failed</option></select></div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Tenant</th><th>Provider</th><th>Recipient</th><th>Status</th><th>Cost</th><th>Sent at</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->tenant?->name ?? 'Platform' }}</td>
                            <td>{{ $log->provider?->name ?? '—' }}</td>
                            <td><code class="text-slate-400">{{ $log->recipient }}</code></td>
                            <td><span class="capitalize font-semibold {{ match($log->status) { 'delivered', 'sent' => 'text-emerald-300', 'failed' => 'text-rose-300', default => 'text-amber-300' } }}">{{ $log->status }}</span></td>
                            <td>৳{{ number_format($log->cost, 4) }}</td>
                            <td class="text-xs text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-12 text-center text-slate-600">No SMS logs match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($logs->hasPages())<div class="border-t border-white/10 p-4">{{ $logs->links() }}</div>@endif
        </div>
    @else
        <div class="mb-5 flex justify-end"><button wire:click="createTemplate" class="bc-primary">Add template</button></div>
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Key</th><th>Name</th><th>Content</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($templates as $t)
                        <tr>
                            <td><code class="text-teal-300">{{ $t->key }}</code></td>
                            <td>{{ $t->name }}</td>
                            <td class="max-w-xs truncate text-slate-400">{{ $t->content }}</td>
                            <td><span class="font-semibold {{ $t->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="toggleTemplateActive({{ $t->id }})" class="font-semibold {{ $t->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $t->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    <button wire:click="editTemplate({{ $t->id }})" class="font-semibold text-teal-300">Edit</button>
                                    <button wire:click="deleteTemplate({{ $t->id }})" wire:confirm="Delete this template?" class="font-semibold text-rose-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No SMS templates yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templateViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="cancelTemplateForm"></div>
                <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">{{ $templateId ? 'Edit template' : 'Add template' }}</h2>
                    <form wire:submit="saveTemplate" class="mt-5 space-y-4">
                        <div><label class="bc-label" for="tpl-key">Key</label><input id="tpl-key" wire:model="templateKey" class="bc-field" placeholder="welcome_sms">@error('templateKey')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="tpl-name">Name</label><input id="tpl-name" wire:model="templateName" class="bc-field">@error('templateName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="tpl-content">Content</label><textarea id="tpl-content" wire:model="templateContent" rows="4" class="bc-field"></textarea>@error('templateContent')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelTemplateForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save template</button></div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
