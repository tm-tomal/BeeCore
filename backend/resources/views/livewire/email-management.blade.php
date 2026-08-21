<div>
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
        <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Email management</h1>
        <p class="mt-2 text-sm text-slate-500">SMTP/API providers, tenant quotas, delivery logs, and templates.</p>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2">
        <button wire:click="$set('tab', 'providers')" class="px-4 py-2 text-sm font-bold {{ $tab === 'providers' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Providers</button>
        <button wire:click="$set('tab', 'quotas')" class="px-4 py-2 text-sm font-bold {{ $tab === 'quotas' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Tenant quotas</button>
        <button wire:click="$set('tab', 'logs')" class="px-4 py-2 text-sm font-bold {{ $tab === 'logs' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Delivery logs</button>
        <button wire:click="$set('tab', 'templates')" class="px-4 py-2 text-sm font-bold {{ $tab === 'templates' ? 'bg-teal-300/10 text-teal-300' : 'text-slate-500' }}" style="border-radius:6px">Templates</button>
    </div>

    @if($tab === 'providers')
        <div class="mb-5 flex justify-end"><button wire:click="createProvider" class="bc-primary">Add provider</button></div>
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Provider</th><th>Type</th><th>From</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($providers as $p)
                        <tr>
                            <td><div class="font-bold text-white">{{ $p->name }}</div><div class="text-xs text-slate-600">{{ $p->provider }}</div></td>
                            <td class="uppercase">{{ $p->type }}</td>
                            <td>{{ $p->from_name }} @if($p->from_address)<span class="text-slate-500">&lt;{{ $p->from_address }}&gt;</span>@endif</td>
                            <td><span class="font-semibold {{ $p->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-right">
                                <div class="flex flex-wrap justify-end gap-3">
                                    <button wire:click="sendTestEmail({{ $p->id }})" class="font-semibold text-slate-300">Test</button>
                                    <button wire:click="toggleProviderActive({{ $p->id }})" class="font-semibold {{ $p->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $p->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    <button wire:click="editProvider({{ $p->id }})" class="font-semibold text-teal-300">Edit</button>
                                    <button wire:click="archiveProvider({{ $p->id }})" wire:confirm="Archive this provider?" class="font-semibold text-rose-300">Archive</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No email providers configured.</td></tr>
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
                            <div><label class="bc-label" for="em-name">Name</label><input id="em-name" wire:model.live="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                            <div><label class="bc-label" for="em-slug">Slug</label><input id="em-slug" wire:model="slug" class="bc-field">@error('slug')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="bc-label" for="em-type">Type</label><select id="em-type" wire:model="type" class="bc-field"><option value="smtp">SMTP</option><option value="api">API</option></select></div>
                            <div><label class="bc-label" for="em-provider">Provider</label><input id="em-provider" wire:model="provider" class="bc-field" placeholder="smtp, postmark, resend, ses, mailgun">@error('provider')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div><label class="bc-label" for="em-from-address">From address</label><input id="em-from-address" wire:model="fromAddress" class="bc-field">@error('fromAddress')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                            <div><label class="bc-label" for="em-from-name">From name</label><input id="em-from-name" wire:model="fromName" class="bc-field">@error('fromName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        </div>
                        <div><label class="bc-label" for="em-creds">Credentials (one <code>key=value</code> per line, encrypted at rest)</label><textarea id="em-creds" wire:model="credentialsText" rows="4" class="bc-field" placeholder="host=...&#10;port=587&#10;username=...&#10;password=..."></textarea></div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelProviderForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save provider</button></div>
                    </form>
                </div>
            </div>
        @endif
    @elseif($tab === 'quotas')
        <div class="mb-5 border border-white/10 bg-white/[0.02] p-4" style="border-radius:6px">
            <h2 class="mb-3 text-sm font-bold text-white">Set monthly email quota</h2>
            <form wire:submit="setQuota" class="grid gap-3 sm:grid-cols-3">
                <select wire:model="quotaTenantId" class="bc-field"><option value="">Select tenant</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select>
                <input wire:model="quotaAmount" type="number" min="0" class="bc-field" placeholder="Monthly quota">
                <button type="submit" class="bc-primary">Save quota</button>
            </form>
            @error('quotaTenantId')<p class="mt-2 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Tenant</th><th>Used this month</th><th>Monthly quota</th></tr></thead>
                <tbody>
                    @forelse($quotas as $quota)
                        <tr><td class="font-semibold text-white">{{ $quota->tenant->name }}</td><td>{{ number_format($quota->used_this_month) }}</td><td>{{ number_format($quota->monthly_quota) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-12 text-center text-slate-600">No tenant email quotas configured yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @elseif($tab === 'logs')
        <section class="mb-5 grid gap-3 sm:grid-cols-3">
            <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Sent / delivered</p><p class="mt-2 text-xl font-black text-emerald-300">{{ $report['sent'] }}</p></div>
            <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Failed</p><p class="mt-2 text-xl font-black text-rose-300">{{ $report['failed'] }}</p></div>
            <div class="bc-panel p-4"><p class="text-xs font-bold uppercase text-slate-500">Bulk emails</p><p class="mt-2 text-xl font-black text-white">{{ $report['bulk'] }}</p></div>
        </section>

        <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:max-w-lg">
            <div><label class="bc-label" for="em-status-filter">Status</label><select id="em-status-filter" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="queued">Queued</option><option value="sent">Sent</option><option value="delivered">Delivered</option><option value="failed">Failed</option></select></div>
            <div><label class="bc-label" for="em-category-filter">Category</label><select id="em-category-filter" wire:model.live="categoryFilter" class="bc-field"><option value="">All</option><option value="transactional">Transactional</option><option value="bulk">Bulk</option></select></div>
        </div>

        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Tenant</th><th>Provider</th><th>Recipient</th><th>Subject</th><th>Category</th><th>Status</th><th>Sent at</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->tenant?->name ?? 'Platform' }}</td>
                            <td>{{ $log->provider?->name ?? '—' }}</td>
                            <td><code class="text-slate-400">{{ $log->recipient }}</code></td>
                            <td class="max-w-48 truncate">{{ $log->subject }}</td>
                            <td class="capitalize">{{ $log->category }}</td>
                            <td><span class="capitalize font-semibold {{ match($log->status) { 'delivered', 'sent' => 'text-emerald-300', 'failed' => 'text-rose-300', default => 'text-amber-300' } }}">{{ $log->status }}</span></td>
                            <td class="text-xs text-slate-500">{{ $log->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-12 text-center text-slate-600">No email logs match these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($logs->hasPages())<div class="border-t border-white/10 p-4">{{ $logs->links() }}</div>@endif
        </div>
    @else
        <div class="mb-5 flex justify-end"><button wire:click="createTemplate" class="bc-primary">Add template</button></div>
        <div class="bc-table-wrap">
            <table class="bc-table">
                <thead><tr><th>Key</th><th>Name</th><th>Subject</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse($templates as $t)
                        <tr>
                            <td><code class="text-teal-300">{{ $t->key }}</code></td>
                            <td>{{ $t->name }}</td>
                            <td class="max-w-xs truncate text-slate-400">{{ $t->subject }}</td>
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
                        <tr><td colspan="5" class="py-12 text-center text-slate-600">No email templates yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($templateViewMode === 'create')
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-black/70" wire:click="cancelTemplateForm"></div>
                <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                    <h2 class="text-lg font-bold text-white">{{ $templateId ? 'Edit template' : 'Add template' }}</h2>
                    <form wire:submit="saveTemplate" class="mt-5 space-y-4">
                        <div><label class="bc-label" for="etpl-key">Key</label><input id="etpl-key" wire:model="templateKey" class="bc-field" placeholder="invoice_generated">@error('templateKey')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="etpl-name">Name</label><input id="etpl-name" wire:model="templateName" class="bc-field">@error('templateName')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="etpl-subject">Subject</label><input id="etpl-subject" wire:model="templateSubject" class="bc-field">@error('templateSubject')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="etpl-body">Body</label><textarea id="etpl-body" wire:model="templateBody" rows="5" class="bc-field"></textarea>@error('templateBody')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div class="flex justify-end gap-3"><button type="button" wire:click="cancelTemplateForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save template</button></div>
                    </form>
                </div>
            </div>
        @endif
    @endif
</div>
