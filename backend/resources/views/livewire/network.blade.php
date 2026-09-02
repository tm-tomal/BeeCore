<div class="space-y-6">
    <!-- Page header -->
    <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-theme-xs font-semibold uppercase tracking-wider text-brand-500 dark:text-brand-400">Infrastructure</p>
            <h1 class="mt-1 text-title-sm font-bold text-gray-800 dark:text-white/90">Network devices</h1>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Track routers, access systems, locations, and operating state.</p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <button wire:click="create" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                Add device
            </button>
        </div>
    </header>

    @if (session()->has('message'))
        <div class="flex items-start gap-3 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-theme-sm font-medium text-success-700 dark:border-success-500/20 dark:bg-success-500/10 dark:text-success-400">
            {{ session('message') }}
        </div>
    @endif

    <!-- Devices table -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="w-full overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-gray-100 bg-gray-50/60 dark:border-gray-800">
                    <tr>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Device</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">IP address</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-5 py-3.5 text-left text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</th>
                        <th class="px-5 py-3.5 text-right text-theme-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($devices as $device)
                        <tr class="transition-colors hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 align-middle text-theme-sm font-medium text-gray-800 dark:text-white/90">{{ $device->name }}</td>
                            <td class="px-5 py-4 align-middle">
                                <code class="text-theme-sm font-medium text-brand-600 dark:text-brand-400">{{ $device->ip_address }}</code>
                            </td>
                            <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $device->device_type }}</td>
                            <td class="px-5 py-4 align-middle text-theme-sm text-gray-600 dark:text-gray-400">{{ $device->location ?: 'Not set' }}</td>
                            <td class="px-5 py-4 align-middle text-right">
                                @if($device->status === 'online')
                                    <span class="rounded-full bg-success-50 px-2.5 py-0.5 text-theme-xs font-medium text-success-600 dark:bg-success-500/15 dark:text-success-500">{{ ucfirst($device->status) }}</span>
                                @elseif($device->status === 'maintenance')
                                    <span class="rounded-full bg-warning-50 px-2.5 py-0.5 text-theme-xs font-medium text-warning-600 dark:bg-warning-500/15 dark:text-warning-500">{{ ucfirst($device->status) }}</span>
                                @else
                                    <span class="rounded-full bg-error-50 px-2.5 py-0.5 text-theme-xs font-medium text-error-600 dark:bg-error-500/15 dark:text-error-500">{{ ucfirst($device->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-theme-sm text-gray-500 dark:text-gray-400">No network devices configured.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($devices->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $devices->links() }}
            </div>
        @endif
    </div>

    <!-- Add Device Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="network-dialog-title">
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
            <div class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl sm:p-6 dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-6 flex items-start justify-between gap-3">
                    <div>
                        <h3 id="network-dialog-title" class="text-lg font-semibold text-gray-800 dark:text-white/90">Add network device</h3>
                        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">Store inventory details only. Credentials are never collected here.</p>
                    </div>
                    <button wire:click="$set('showModal', false)" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/[0.03] dark:hover:text-gray-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit="save" class="space-y-5">
                    <div>
                        <label for="device-name" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Device name</label>
                        <input id="device-name" type="text" wire:model="name" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                        @error('name') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="device-ip" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">IP address</label>
                            <input id="device-ip" type="text" wire:model="ip_address" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="192.0.2.10">
                            @error('ip_address') <p class="mt-1.5 text-theme-xs text-error-600 dark:text-error-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="device-type" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Device type</label>
                            <select id="device-type" wire:model="device_type" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                                <option value="mikrotik">MikroTik</option>
                                <option value="radius">RADIUS</option>
                                <option value="olt">OLT</option>
                                <option value="router">Router</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="device-location" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Location</label>
                        <input id="device-location" type="text" wire:model="location" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30" placeholder="Central POP">
                    </div>

                    <div>
                        <label for="device-status" class="mb-1.5 block text-theme-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                        <select id="device-status" wire:model="status" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-theme-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30">
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                        <button type="button" wire:click="$set('showModal', false)" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-theme-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03]">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-theme-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600 disabled:cursor-not-allowed disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Save device</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
