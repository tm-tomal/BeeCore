<div>
    <header class="mb-6"><p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p><h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Audit activity</h1><p class="mt-2 text-sm text-slate-500">Review authentication, impersonation, billing, and record changes across workspaces.</p></header>

    <div class="mb-5 grid gap-3 sm:grid-cols-2 lg:max-w-3xl"><div><label for="audit-search" class="bc-label">Action</label><input id="audit-search" wire:model.live.debounce.300ms="search" type="search" class="bc-field" placeholder="e.g. auth.login"></div><div><label for="audit-tenant" class="bc-label">Tenant</label><select id="audit-tenant" wire:model.live="tenantId" class="bc-field"><option value="">All workspaces</option>@foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach</select></div></div>

    <div class="bc-table-wrap">
        <table class="bc-table"><thead><tr><th>Time</th><th>Action</th><th>Actor</th><th>Workspace</th><th>Subject</th><th>IP address</th></tr></thead><tbody>
            @forelse($logs as $log)
                <tr><td class="whitespace-nowrap">{{ $log->created_at->format('d M Y, H:i') }}</td><td><code class="text-teal-300">{{ $log->action }}</code></td><td>{{ $log->user?->name ?? 'System' }}</td><td>{{ $log->tenant?->name ?? 'Platform' }}</td><td><div class="max-w-48 truncate">{{ $log->subject_type ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}</div></td><td><code class="text-slate-500">{{ $log->ip_address ?? '—' }}</code></td></tr>
            @empty<tr><td colspan="6" class="py-12 text-center text-slate-600">No audit activity matches these filters.</td></tr>@endforelse
        </tbody></table>
        @if($logs->hasPages())<div class="border-t border-white/10 p-4">{{ $logs->links() }}</div>@endif
    </div>
</div>