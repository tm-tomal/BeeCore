<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayLog;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PaymentGateways extends Component
{
    public string $viewMode = 'index';
    public ?int $gatewayId = null;

    public string $name = '';
    public string $slug = '';
    public string $provider = 'manual';
    public string $mode = 'sandbox';
    public string $credentialsText = '';
    public string $webhookUrl = '';
    public string $webhookSecret = '';

    public ?int $logsForId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('payment_gateways', 'slug')->ignore($this->gatewayId)],
            'provider' => ['required', 'string', 'max:50'],
            'mode' => ['required', Rule::in(['sandbox', 'live'])],
            'credentialsText' => ['nullable', 'string'],
            'webhookUrl' => ['nullable', 'url', 'max:255'],
            'webhookSecret' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function updatedName($value): void
    {
        if (!$this->gatewayId) {
            $this->slug = Str::slug($value);
        }
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['gatewayId', 'name', 'slug', 'credentialsText', 'webhookUrl', 'webhookSecret']);
        $this->provider = 'manual';
        $this->mode = 'sandbox';
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $gateway = PaymentGateway::findOrFail($id);
        $this->gatewayId = $gateway->id;
        $this->name = $gateway->name;
        $this->slug = $gateway->slug;
        $this->provider = $gateway->provider;
        $this->mode = $gateway->mode;
        $this->credentialsText = collect($gateway->credentials ?? [])->map(fn ($value, $key) => "{$key}={$value}")->implode("\n");
        $this->webhookUrl = $gateway->webhook_url ?? '';
        $this->webhookSecret = '';
        $this->viewMode = 'create';
    }

    public function cancel(): void
    {
        $this->viewMode = 'index';
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $credentials = collect(preg_split('/\r\n|\r|\n/', trim($data['credentialsText'])))
            ->filter()
            ->mapWithKeys(function ($line) {
                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');

                return [trim($key) => trim($value)];
            })
            ->filter(fn ($value, $key) => $key !== '')
            ->all();

        $attributes = [
            'name' => $data['name'],
            'slug' => $data['slug'],
            'provider' => $data['provider'],
            'mode' => $data['mode'],
            'credentials' => $credentials,
            'webhook_url' => $data['webhookUrl'] ?: null,
        ];

        if (filled($data['webhookSecret'])) {
            $attributes['webhook_secret'] = $data['webhookSecret'];
        }

        $gateway = $this->gatewayId ? PaymentGateway::findOrFail($this->gatewayId) : new PaymentGateway(['is_active' => false]);
        $gateway->fill($attributes)->save();

        AuditLog::record($this->gatewayId ? 'payment_gateway.updated' : 'payment_gateway.created', $gateway, ['provider' => $gateway->provider]);

        $this->viewMode = 'index';
        session()->flash('message', $this->gatewayId ? 'Gateway updated.' : 'Gateway created.');
    }

    public function toggleActive(int $id): void
    {
        $this->assertSuperAdmin();
        $gateway = PaymentGateway::findOrFail($id);
        $gateway->update(['is_active' => !$gateway->is_active]);
        AuditLog::record($gateway->is_active ? 'payment_gateway.activated' : 'payment_gateway.deactivated', $gateway);
        session()->flash('message', 'Gateway '.($gateway->is_active ? 'activated' : 'deactivated').'.');
    }

    public function archive(int $id): void
    {
        $this->assertSuperAdmin();
        $gateway = PaymentGateway::whereNull('archived_at')->findOrFail($id);
        $gateway->update(['is_active' => false, 'archived_at' => now()]);
        AuditLog::record('payment_gateway.archived', $gateway);
        session()->flash('message', 'Gateway archived.');
    }

    public function testConnection(int $id): void
    {
        $this->assertSuperAdmin();
        $gateway = PaymentGateway::findOrFail($id);

        $log = PaymentGatewayLog::create([
            'payment_gateway_id' => $gateway->id,
            'event' => 'connection_test',
            'status' => $gateway->is_active ? 'success' : 'failed',
            'metadata' => ['triggered_by' => auth()->user()?->name, 'mode' => $gateway->mode],
            'created_at' => now(),
        ]);

        AuditLog::record('payment_gateway.connection_tested', $gateway, ['result' => $log->status]);
        session()->flash('message', 'Connection test logged as '.$log->status.'.');
    }

    public function viewLogs(int $id): void
    {
        $this->logsForId = $id;
    }

    public function closeLogs(): void
    {
        $this->logsForId = null;
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.payment-gateways', [
            'gateways' => PaymentGateway::query()
                ->whereNull('archived_at')
                ->withCount(['logs as success_count' => fn ($q) => $q->where('status', 'success')])
                ->withCount(['logs as failed_count' => fn ($q) => $q->where('status', 'failed')])
                ->latest()
                ->get(),
            'logs' => $this->logsForId
                ? PaymentGatewayLog::where('payment_gateway_id', $this->logsForId)->latest('id')->limit(50)->get()
                : collect(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
