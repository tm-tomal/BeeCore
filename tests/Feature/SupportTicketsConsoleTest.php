<?php

namespace Tests\Feature;

use App\Livewire\SupportTickets;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SupportTicketsConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Support ISP', 'slug' => 'support-isp-'.uniqid(), 'status' => 'active',
            'currency' => 'BDT', 'timezone' => 'Asia/Dhaka',
        ]);
    }

    public function test_super_admin_can_create_a_ticket_with_sla(): void
    {
        $admin = User::factory()->create();
        $tenant = $this->tenant();

        Livewire::actingAs($admin)->test(SupportTickets::class)
            ->call('create')
            ->set('tenantId', $tenant->id)
            ->set('subject', 'Router keeps rebooting')
            ->set('description', 'Customer reports the router restarts every hour.')
            ->set('category', 'technical')
            ->set('priority', 'high')
            ->set('slaHours', 24)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_tickets', ['tenant_id' => $tenant->id, 'subject' => 'Router keeps rebooting', 'status' => 'open']);
        $ticket = SupportTicket::where('subject', 'Router keeps rebooting')->firstOrFail();
        $this->assertNotNull($ticket->sla_due_at);
    }

    public function test_assigning_an_agent_moves_an_open_ticket_to_in_progress(): void
    {
        $admin = User::factory()->create();
        $agent = User::factory()->create(['role' => User::ROLE_SUPPORT, 'tenant_id' => null]);
        $ticket = SupportTicket::create(['subject' => 'Billing question', 'description' => 'x', 'category' => 'billing', 'priority' => 'low', 'status' => 'open']);

        Livewire::actingAs($admin)->test(SupportTickets::class)
            ->call('assign', $ticket->id, $agent->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_tickets', ['id' => $ticket->id, 'assigned_to' => $agent->id, 'status' => 'in_progress']);
    }

    public function test_replying_sets_first_responded_at_and_resolving_sets_resolved_at(): void
    {
        $admin = User::factory()->create();
        $ticket = SupportTicket::create(['subject' => 'Slow connection', 'description' => 'x', 'category' => 'network', 'priority' => 'medium', 'status' => 'open']);

        Livewire::actingAs($admin)->test(SupportTickets::class)
            ->call('viewDetail', $ticket->id)
            ->set('replyMessage', 'Looking into this now.')
            ->call('reply')
            ->assertHasNoErrors();

        $ticket->refresh();
        $this->assertNotNull($ticket->first_responded_at);
        $this->assertDatabaseHas('support_ticket_replies', ['support_ticket_id' => $ticket->id, 'message' => 'Looking into this now.']);

        Livewire::actingAs($admin)->test(SupportTickets::class)
            ->call('updateStatus', $ticket->id, 'resolved')
            ->assertHasNoErrors();

        $ticket->refresh();
        $this->assertSame('resolved', $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
    }
}
