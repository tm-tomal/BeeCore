<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\SmsLog;
use App\Models\SmsProvider;
use App\Models\SmsTemplate;
use App\Models\Tenant;
use App\Models\TenantSmsBalance;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class SmsManagement extends Component
{
    use WithPagination;

    public string $tab = 'providers';

    // Provider form
    public string $providerViewMode = 'index';
    public ?int $providerId = null;
    public string $name = '';
    public string $slug = '';
    public string $provider = 'manual';
    public string $senderId = '';
    public float $pricePerSms = 0;
    public string $credentialsText = '';

    // Balance form
    public ?int $creditTenantId = null;
    public int $creditAmount = 100;

    // Log filters
    public string $statusFilter = '';

    // Template form
    public string $templateViewMode = 'index';
    public ?int $templateId = null;
    public string $templateKey = '';
    public string $templateName = '';
    public string $templateContent = '';

    protected function rules(): array
    {
        if ($this->tab === 'templates') {
            return [
                'templateKey' => ['required', 'string', 'max:255', Rule::unique('sms_templates', 'key')->ignore($this->templateId)],
                'templateName' => ['required', 'string', 'max:255'],
                'templateContent' => ['required', 'string', 'max:1000'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('sms_providers', 'slug')->ignore($this->providerId)],
            'provider' => ['required', 'string', 'max:50'],
            'senderId' => ['nullable', 'string', 'max:50'],
            'pricePerSms' => ['required', 'numeric', 'min:0'],
            'credentialsText' => ['nullable', 'string'],
        ];
    }

    public function updatedName($value): void
    {
        if (!$this->providerId) {
            $this->slug = Str::slug($value);
        }
    }

    // --- Providers ---

    public function createProvider(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['providerId', 'name', 'slug', 'senderId', 'credentialsText']);
        $this->provider = 'manual';
        $this->pricePerSms = 0;
        $this->providerViewMode = 'create';
    }

    public function editProvider(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $p = SmsProvider::findOrFail($id);
        $this->providerId = $p->id;
        $this->name = $p->name;
        $this->slug = $p->slug;
        $this->provider = $p->provider;
        $this->senderId = $p->sender_id ?? '';
        $this->pricePerSms = (float) $p->price_per_sms;
        $this->credentialsText = collect($p->credentials ?? [])->map(fn ($v, $k) => "{$k}={$v}")->implode("\n");
        $this->providerViewMode = 'create';
    }

    public function cancelProviderForm(): void
    {
        $this->providerViewMode = 'index';
    }

    public function saveProvider(): void
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

        $p = $this->providerId ? SmsProvider::findOrFail($this->providerId) : new SmsProvider(['is_active' => false]);
        $p->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'provider' => $data['provider'],
            'sender_id' => $data['senderId'] ?: null,
            'price_per_sms' => $data['pricePerSms'],
            'credentials' => $credentials,
        ])->save();

        AuditLog::record($this->providerId ? 'sms_provider.updated' : 'sms_provider.created', $p, ['provider' => $p->provider]);

        $this->providerViewMode = 'index';
        session()->flash('message', $this->providerId ? 'SMS provider updated.' : 'SMS provider added.');
    }

    public function toggleProviderActive(int $id): void
    {
        $this->assertSuperAdmin();
        $p = SmsProvider::findOrFail($id);
        $p->update(['is_active' => !$p->is_active]);
        AuditLog::record($p->is_active ? 'sms_provider.activated' : 'sms_provider.deactivated', $p);
        session()->flash('message', 'SMS provider '.($p->is_active ? 'activated' : 'deactivated').'.');
    }

    public function archiveProvider(int $id): void
    {
        $this->assertSuperAdmin();
        $p = SmsProvider::whereNull('archived_at')->findOrFail($id);
        $p->update(['is_active' => false, 'archived_at' => now()]);
        AuditLog::record('sms_provider.archived', $p);
        session()->flash('message', 'SMS provider archived.');
    }

    public function sendTestSms(int $id): void
    {
        $this->assertSuperAdmin();
        $p = SmsProvider::findOrFail($id);

        $log = SmsLog::create([
            'sms_provider_id' => $p->id,
            'recipient' => '01700000000',
            'message' => 'BeeCore test SMS via '.$p->name,
            'status' => $p->is_active ? 'delivered' : 'failed',
            'cost' => $p->is_active ? $p->price_per_sms : 0,
            'created_at' => now(),
        ]);

        AuditLog::record('sms.test_sent', $log, ['provider_id' => $p->id, 'status' => $log->status]);
        session()->flash('message', 'Test SMS logged as '.$log->status.'.');
    }

    // --- Balances ---

    public function addCredit(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'creditTenantId' => ['required', 'exists:tenants,id'],
            'creditAmount' => ['required', 'integer', 'min:1'],
        ]);

        $balance = TenantSmsBalance::firstOrCreate(['tenant_id' => $data['creditTenantId']], ['balance' => 0]);
        $balance->increment('balance', $data['creditAmount']);

        AuditLog::record('tenant.sms_credit_added', $balance, ['amount' => $data['creditAmount']], tenantId: $data['creditTenantId']);

        $this->reset(['creditTenantId']);
        $this->creditAmount = 100;
        session()->flash('message', 'SMS credit added.');
    }

    // --- Templates ---

    public function createTemplate(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['templateId', 'templateKey', 'templateName', 'templateContent']);
        $this->tab = 'templates';
        $this->templateViewMode = 'create';
    }

    public function editTemplate(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $t = SmsTemplate::findOrFail($id);
        $this->templateId = $t->id;
        $this->templateKey = $t->key;
        $this->templateName = $t->name;
        $this->templateContent = $t->content;
        $this->tab = 'templates';
        $this->templateViewMode = 'create';
    }

    public function cancelTemplateForm(): void
    {
        $this->templateViewMode = 'index';
    }

    public function saveTemplate(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $t = $this->templateId ? SmsTemplate::findOrFail($this->templateId) : new SmsTemplate(['is_active' => true]);
        $t->fill([
            'key' => $data['templateKey'],
            'name' => $data['templateName'],
            'content' => $data['templateContent'],
        ])->save();

        AuditLog::record($this->templateId ? 'sms_template.updated' : 'sms_template.created', $t);

        $this->templateViewMode = 'index';
        session()->flash('message', $this->templateId ? 'Template updated.' : 'Template created.');
    }

    public function toggleTemplateActive(int $id): void
    {
        $this->assertSuperAdmin();
        $t = SmsTemplate::findOrFail($id);
        $t->update(['is_active' => !$t->is_active]);
        session()->flash('message', 'Template '.($t->is_active ? 'activated' : 'deactivated').'.');
    }

    public function deleteTemplate(int $id): void
    {
        $this->assertSuperAdmin();
        SmsTemplate::findOrFail($id)->delete();
        session()->flash('message', 'Template deleted.');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $logsQuery = SmsLog::query();

        return view('livewire.sms-management', [
            'providers' => SmsProvider::query()->whereNull('archived_at')->orderBy('name')->get(),
            'balances' => TenantSmsBalance::query()->with('tenant')->orderByDesc('balance')->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'logs' => SmsLog::query()->with(['tenant', 'provider'])
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->latest('id')->paginate(15),
            'templates' => SmsTemplate::query()->orderBy('name')->get(),
            'report' => [
                'sent' => (clone $logsQuery)->whereIn('status', ['sent', 'delivered'])->count(),
                'failed' => (clone $logsQuery)->where('status', 'failed')->count(),
                'cost' => (float) (clone $logsQuery)->whereIn('status', ['sent', 'delivered'])->sum('cost'),
            ],
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
