<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\EmailLog;
use App\Models\EmailProvider;
use App\Models\EmailTemplate;
use App\Models\Tenant;
use App\Models\TenantEmailQuota;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class EmailManagement extends Component
{
    use WithPagination;

    public string $tab = 'providers';

    // Provider form
    public string $providerViewMode = 'index';
    public ?int $providerId = null;
    public string $name = '';
    public string $slug = '';
    public string $type = 'smtp';
    public string $provider = 'smtp';
    public string $fromAddress = '';
    public string $fromName = '';
    public string $credentialsText = '';

    // Quota form
    public ?int $quotaTenantId = null;
    public int $quotaAmount = 1000;

    // Log filters
    public string $statusFilter = '';
    public string $categoryFilter = '';

    // Template form
    public string $templateViewMode = 'index';
    public ?int $templateId = null;
    public string $templateKey = '';
    public string $templateName = '';
    public string $templateSubject = '';
    public string $templateBody = '';

    protected function rules(): array
    {
        if ($this->tab === 'templates') {
            return [
                'templateKey' => ['required', 'string', 'max:255', Rule::unique('email_templates', 'key')->ignore($this->templateId)],
                'templateName' => ['required', 'string', 'max:255'],
                'templateSubject' => ['required', 'string', 'max:255'],
                'templateBody' => ['required', 'string', 'max:5000'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('email_providers', 'slug')->ignore($this->providerId)],
            'type' => ['required', Rule::in(['smtp', 'api'])],
            'provider' => ['required', 'string', 'max:50'],
            'fromAddress' => ['nullable', 'email', 'max:255'],
            'fromName' => ['nullable', 'string', 'max:255'],
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
        $this->reset(['providerId', 'name', 'slug', 'fromAddress', 'fromName', 'credentialsText']);
        $this->tab = 'providers';
        $this->type = 'smtp';
        $this->provider = 'smtp';
        $this->providerViewMode = 'create';
    }

    public function editProvider(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $p = EmailProvider::findOrFail($id);
        $this->providerId = $p->id;
        $this->name = $p->name;
        $this->slug = $p->slug;
        $this->type = $p->type;
        $this->provider = $p->provider;
        $this->fromAddress = $p->from_address ?? '';
        $this->fromName = $p->from_name ?? '';
        $this->credentialsText = collect($p->credentials ?? [])->map(fn ($v, $k) => "{$k}={$v}")->implode("\n");
        $this->tab = 'providers';
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

        $p = $this->providerId ? EmailProvider::findOrFail($this->providerId) : new EmailProvider(['is_active' => false]);
        $p->fill([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'type' => $data['type'],
            'provider' => $data['provider'],
            'from_address' => $data['fromAddress'] ?: null,
            'from_name' => $data['fromName'] ?: null,
            'credentials' => $credentials,
        ])->save();

        AuditLog::record($this->providerId ? 'email_provider.updated' : 'email_provider.created', $p, ['provider' => $p->provider]);

        $this->providerViewMode = 'index';
        session()->flash('message', $this->providerId ? 'Email provider updated.' : 'Email provider added.');
    }

    public function toggleProviderActive(int $id): void
    {
        $this->assertSuperAdmin();
        $p = EmailProvider::findOrFail($id);
        $p->update(['is_active' => !$p->is_active]);
        AuditLog::record($p->is_active ? 'email_provider.activated' : 'email_provider.deactivated', $p);
        session()->flash('message', 'Email provider '.($p->is_active ? 'activated' : 'deactivated').'.');
    }

    public function archiveProvider(int $id): void
    {
        $this->assertSuperAdmin();
        $p = EmailProvider::whereNull('archived_at')->findOrFail($id);
        $p->update(['is_active' => false, 'archived_at' => now()]);
        AuditLog::record('email_provider.archived', $p);
        session()->flash('message', 'Email provider archived.');
    }

    public function sendTestEmail(int $id): void
    {
        $this->assertSuperAdmin();
        $p = EmailProvider::findOrFail($id);

        $log = EmailLog::create([
            'email_provider_id' => $p->id,
            'recipient' => 'test@beecore.test',
            'subject' => 'BeeCore test email via '.$p->name,
            'category' => 'transactional',
            'status' => $p->is_active ? 'delivered' : 'failed',
            'created_at' => now(),
        ]);

        AuditLog::record('email.test_sent', $log, ['provider_id' => $p->id, 'status' => $log->status]);
        session()->flash('message', 'Test email logged as '.$log->status.'.');
    }

    // --- Quotas ---

    public function setQuota(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate([
            'quotaTenantId' => ['required', 'exists:tenants,id'],
            'quotaAmount' => ['required', 'integer', 'min:0'],
        ]);

        $quota = TenantEmailQuota::firstOrCreate(['tenant_id' => $data['quotaTenantId']], ['used_this_month' => 0]);
        $quota->update(['monthly_quota' => $data['quotaAmount']]);

        AuditLog::record('tenant.email_quota_updated', $quota, ['monthly_quota' => $data['quotaAmount']], tenantId: $data['quotaTenantId']);

        $this->reset(['quotaTenantId']);
        $this->quotaAmount = 1000;
        session()->flash('message', 'Email quota updated.');
    }

    // --- Templates ---

    public function createTemplate(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['templateId', 'templateKey', 'templateName', 'templateSubject', 'templateBody']);
        $this->tab = 'templates';
        $this->templateViewMode = 'create';
    }

    public function editTemplate(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $t = EmailTemplate::findOrFail($id);
        $this->templateId = $t->id;
        $this->templateKey = $t->key;
        $this->templateName = $t->name;
        $this->templateSubject = $t->subject;
        $this->templateBody = $t->body;
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

        $t = $this->templateId ? EmailTemplate::findOrFail($this->templateId) : new EmailTemplate(['is_active' => true]);
        $t->fill([
            'key' => $data['templateKey'],
            'name' => $data['templateName'],
            'subject' => $data['templateSubject'],
            'body' => $data['templateBody'],
        ])->save();

        AuditLog::record($this->templateId ? 'email_template.updated' : 'email_template.created', $t);

        $this->templateViewMode = 'index';
        session()->flash('message', $this->templateId ? 'Template updated.' : 'Template created.');
    }

    public function toggleTemplateActive(int $id): void
    {
        $this->assertSuperAdmin();
        $t = EmailTemplate::findOrFail($id);
        $t->update(['is_active' => !$t->is_active]);
        session()->flash('message', 'Template '.($t->is_active ? 'activated' : 'deactivated').'.');
    }

    public function deleteTemplate(int $id): void
    {
        $this->assertSuperAdmin();
        EmailTemplate::findOrFail($id)->delete();
        session()->flash('message', 'Template deleted.');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $logsQuery = EmailLog::query();

        return view('livewire.email-management', [
            'providers' => EmailProvider::query()->whereNull('archived_at')->orderBy('name')->get(),
            'quotas' => TenantEmailQuota::query()->with('tenant')->orderByDesc('monthly_quota')->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'logs' => EmailLog::query()->with(['tenant', 'provider'])
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->categoryFilter, fn ($q) => $q->where('category', $this->categoryFilter))
                ->latest('id')->paginate(15),
            'templates' => EmailTemplate::query()->orderBy('name')->get(),
            'report' => [
                'sent' => (clone $logsQuery)->whereIn('status', ['sent', 'delivered'])->count(),
                'failed' => (clone $logsQuery)->where('status', 'failed')->count(),
                'bulk' => (clone $logsQuery)->where('category', 'bulk')->count(),
            ],
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
