<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Announcements</h1>
            <p class="mt-2 text-sm text-slate-500">Draft, schedule, and publish global or tenant-specific announcements.</p>
        </div>
        <button wire:click="create" class="bc-primary">Create announcement</button>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="mb-5 max-w-xs"><label class="bc-label" for="an-status-filter">Status</label><select id="an-status-filter" wire:model.live="statusFilter" class="bc-field"><option value="">All</option><option value="draft">Draft</option><option value="scheduled">Scheduled</option><option value="published">Published</option></select></div>

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Title</th><th>Type</th><th>Audience</th><th>Channels</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($announcements as $a)
                    <tr>
                        <td><div class="font-bold text-white">{{ $a->title }}</div><div class="text-xs text-slate-600">by {{ $a->creator?->name ?? 'System' }}</div></td>
                        <td class="capitalize">{{ $a->type }}</td>
                        <td>{{ $a->tenant?->name ?? 'Global' }}</td>
                        <td class="text-xs text-slate-500">
                            @if($a->dashboard_channel)Dashboard @endif
                            @if($a->email_channel)· Email @endif
                            @if($a->sms_channel)· SMS @endif
                            @if($a->push_channel)· Push @endif
                        </td>
                        <td><span class="capitalize font-semibold {{ match($a->status) { 'published' => 'text-emerald-300', 'scheduled' => 'text-amber-300', default => 'text-slate-500' } }}">{{ $a->status }}</span>@if($a->status === 'scheduled')<div class="text-[10px] text-slate-600">{{ $a->publish_at->format('d M Y, H:i') }}</div>@endif</td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-3">
                                @if($a->status === 'published')
                                    <button wire:click="unpublish({{ $a->id }})" class="font-semibold text-amber-300">Unpublish</button>
                                @endif
                                <button wire:click="edit({{ $a->id }})" class="font-semibold text-teal-300">Edit</button>
                                <button wire:click="delete({{ $a->id }})" wire:confirm="Delete this announcement?" class="font-semibold text-rose-300">Delete</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-12 text-center text-slate-600">No announcements yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($announcements->hasPages())<div class="border-t border-white/10 p-4">{{ $announcements->links() }}</div>@endif
    </div>

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative max-h-[90vh] w-full max-w-lg overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $announcementId ? 'Edit announcement' : 'Create announcement' }}</h2>
                <div class="mt-5 space-y-4">
                    <div><label class="bc-label" for="an-title">Title</label><input id="an-title" wire:model="title" class="bc-field">@error('title')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div><label class="bc-label" for="an-body">Message</label><textarea id="an-body" wire:model="body" rows="4" class="bc-field"></textarea>@error('body')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="an-type">Type</label><select id="an-type" wire:model="type" class="bc-field"><option value="general">General</option><option value="maintenance">Maintenance notice</option><option value="feature">Feature announcement</option><option value="payment">Payment notice</option><option value="system">System notice</option></select></div>
                        <div><label class="bc-label" for="an-tenant">Audience</label><select id="an-tenant" wire:model="tenantId" class="bc-field"><option value="">Global (all tenants)</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></div>
                    </div>
                    <div>
                        <span class="bc-label">Channels</span>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-sm text-slate-300">
                            <label class="inline-flex items-center gap-2"><input wire:model="dashboardChannel" type="checkbox">Dashboard</label>
                            <label class="inline-flex items-center gap-2"><input wire:model="emailChannel" type="checkbox">Email</label>
                            <label class="inline-flex items-center gap-2"><input wire:model="smsChannel" type="checkbox">SMS</label>
                            <label class="inline-flex items-center gap-2"><input wire:model="pushChannel" type="checkbox">Push</label>
                        </div>
                    </div>
                    <div><label class="bc-label" for="an-publish-at">Schedule for (optional)</label><input id="an-publish-at" wire:model="publishAt" type="datetime-local" class="bc-field">@error('publishAt')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="flex flex-wrap justify-end gap-3">
                        <button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button>
                        <button type="button" wire:click="saveDraft" class="bc-secondary">Save draft</button>
                        <button type="button" wire:click="schedule" class="bc-secondary">Schedule</button>
                        <button type="button" wire:click="publishNow" class="bc-primary">Publish now</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
