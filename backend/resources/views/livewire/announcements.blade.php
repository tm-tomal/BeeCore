<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">SaaS administration</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Announcements</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Draft, schedule, and publish global or tenant-specific announcements.</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <button wire:click="create" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Create announcement</button>
        </div>
    </header>

    @if(session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 dark:border-success-500/20 dark:bg-success-500/10">
            <svg class="mt-0.5 size-5 shrink-0 stroke-current" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <p class="text-theme-sm text-success-700 dark:text-success-300">{{ session('message') }}</p>
        </div>
    @endif

    <div class="max-w-xs">
        <label for="an-status-filter" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
        <select id="an-status-filter" wire:model.live="statusFilter" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
            <option value="">All</option>
            <option value="draft">Draft</option>
            <option value="scheduled">Scheduled</option>
            <option value="published">Published</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-y border-gray-100 bg-gray-50/60 dark:border-gray-800">
                        <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Title</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Audience</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Channels</th>
                        <th scope="col" class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th scope="col" class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($announcements as $a)
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 align-middle text-theme-sm">
                                <div class="font-medium text-gray-800 dark:text-white/90">{{ $a->title }}</div>
                                <div class="mt-0.5 text-theme-xs text-gray-500 dark:text-gray-400">by {{ $a->creator?->name ?? 'System' }}</div>
                            </td>
                            <td class="px-5 py-4 align-middle text-theme-sm capitalize text-gray-600 dark:text-gray-400">{{ $a->type }}</td>
                            <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $a->tenant?->name ?? 'Global' }}</td>
                            <td class="px-5 py-4 align-middle text-theme-xs text-gray-500 dark:text-gray-400">
                                @if($a->dashboard_channel)Dashboard @endif
                                @if($a->email_channel)· Email @endif
                                @if($a->sms_channel)· SMS @endif
                                @if($a->push_channel)· Push @endif
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <span class="rounded-full px-2.5 py-1 text-theme-xs font-medium capitalize {{ match($a->status) { 'published' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500', 'scheduled' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-500', default => 'bg-gray-100 text-gray-600 dark:bg-white/[0.05] dark:text-gray-400' } }}">{{ $a->status }}</span>
                                @if($a->status === 'scheduled')<div class="mt-1 text-theme-xs text-gray-500 dark:text-gray-400">{{ $a->publish_at->format('d M Y, H:i') }}</div>@endif
                            </td>
                            <td class="px-5 py-4 align-middle">
                                <div class="flex flex-wrap items-center justify-end gap-1">
                                    @if($a->status === 'published')
                                        <button wire:click="unpublish({{ $a->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-warning-600 transition hover:bg-warning-50 dark:text-warning-400 dark:hover:bg-warning-500/10">Unpublish</button>
                                    @endif
                                    <button wire:click="edit({{ $a->id }})" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10">Edit</button>
                                    <button wire:click="delete({{ $a->id }})" wire:confirm="Delete this announcement?" class="rounded-lg px-3 py-2 text-theme-sm font-medium text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-4 align-middle">
                                <div class="py-10 text-center">
                                    <p class="text-theme-sm text-gray-500 dark:text-gray-400">No announcements yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($announcements->hasPages())<div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">{{ $announcements->links() }}</div>@endif
    </div>

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="cancelForm"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $announcementId ? 'Edit announcement' : 'Create announcement' }}</h3>
                    <button type="button" wire:click="cancelForm" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="space-y-5">
                    <div>
                        <label for="an-title" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Title</label>
                        <input id="an-title" wire:model="title" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('title')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="an-body" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Message</label>
                        <textarea id="an-body" wire:model="body" rows="4" class="min-h-24 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"></textarea>
                        @error('body')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="an-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Type</label>
                            <select id="an-type" wire:model="type" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="general">General</option>
                                <option value="maintenance">Maintenance notice</option>
                                <option value="feature">Feature announcement</option>
                                <option value="payment">Payment notice</option>
                                <option value="system">System notice</option>
                            </select>
                        </div>
                        <div>
                            <label for="an-tenant" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Audience</label>
                            <select id="an-tenant" wire:model="tenantId" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">Global (all tenants)</option>
                                @foreach($tenants as $tenant)<option value="{{ $tenant->id }}">{{ $tenant->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <span class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Channels</span>
                        <div class="mt-2 grid grid-cols-2 gap-2">
                            <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="dashboardChannel" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Dashboard</label>
                            <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="emailChannel" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Email</label>
                            <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="smsChannel" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">SMS</label>
                            <label class="inline-flex cursor-pointer items-center gap-2.5 text-theme-sm text-gray-700 dark:text-gray-400"><input wire:model="pushChannel" type="checkbox" class="h-4.5 w-4.5 rounded border-gray-300 bg-transparent text-brand-500 accent-brand-500 focus:ring-brand-500/30 dark:border-gray-700 dark:bg-gray-900">Push</label>
                        </div>
                    </div>
                    <div>
                        <label for="an-publish-at" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Schedule for (optional)</label>
                        <input id="an-publish-at" wire:model="publishAt" type="datetime-local" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800">
                        @error('publishAt')<p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="cancelForm" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Cancel</button>
                        <button type="button" wire:click="saveDraft" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Save draft</button>
                        <button type="button" wire:click="schedule" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200">Schedule</button>
                        <button type="button" wire:click="publishNow" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">Publish now</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
