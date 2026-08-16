<div>
    <header class="mb-8 flex items-center justify-between">
        <div><p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Network</p><h1 class="mt-2 text-3xl font-black text-white">Network Devices</h1></div>
        <button wire:click="create" class="rounded-xl bg-cyan-500 px-4 py-2 font-semibold text-slate-950 transition hover:bg-cyan-400">Add Device</button>
    </header>
    @if (session()->has('message'))<div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-400">{{ session('message') }}</div>@endif
    <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-sm"><thead class="border-b border-slate-800 bg-slate-950/50 text-slate-400"><tr>
            <th class="px-6 py-4 font-semibold uppercase">Device Name</th><th class="px-6 py-4 font-semibold uppercase">IP Address</th><th class="px-6 py-4 font-semibold uppercase">Status</th>
        </tr></thead><tbody class="divide-y divide-slate-800 text-slate-300">
            @forelse($devices as $d)
            <tr class="hover:bg-slate-800/50"><td class="px-6 py-4 font-medium text-white">{{ $d->name }} <span class="ml-2 rounded bg-slate-800 px-2 py-1 text-xs text-slate-400 uppercase">{{ $d->device_type }}</span></td><td class="px-6 py-4">{{ $d->ip_address }}</td><td class="px-6 py-4">@if($d->status == 'online')<span class="text-emerald-400">Online</span>@else<span class="text-rose-400">Offline</span>@endif</td></tr>
            @empty <tr><td colspan="3" class="p-6 text-center text-slate-500">No network devices found.</td></tr> @endforelse
        </tbody></table>
        <div class="p-4">{{ $devices->links() }}</div>
    </div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
        <div class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
            <h3 class="mb-6 text-2xl font-bold text-white">Add Device</h3>
            <form wire:submit="save" class="space-y-4">
                <div><label class="mb-2 text-sm text-slate-300">Device Name</label><input type="text" wire:model="name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"></div>
                <div><label class="mb-2 text-sm text-slate-300">IP Address</label><input type="text" wire:model="ip_address" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"></div>
                <div class="flex justify-end gap-3 pt-4"><button type="submit" class="rounded-xl bg-cyan-500 px-6 py-2 font-medium text-slate-950">Save Device</button></div>
            </form>
        </div>
    </div>
    @endif
</div>
