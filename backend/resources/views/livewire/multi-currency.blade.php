<div>
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-teal-300">SaaS administration</p>
            <h1 class="mt-2 text-2xl font-black text-white sm:text-3xl">Multi-currency</h1>
            <p class="mt-2 text-sm text-slate-500">Manage supported currencies, formatting rules, exchange rates, and rate history.</p>
        </div>
        <button wire:click="create" class="bc-primary">Add currency</button>
    </header>

    @if(session()->has('message'))
        <div class="mb-5 border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-300" style="border-radius:6px">{{ session('message') }}</div>
    @endif

    <div class="bc-table-wrap">
        <table class="bc-table">
            <thead><tr><th>Currency</th><th>Symbol</th><th>Decimals</th><th>Exchange rate</th><th>Default</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @forelse($currencies as $currency)
                    <tr>
                        <td class="font-bold text-white">{{ $currency->name }}</td>
                        <td><code class="text-teal-300">{{ $currency->code }}</code> {{ $currency->symbol }}</td>
                        <td>{{ $currency->decimal_places }}</td>
                        <td>{{ number_format($currency->exchange_rate, 6) }}</td>
                        <td>{{ $currency->is_default ? 'Yes' : '—' }}</td>
                        <td><span class="font-semibold {{ $currency->is_active ? 'text-emerald-300' : 'text-slate-500' }}">{{ $currency->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-3">
                                <button wire:click="openRate({{ $currency->id }})" class="font-semibold text-slate-300">Update rate</button>
                                <button wire:click="viewHistory({{ $currency->id }})" class="font-semibold text-slate-300">History</button>
                                @if(!$currency->is_default)<button wire:click="setDefault({{ $currency->id }})" class="font-semibold text-slate-300">Make default</button>@endif
                                <button wire:click="toggleActive({{ $currency->id }})" class="font-semibold {{ $currency->is_active ? 'text-amber-300' : 'text-emerald-300' }}">{{ $currency->is_active ? 'Deactivate' : 'Activate' }}</button>
                                <button wire:click="edit({{ $currency->id }})" class="font-semibold text-teal-300">Edit</button>
                                @if(!$currency->is_default)<button wire:click="delete({{ $currency->id }})" wire:confirm="Remove this currency?" class="font-semibold text-rose-300">Delete</button>@endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-12 text-center text-slate-600">No currencies configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($viewMode === 'create')
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="cancelForm"></div>
            <div class="bc-panel relative w-full max-w-md p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">{{ $currencyId ? 'Edit currency' : 'Add currency' }}</h2>
                <form wire:submit="save" class="mt-5 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="cur-code">Code</label><input id="cur-code" wire:model="code" class="bc-field" placeholder="BDT, USD, EUR">@error('code')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="cur-symbol">Symbol</label><input id="cur-symbol" wire:model="symbol" class="bc-field" placeholder="৳, $, €">@error('symbol')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    </div>
                    <div><label class="bc-label" for="cur-name">Name</label><input id="cur-name" wire:model="name" class="bc-field">@error('name')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="bc-label" for="cur-decimals">Decimal places</label><input id="cur-decimals" wire:model="decimalPlaces" type="number" min="0" max="6" class="bc-field">@error('decimalPlaces')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                        <div><label class="bc-label" for="cur-rate">Exchange rate (to base)</label><input id="cur-rate" wire:model="exchangeRate" type="number" step="0.000001" min="0.000001" class="bc-field">@error('exchangeRate')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    </div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="cancelForm" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save currency</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($rateForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative w-full max-w-sm p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Update exchange rate</h2>
                <form wire:submit="updateRate" class="mt-5 space-y-4">
                    <div><label class="bc-label" for="new-rate">New rate</label><input id="new-rate" wire:model="newRate" type="number" step="0.000001" min="0.000001" class="bc-field">@error('newRate')<p class="text-xs text-rose-300">{{ $message }}</p>@enderror</div>
                    <div class="flex justify-end gap-3"><button type="button" wire:click="closeModals" class="bc-secondary">Cancel</button><button type="submit" class="bc-primary">Save rate</button></div>
                </form>
            </div>
        </div>
    @endif

    @if($historyForId)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/70" wire:click="closeModals"></div>
            <div class="bc-panel relative max-h-[80vh] w-full max-w-md overflow-y-auto p-6" style="border-radius:8px">
                <h2 class="text-lg font-bold text-white">Exchange rate history</h2>
                <ul class="mt-5 space-y-3 text-sm">
                    @forelse($history as $entry)
                        <li class="flex items-center justify-between border-b border-white/10 pb-3">
                            <span class="text-slate-300">{{ number_format($entry->rate, 6) }}</span>
                            <span class="text-xs text-slate-600">{{ $entry->recorded_at->format('d M Y, H:i') }} · {{ $entry->recordedBy?->name ?? 'System' }}</span>
                        </li>
                    @empty
                        <li class="py-6 text-center text-slate-600">No rate history yet.</li>
                    @endforelse
                </ul>
                <div class="mt-5 flex justify-end"><button wire:click="closeModals" class="bc-secondary">Close</button></div>
            </div>
        </div>
    @endif
</div>
