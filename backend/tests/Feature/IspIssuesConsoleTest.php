<?php

namespace Tests\Feature;

use App\Livewire\IspIssues;
use App\Models\Customer;
use App\Models\Issue;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IspIssuesConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $slug, string $name): Tenant
    {
        return Tenant::create(['name' => $name, 'slug' => $slug, 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
    }

    public function test_staff_can_create_and_resolve_issue(): void
    {
        $tenant = $this->tenant('iss-a', 'Alpha Net');
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_SUPPORT]);

        Livewire::actingAs($user)
            ->test(IspIssues::class)
            ->assertSee('Issues')
            ->call('createForm')
            ->set('subject', 'No internet in Banani')
            ->set('category', 'connection')
            ->set('reporterName', 'Rahim')
            ->set('reporterPhone', '01700000000')
            ->call('save')
            ->assertHasNoErrors();

        $issue = Issue::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('staff', $issue->source);
        $this->assertSame($user->id, $issue->created_by);
        $this->assertSame('new', $issue->status);

        Livewire::actingAs($user)
            ->test(IspIssues::class)
            ->call('updateStatus', $issue->id, 'resolved')
            ->assertHasNoErrors();

        $issue->refresh();
        $this->assertSame('resolved', $issue->status);
        $this->assertNotNull($issue->resolved_at);
    }

    public function test_network_engineer_can_open_issues_page(): void
    {
        $tenant = $this->tenant('iss-b', 'Beta Net');
        $engineer = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_NETWORK_ENGINEER]);

        $this->actingAs($engineer)->get('/issues')->assertOk()->assertSee('Issues');
    }

    public function test_tenant_only_sees_its_own_issues(): void
    {
        $alpha = $this->tenant('iss-c', 'Alpha Net');
        $beta = $this->tenant('iss-d', 'Beta Net');
        $alphaAdmin = User::factory()->create(['tenant_id' => $alpha->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $betaAdmin = User::factory()->create(['tenant_id' => $beta->id, 'role' => User::ROLE_TENANT_ADMIN]);

        Issue::create([
            'tenant_id' => $alpha->id, 'created_by' => $alphaAdmin->id, 'reporter_name' => 'Alpha',
            'subject' => 'ALPHA-SECRET-ISSUE', 'category' => 'network', 'status' => 'new', 'source' => 'staff',
        ]);

        Livewire::actingAs($betaAdmin)
            ->test(IspIssues::class)
            ->assertDontSee('ALPHA-SECRET-ISSUE');
    }

    public function test_public_customer_report_page_creates_issue_linked_to_subscriber(): void
    {
        $tenant = $this->tenant('iss-e', 'Gamma Net');
        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Rahim', 'phone' => '01712345678', 'status' => 'active']);

        $this->get('/r/'.$tenant->slug.'/report')->assertOk()->assertSee('Send report');

        $this->post('/r/'.$tenant->slug.'/report', [
            'name' => 'Rahim',
            'phone' => '01712345678',
            'category' => 'connection',
            'subject' => 'Internet is down',
            'description' => 'Since this morning',
        ])->assertRedirect()->assertSessionHas('status');

        $issue = Issue::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('public', $issue->source);
        $this->assertSame($customer->id, $issue->customer_id);
        $this->assertSame('new', $issue->status);
    }

    public function test_public_report_requires_active_tenant(): void
    {
        $tenant = $this->tenant('iss-f', 'Hidden Net');
        $tenant->update(['status' => 'suspended']);

        $this->get('/r/'.$tenant->slug.'/report')->assertNotFound();
    }
}
