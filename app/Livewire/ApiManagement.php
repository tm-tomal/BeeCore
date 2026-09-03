<?php

namespace App\Livewire;

use App\Models\ApiClient;
use App\Models\ApiClientLog;
use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\Webhook;
use App\Models\WebhookLog;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ApiManagement extends Component
{
    use WithPagination;

    public string $tab = 'clients';

    // Client form
    public string $clientName = '';
    public ?int $clientTenantId = null;
    public int $rateLimit = 60;
    public ?string $issuedToken = null;

    // Log filters
    public string $statusFilter = '';

    // Webhook form
    public string $webhookViewMode = 'index';
    public ?int $webhookId = null;
    public string $webhookEvent = '';
    public string $webhookUrl = '';
    public string $webhookSecret = '';
    public ?int $webhookTenantId = null;

    public ?int $webhookLogsForId = null;

    public function createClient(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'clientName' => ['required', 'string', 'max:255'],
            'clientTenantId' => ['nullable', 'exists:tenants,id'],
            'rateLimit' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $plainToken = 'bee_'.Str::random(40);

        $client = ApiClient::create([
            'tenant_id' => $data['clientTenantId'] ?: null,
            'name' => $data['clientName'],
            'token_hash' => hash('sha256', $plainToken),
            'rate_limit_per_minute' => $data['rateLimit'],
            'is_active' => true,
        ]);

        AuditLog::record('api_client.created', $client, tenantId: $client->tenant_id);

        $this->issuedToken = $plainToken;
        $this->reset(['clientName', 'clientTenantId']);
        $this->rateLimit = 60;
        session()->flash('message', 'API client created. Copy the token now — it will not be shown again.');
    }

    public function dismissToken(): void
    {
        $this->issuedToken = null;
    }

    public function toggleClientActive(int $id): void
    {
        $this->assertSuperAdmin();
        $client = ApiClient::findOrFail($id);
        $client->update(['is_active' => !$client->is_active]);
        AuditLog::record($client->is_active ? 'api_client.activated' : 'api_client.revoked', $client, tenantId: $client->tenant_id);
        session()->flash('message', 'API client '.($client->is_active ? 'activated' : 'revoked').'.');
    }

    public function deleteClient(int $id): void
    {
        $this->assertSuperAdmin();
        $client = ApiClient::findOrFail($id);
        $client->delete();
        AuditLog::record('api_client.deleted', null, ['name' => $client->name], tenantId: $client->tenant_id);
        session()->flash('message', 'API client deleted.');
    }

    public function simulateRequest(int $id, bool $failed = false): void
    {
        $this->assertSuperAdmin();
        $client = ApiClient::findOrFail($id);

        ApiClientLog::create([
            'api_client_id' => $client->id,
            'endpoint' => '/api/v1/ping',
            'method' => 'GET',
            'status_code' => $failed ? 500 : 200,
            'is_failed' => $failed,
            'created_at' => now(),
        ]);

        $client->update(['last_used_at' => now()]);
        session()->flash('message', 'Test request logged.');
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    // --- Webhooks ---

    public function createWebhook(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['webhookId', 'webhookEvent', 'webhookUrl', 'webhookSecret', 'webhookTenantId']);
        $this->tab = 'webhooks';
        $this->webhookViewMode = 'create';
    }

    public function editWebhook(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $w = Webhook::findOrFail($id);
        $this->webhookId = $w->id;
        $this->webhookEvent = $w->event;
        $this->webhookUrl = $w->url;
        $this->webhookSecret = '';
        $this->webhookTenantId = $w->tenant_id;
        $this->tab = 'webhooks';
        $this->webhookViewMode = 'create';
    }

    public function cancelWebhookForm(): void
    {
        $this->webhookViewMode = 'index';
    }

    public function saveWebhook(): void
    {
        $this->assertSuperAdmin();

        $data = $this->validate([
            'webhookEvent' => ['required', 'string', 'max:255'],
            'webhookUrl' => ['required', 'url', 'max:255'],
            'webhookSecret' => ['nullable', 'string', 'max:255'],
            'webhookTenantId' => ['nullable', 'exists:tenants,id'],
        ]);

        $w = $this->webhookId ? Webhook::findOrFail($this->webhookId) : new Webhook(['is_active' => true]);
        $attributes = [
            'event' => $data['webhookEvent'],
            'url' => $data['webhookUrl'],
            'tenant_id' => $data['webhookTenantId'] ?: null,
        ];
        if (filled($data['webhookSecret'])) {
            $attributes['secret'] = $data['webhookSecret'];
        }
        $w->fill($attributes)->save();

        AuditLog::record($this->webhookId ? 'webhook.updated' : 'webhook.created', $w, ['event' => $w->event], tenantId: $w->tenant_id);

        $this->webhookViewMode = 'index';
        session()->flash('message', $this->webhookId ? 'Webhook updated.' : 'Webhook created.');
    }

    public function toggleWebhookActive(int $id): void
    {
        $this->assertSuperAdmin();
        $w = Webhook::findOrFail($id);
        $w->update(['is_active' => !$w->is_active]);
        session()->flash('message', 'Webhook '.($w->is_active ? 'activated' : 'deactivated').'.');
    }

    public function triggerTestWebhook(int $id): void
    {
        $this->assertSuperAdmin();
        $w = Webhook::findOrFail($id);

        $success = $w->is_active;
        WebhookLog::create([
            'webhook_id' => $w->id,
            'status_code' => $success ? 200 : 0,
            'success' => $success,
            'response_body' => $success ? 'OK' : 'Webhook is inactive',
            'created_at' => now(),
        ]);
        $w->update(['last_triggered_at' => now()]);

        AuditLog::record('webhook.test_triggered', $w, ['success' => $success], tenantId: $w->tenant_id);
        session()->flash('message', 'Test webhook '.($success ? 'succeeded' : 'failed').'.');
    }

    public function viewWebhookLogs(int $id): void
    {
        $this->webhookLogsForId = $id;
    }

    public function closeWebhookLogs(): void
    {
        $this->webhookLogsForId = null;
    }

    public function deleteWebhook(int $id): void
    {
        $this->assertSuperAdmin();
        $w = Webhook::findOrFail($id);
        $w->delete();
        AuditLog::record('webhook.deleted', null, ['event' => $w->event], tenantId: $w->tenant_id);
        session()->flash('message', 'Webhook deleted.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        return view('livewire.api-management', [
            'clients' => ApiClient::query()->with('tenant')->withCount(['logs as failed_count' => fn ($q) => $q->where('is_failed', true)])->latest()->get(),
            'tenants' => Tenant::query()->whereNull('archived_at')->orderBy('name')->get(),
            'logs' => ApiClientLog::query()->with('client')
                ->when($this->statusFilter === 'failed', fn ($q) => $q->where('is_failed', true))
                ->when($this->statusFilter === 'success', fn ($q) => $q->where('is_failed', false))
                ->latest('id')->paginate(15),
            'webhooks' => Webhook::query()->with('tenant')->latest()->get(),
            'webhookLogs' => $this->webhookLogsForId
                ? WebhookLog::where('webhook_id', $this->webhookLogsForId)->latest('id')->limit(50)->get()
                : collect(),
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
