<div>
    <header class="mb-8 flex items-center justify-between">
        <div><p class="text-sm uppercase tracking-[0.2em] text-cyan-300">Resellers</p><h1 class="mt-2 text-3xl font-black text-white">Manage Resellers</h1></div>
        <button wire:click="create" class="rounded-xl bg-cyan-500 px-4 py-2 font-semibold text-slate-950 transition hover:bg-cyan-400">Add Reseller</button>
    </header>
    @if (session()->has('message'))<div class="mb-6 rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-4 text-emerald-400">{{ session('message') }}</div>@endif
    <div class="overflow-hidden rounded-3xl border border-slate-800 bg-slate-900">
        <table class="w-full text-left text-sm"><thead class="border-b border-slate-800 bg-slate-950/50 text-slate-400"><tr>
            <th class="px-6 py-4 font-semibold uppercase">Reseller Details</th><th class="px-6 py-4 font-semibold uppercase">Balance (BDT)</th><th class="px-6 py-4 font-semibold uppercase">Status</th>
        </tr></thead><tbody class="divide-y divide-slate-800 text-slate-300">
            @forelse($resellers as $r)
            <tr class="hover:bg-slate-800/50"><td class="px-6 py-4"><div class="font-bold text-white">{{ $r->name }}</div><div class="text-xs text-slate-500">{{ $r->email }}</div></td><td class="px-6 py-4">{{ number_format($r->balance, 2) }}</td><td class="px-6 py-4">@if($r->status == 'active')<span class="text-emerald-400">Active</span>@else<span class="text-rose-400">Suspended</span>@endif</td></tr>
            @empty <tr><td colspan="3" class="p-6 text-center text-slate-500">No resellers found.</td></tr> @endforelse
        </tbody></table>
        <div class="p-4">{{ $resellers->links() }}</div>
    </div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
        <div class="relative w-full max-w-lg rounded-3xl border border-slate-800 bg-slate-900 p-8 shadow-2xl">
            <h3 class="mb-6 text-2xl font-bold text-white">Add Reseller</h3>
            <form wire:submit="save" class="space-y-4">
                <div><label class="mb-2 text-sm text-slate-300">Name</label><input type="text" wire:model="name" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"></div>
                <div><label class="mb-2 text-sm text-slate-300">Email Address</label><input type="email" wire:model="email" class="w-full rounded-xl border border-slate-700 bg-slate-950 p-3 text-white"></div>
                <div class="flex justify-end gap-3 pt-4"><button type="submit" class="rounded-xl bg-cyan-500 px-6 py-2 font-medium text-slate-950">Save</button></div>
            </form>
        </div>
    </div>
    @endif
</div>
