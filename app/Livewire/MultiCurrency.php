<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Currency;
use App\Models\CurrencyRateHistory;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MultiCurrency extends Component
{
    public string $viewMode = 'index';
    public string $search = '';
    public ?int $currencyId = null;
    public string $code = '';
    public string $name = '';
    public string $symbol = '';
    public int $decimalPlaces = 2;
    public float $exchangeRate = 1;

    public ?int $rateForId = null;
    public float $newRate = 1;

    public ?int $historyForId = null;

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('currencies', 'code')->ignore($this->currencyId)],
            'name' => ['required', 'string', 'max:255'],
            'symbol' => ['required', 'string', 'max:10'],
            'decimalPlaces' => ['required', 'integer', 'min:0', 'max:6'],
            'exchangeRate' => ['required', 'numeric', 'min:0.000001'],
        ];
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['currencyId', 'code', 'name', 'symbol']);
        $this->decimalPlaces = 2;
        $this->exchangeRate = 1;
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $currency = Currency::findOrFail($id);
        $this->currencyId = $currency->id;
        $this->code = $currency->code;
        $this->name = $currency->name;
        $this->symbol = $currency->symbol;
        $this->decimalPlaces = $currency->decimal_places;
        $this->exchangeRate = (float) $currency->exchange_rate;
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function updatedSearch(): void
    {
        // Search re-renders automatically.
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $currency = $this->currencyId ? Currency::findOrFail($this->currencyId) : new Currency(['is_active' => true]);
        $rateChanged = !$currency->exists || (float) $currency->exchange_rate !== (float) $data['exchangeRate'];

        $currency->fill([
            'code' => $data['code'],
            'name' => $data['name'],
            'symbol' => $data['symbol'],
            'decimal_places' => $data['decimalPlaces'],
            'exchange_rate' => $data['exchangeRate'],
        ])->save();

        if ($rateChanged) {
            $this->logRate($currency);
        }

        AuditLog::record($this->currencyId ? 'currency.updated' : 'currency.created', $currency, ['code' => $currency->code]);

        $this->viewMode = 'index';
        session()->flash('message', $this->currencyId ? 'Currency updated.' : 'Currency added.');
    }

    public function toggleActive(int $id): void
    {
        $this->assertSuperAdmin();
        $currency = Currency::findOrFail($id);
        abort_if($currency->is_default && $currency->is_active, 422, 'The default currency cannot be deactivated.');
        $currency->update(['is_active' => !$currency->is_active]);
        AuditLog::record($currency->is_active ? 'currency.activated' : 'currency.deactivated', $currency);
        session()->flash('message', 'Currency '.($currency->is_active ? 'activated' : 'deactivated').'.');
    }

    public function setDefault(int $id): void
    {
        $this->assertSuperAdmin();
        $currency = Currency::findOrFail($id);
        abort_unless($currency->is_active, 422, 'Only an active currency can become the default.');

        Currency::where('is_default', true)->update(['is_default' => false]);
        $currency->update(['is_default' => true]);
        AuditLog::record('currency.default_set', $currency);
        session()->flash('message', $currency->name.' is now the default currency.');
    }

    public function delete(int $id): void
    {
        $this->assertSuperAdmin();
        $currency = Currency::findOrFail($id);
        abort_if($currency->is_default, 422, 'The default currency cannot be deleted.');

        $currency->delete();
        AuditLog::record('currency.deleted', null, ['code' => $currency->code]);
        session()->flash('message', 'Currency removed.');
    }

    public function openRate(int $id): void
    {
        $currency = Currency::findOrFail($id);
        $this->rateForId = $id;
        $this->newRate = (float) $currency->exchange_rate;
    }

    public function updateRate(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate(['newRate' => ['required', 'numeric', 'min:0.000001']]);

        $currency = Currency::findOrFail($this->rateForId);
        $currency->update(['exchange_rate' => $data['newRate']]);
        $this->logRate($currency);

        AuditLog::record('currency.rate_updated', $currency, ['rate' => $data['newRate']]);
        $this->rateForId = null;
        session()->flash('message', 'Exchange rate updated.');
    }

    public function viewHistory(int $id): void
    {
        $this->historyForId = $id;
    }

    public function closeModals(): void
    {
        $this->rateForId = null;
        $this->historyForId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.multi-currency', [
            'currencies' => Currency::query()
                ->when($this->search !== '', fn ($query) => $query->where(fn ($q) => $q
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
                    ->orWhere('symbol', 'like', '%'.$this->search.'%')))
                ->orderBy('name')
                ->get(),
            'history' => $this->historyForId
                ? CurrencyRateHistory::where('currency_id', $this->historyForId)->with('recordedBy')->latest('recorded_at')->limit(50)->get()
                : collect(),
        ]);
    }

    private function logRate(Currency $currency): void
    {
        CurrencyRateHistory::create([
            'currency_id' => $currency->id,
            'rate' => $currency->exchange_rate,
            'recorded_by' => auth()->id(),
            'recorded_at' => now(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
