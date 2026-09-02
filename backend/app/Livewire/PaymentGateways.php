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
    public string $provider = 'bkash';
    public string $mode = 'sandbox';
    public array $credentialValues = [];
    public string $credentialsText = '';
    public string $webhookUrl = '';
    public string $webhookSecret = '';

    public ?int $logsForId = null;

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('payment_gateways', 'slug')->ignore($this->gatewayId)],
            'provider' => ['required', 'string', 'max:50'],
            'mode' => ['required', Rule::in(['sandbox', 'live'])],
            'credentialValues' => ['array'],
            'credentialsText' => ['nullable', 'string'],
            'webhookUrl' => ['nullable', 'url', 'max:255'],
            'webhookSecret' => ['nullable', 'string', 'max:255'],
        ];

        $fieldKeys = collect($this->providerCatalog())
            ->pluck('fields')
            ->flatten(1)
            ->pluck('key')
            ->unique();

        foreach ($fieldKeys as $field) {
            $rules['credentialValues.'.$field] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
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
        $this->reset(['gatewayId', 'name', 'slug', 'credentialsText', 'webhookUrl', 'webhookSecret', 'credentialValues']);
        $this->selectProvider('bkash');
        $this->viewMode = 'create';
    }

    public function selectProvider(string $provider): void
    {
        $catalog = $this->providerCatalog();

        if (!isset($catalog[$provider]) && $provider !== 'custom') {
            return;
        }

        $this->provider = $provider;

        if (!$this->gatewayId) {
            if ($provider === 'custom') {
                if ($this->name === '' || $this->slug === '') {
                    $this->name = $this->name ?: '';
                    $this->slug = $this->slug ?: '';
                }

                return;
            }

            $meta = $catalog[$provider];
            $this->name = $meta['label'];
            $this->slug = $meta['slug'];
            $this->mode = $meta['mode_supported'] ? 'sandbox' : 'live';
        }

        // Reset the visible credential field values when a preset changes.
        $preset = $provider === 'custom' ? [] : ($catalog[$provider]['fields'] ?? []);
        $this->credentialValues = collect($preset)->mapWithKeys(fn ($field) => [$field['key'] => ''])->all();
        $this->resetValidation('credentialValues.*');
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
        $this->credentialsText = '';
        $this->webhookUrl = $gateway->webhook_url ?? '';
        $this->webhookSecret = '';

        $catalog = $this->providerCatalog();
        $stored = $gateway->credentials ?? [];
        $this->credentialValues = [];

        if (isset($catalog[$gateway->provider])) {
            foreach ($catalog[$gateway->provider]['fields'] as $field) {
                // Secrets stay masked on edit; leave blank to keep the stored value.
                $this->credentialValues[$field['key']] = $field['secret'] ? '' : (string) ($stored[$field['key']] ?? '');
            }
        } else {
            // Legacy/custom gateway: fall back to the raw key=value editor.
            $this->credentialsText = collect($stored)->map(fn ($value, $key) => "{$key}={$value}")->implode("\n");
        }

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

        // Legacy raw textarea (custom providers) still parses key=value lines.
        $fromLines = collect(preg_split('/\r\n|\r|\n/', trim($data['credentialsText'])))
            ->filter()
            ->mapWithKeys(function ($line) {
                [$key, $value] = array_pad(explode('=', $line, 2), 2, '');

                return [trim($key) => trim($value)];
            })
            ->filter(fn ($value, $key) => $key !== '')
            ->all();

        $fromFields = collect($data['credentialValues'] ?? [])
            ->filter(fn ($value) => filled(trim((string) $value)))
            ->map(fn ($value) => trim((string) $value))
            ->all();

        // Fields always win over raw lines; blank preset fields never wipe stored credentials.
        $credentials = array_merge($fromLines, $fromFields);

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
            'metadata' => ['triggered_by' => auth()->user()?->name, 'mode' => $gateway->mode, 'provider' => $gateway->provider],
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

        $gateways = PaymentGateway::query()
            ->whereNull('archived_at')
            ->withCount(['logs as success_count' => fn ($q) => $q->where('status', 'success')])
            ->withCount(['logs as failed_count' => fn ($q) => $q->where('status', 'failed')])
            ->latest()
            ->get();

        $totalTests = (int) $gateways->sum(fn ($g) => $g->success_count + $g->failed_count);
        $successTests = (int) $gateways->sum('success_count');

        return view('livewire.payment-gateways', [
            'providers' => $this->providerCatalog(),
            'gateways' => $gateways,
            'stats' => [
                'total' => $gateways->count(),
                'active' => $gateways->where('is_active', true)->count(),
                'sandbox' => $gateways->where('mode', 'sandbox')->count(),
                'success_rate' => $totalTests > 0 ? round($successTests / $totalTests * 100) : null,
            ],
            'catalogProvider' => $this->providerCatalog()[$this->provider] ?? null,
            'isKnownProvider' => isset($this->providerCatalog()[$this->provider]),
            'logs' => $this->logsForId
                ? PaymentGatewayLog::where('payment_gateway_id', $this->logsForId)->latest('id')->limit(50)->get()
                : collect(),
        ]);
    }

    /**
     * Preset gateway providers with branded accent colours and structured credential fields.
     */
    public function providerCatalog(): array
    {
        return [
            'bkash' => [
                'label' => 'bKash',
                'slug' => 'bkash',
                'mode_supported' => true,
                'webhook_supported' => true,
                'letter' => 'bK',
                'chip' => 'bg-pink-50 text-pink-600 ring-1 ring-inset ring-pink-100 dark:bg-pink-500/10 dark:text-pink-400 dark:ring-pink-500/25',
                'avatar' => 'from-pink-500 to-rose-500',
                'hint' => 'bKash Merchant / Tokenized Checkout credentials for collecting customer payments.',
                'fields' => [
                    ['key' => 'merchant_number', 'label' => 'Merchant / wallet number', 'type' => 'text', 'secret' => false, 'placeholder' => '01XXXXXXXXX'],
                    ['key' => 'app_key', 'label' => 'App key', 'type' => 'text', 'secret' => false, 'placeholder' => 'bkash app key'],
                    ['key' => 'app_secret', 'label' => 'App secret', 'type' => 'password', 'secret' => true, 'placeholder' => 'bkash app secret'],
                    ['key' => 'username', 'label' => 'API username', 'type' => 'text', 'secret' => false, 'placeholder' => 'merchant api username'],
                    ['key' => 'password', 'label' => 'API password', 'type' => 'password', 'secret' => true, 'placeholder' => 'merchant api password'],
                ],
            ],
            'nagad' => [
                'label' => 'Nagad',
                'slug' => 'nagad',
                'mode_supported' => true,
                'webhook_supported' => true,
                'letter' => 'Ng',
                'chip' => 'bg-orange-50 text-orange-600 ring-1 ring-inset ring-orange-100 dark:bg-orange-500/10 dark:text-orange-400 dark:ring-orange-500/25',
                'avatar' => 'from-orange-500 to-amber-500',
                'hint' => 'Nagad Merchant credentials — merchant ID, keys and wallet number.',
                'fields' => [
                    ['key' => 'merchant_id', 'label' => 'Merchant ID', 'type' => 'text', 'secret' => false, 'placeholder' => 'Nagad merchant id'],
                    ['key' => 'merchant_number', 'label' => 'Merchant / wallet number', 'type' => 'text', 'secret' => false, 'placeholder' => '01XXXXXXXXX'],
                    ['key' => 'public_key', 'label' => 'Public key', 'type' => 'textarea', 'secret' => false, 'placeholder' => '-----BEGIN PUBLIC KEY-----'],
                    ['key' => 'private_key', 'label' => 'Private key', 'type' => 'textarea', 'secret' => true, 'placeholder' => '-----BEGIN RSA PRIVATE KEY-----'],
                ],
            ],
            'stripe' => [
                'label' => 'Stripe',
                'slug' => 'stripe',
                'mode_supported' => true,
                'webhook_supported' => true,
                'letter' => 'St',
                'chip' => 'bg-indigo-50 text-indigo-600 ring-1 ring-inset ring-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/25',
                'avatar' => 'from-indigo-500 to-violet-500',
                'hint' => 'Stripe API keys (publishable + secret). Point webhooks to your payment URL.',
                'fields' => [
                    ['key' => 'publishable_key', 'label' => 'Publishable key', 'type' => 'text', 'secret' => false, 'placeholder' => 'pk_live_...'],
                    ['key' => 'secret_key', 'label' => 'Secret key', 'type' => 'password', 'secret' => true, 'placeholder' => 'sk_live_...'],
                ],
            ],
            'bank' => [
                'label' => 'Bank Account',
                'slug' => 'bank-account',
                'mode_supported' => false,
                'webhook_supported' => false,
                'letter' => 'Ba',
                'chip' => 'bg-emerald-50 text-emerald-600 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/25',
                'avatar' => 'from-emerald-500 to-teal-500',
                'hint' => 'Bank transfer collection — shown to customers as a manual payment method.',
                'fields' => [
                    ['key' => 'bank_name', 'label' => 'Bank name', 'type' => 'text', 'secret' => false, 'placeholder' => 'e.g. bKash Bank / DBBL'],
                    ['key' => 'account_name', 'label' => 'Account name', 'type' => 'text', 'secret' => false, 'placeholder' => 'Beneficiary name'],
                    ['key' => 'account_number', 'label' => 'Account number', 'type' => 'text', 'secret' => false, 'placeholder' => 'e.g. 1234567890'],
                    ['key' => 'branch_name', 'label' => 'Branch', 'type' => 'text', 'secret' => false, 'placeholder' => 'e.g. Gulshan'],
                    ['key' => 'routing_number', 'label' => 'Routing number', 'type' => 'text', 'secret' => false, 'placeholder' => 'Bank routing number'],
                    ['key' => 'swift_code', 'label' => 'SWIFT / IBAN (optional)', 'type' => 'text', 'secret' => false, 'placeholder' => 'Optional'],
                ],
            ],
        ];
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
