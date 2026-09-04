<?php

namespace Tests\Feature;

use App\Livewire\IspSupport;
use App\Livewire\SupportTickets;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class IspSupportConsoleTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(string $slug, string $name): Tenant
    {
        return Tenant::create(['name' => $name, 'slug' => $slug, 'status' => 'active', 'currency' => 'BDT', 'timezone' => 'UTC']);
    }

    public function test_tenant_admin_can_open_isp_support_page(): void
    {
        $tenant = $this->tenant('spa', 'Alpha ISP');
        $user = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $this->actingAs($user)->get('/support')->assertOk()->assertSee('Support');
    }

    public function test_tenant_can_create_ticket_and_admin_replies_back_and_forth(): void
    {
        $tenant = $this->tenant('spb', 'Beta ISP');
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $super = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);

        Livewire::actingAs($admin)
            ->test(IspSupport::class)
            ->call('createForm')
            ->set('subject', 'Billing cycle is wrong')
            ->set('category', 'billing')
            ->set('description', 'Invoices are generated on the wrong day.')
            ->call('save')
            ->assertHasNoErrors();

        $ticket = SupportTicket::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('open', $ticket->status);
        $this->assertSame($admin->id, $ticket->created_by);

        // BeeCore admin replies from the platform console.
        Livewire::actingAs($super)
            ->test(SupportTickets::class)
            ->assertSee('Billing cycle is wrong')
            ->call('viewDetail', $ticket->id)
            ->set('replyMessage', 'Thanks, we fixed the cycle for you.')
            ->call('reply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $super->id,
            'message' => 'Thanks, we fixed the cycle for you.',
        ]);

        // The ISP sees the admin reply inside their own detail view.
        Livewire::actingAs($admin)
            ->test(IspSupport::class)
            ->call('viewDetail', $ticket->id)
            ->assertSee('Thanks, we fixed the cycle for you.')
            ->set('replyMessage', 'Confirmed — all good now.')
            ->call('reply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'message' => 'Confirmed — all good now.',
        ]);
    }

    public function test_tenant_only_sees_its_own_tickets(): void
    {
        $alpha = $this->tenant('spc', 'Alpha ISP');
        $beta = $this->tenant('spd', 'Beta ISP');
        $alphaAdmin = User::factory()->create(['tenant_id' => $alpha->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $betaAdmin = User::factory()->create(['tenant_id' => $beta->id, 'role' => User::ROLE_TENANT_ADMIN]);

        SupportTicket::create([
            'tenant_id' => $alpha->id, 'subject' => 'ALPHA-SECRET-ISSUE', 'description' => 'desc',
            'category' => 'technical', 'priority' => 'medium', 'status' => 'open', 'created_by' => $alphaAdmin->id,
        ]);

        Livewire::actingAs($betaAdmin)
            ->test(IspSupport::class)
            ->assertDontSee('ALPHA-SECRET-ISSUE');
    }

    public function test_finance_role_cannot_open_tenant_support_page(): void
    {
        $tenant = $this->tenant('spe', 'Gamma ISP');
        $finance = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_FINANCE]);

        $this->actingAs($finance)->get('/support')->assertForbidden();
    }

    public function test_isp_marking_ticket_resolved_keeps_it_out_of_open_count(): void
    {
        $tenant = $this->tenant('spf', 'Delta ISP');
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        $ticket = SupportTicket::create([
            'tenant_id' => $tenant->id, 'subject' => 'Resolve me', 'description' => 'desc',
            'category' => 'other', 'priority' => 'low', 'status' => 'open', 'created_by' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(IspSupport::class)
            ->call('updateStatus', $ticket->id, 'resolved')
            ->assertHasNoErrors();

        $this->assertSame('resolved', $ticket->fresh()->status);
        $this->assertNotNull($ticket->fresh()->resolved_at);
    }

    public function test_isp_can_attach_photos_to_a_ticket_and_to_a_reply(): void
    {
        Storage::fake('local');
        $tenant = $this->tenant('spg', 'Epsilon ISP');
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);

        Livewire::actingAs($admin)
            ->test(IspSupport::class)
            ->call('createForm')
            ->set('subject', 'Dashboard looks broken')
            ->set('category', 'technical')
            ->set('description', 'Screenshot attached below.')
            ->set('files', [UploadedFile::fake()->image('dashboard.png', 160, 90)])
            ->call('save')
            ->assertHasNoErrors();

        $ticket = SupportTicket::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertDatabaseHas('attachments', [
            'tenant_id' => $tenant->id,
            'attachable_type' => SupportTicket::class,
            'attachable_id' => $ticket->id,
            'original_name' => 'dashboard.png',
        ]);

        Livewire::actingAs($admin)
            ->test(IspSupport::class)
            ->call('viewDetail', $ticket->id)
            ->set('replyMessage', 'Also sending the video clip')
            ->set('files', [UploadedFile::fake()->image('clip.png', 60, 40)])
            ->call('reply')
            ->assertHasNoErrors();

        $reply = SupportTicketReply::where('support_ticket_id', $ticket->id)->firstOrFail();
        $this->assertSame($admin->id, $reply->user_id);
        $this->assertDatabaseHas('attachments', [
            'attachable_type' => SupportTicketReply::class,
            'attachable_id' => $reply->id,
            'original_name' => 'clip.png',
        ]);
    }
}
