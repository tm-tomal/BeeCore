<?php

namespace Tests\Feature;

use App\Livewire\IspIssues;
use App\Livewire\PublicIssueReport;
use App\Models\Attachment;
use App\Models\Customer;
use App\Models\Issue;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        Livewire::test(PublicIssueReport::class, ['tenant' => $tenant])
            ->set('name', 'Rahim')
            ->set('phone', '01712345678')
            ->set('category', 'connection')
            ->set('subject', 'Internet is down')
            ->set('description', 'Since this morning')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('issues.public.report', ['tenant' => $tenant->slug]));

        $issue = Issue::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertSame('public', $issue->source);
        $this->assertSame($customer->id, $issue->customer_id);
        $this->assertSame('new', $issue->status);
    }

    public function test_public_report_can_attach_a_photo_of_the_problem(): void
    {
        Storage::fake('local');
        $tenant = $this->tenant('iss-g', 'Delta Net');

        Livewire::test(PublicIssueReport::class, ['tenant' => $tenant])
            ->set('name', 'Karim')
            ->set('phone', '01811111111')
            ->set('category', 'network')
            ->set('subject', 'Broken cable on the pole')
            ->set('files', [UploadedFile::fake()->image('cable.jpg', 120, 80)])
            ->call('save')
            ->assertHasNoErrors();

        $issue = Issue::where('tenant_id', $tenant->id)->firstOrFail();
        $this->assertDatabaseHas('attachments', [
            'tenant_id' => $tenant->id,
            'attachable_type' => Issue::class,
            'attachable_id' => $issue->id,
            'original_name' => 'cable.jpg',
        ]);
        $this->assertSame(1, $issue->attachments()->count());
    }

    public function test_public_report_rejects_non_media_files(): void
    {
        Storage::fake('local');
        $tenant = $this->tenant('iss-h', 'Epsilon Net');

        Livewire::test(PublicIssueReport::class, ['tenant' => $tenant])
            ->set('name', 'Sami')
            ->set('phone', '01911111111')
            ->set('subject', 'Weird attachment')
            ->set('files', [UploadedFile::fake()->create('note.txt', 20)])
            ->call('save')
            ->assertHasErrors('files.0');

        $this->assertDatabaseCount('issues', 0);
        $this->assertDatabaseCount('attachments', 0);
    }

    public function test_staff_can_open_an_issue_on_a_full_page_detail_view(): void
    {
        $tenant = $this->tenant('iss-k', 'Theta Net');
        $staff = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_SUPPORT]);
        $issue = Issue::create([
            'tenant_id' => $tenant->id, 'created_by' => $staff->id, 'reporter_name' => 'Rafi',
            'subject' => 'Cable down near the lake', 'category' => 'connection', 'priority' => 'high',
            'status' => 'new', 'source' => 'staff', 'description' => 'Since 9 AM.',
        ]);

        Livewire::actingAs($staff)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->assertSet('viewMode', 'detail')
            ->assertSet('detailIssueId', $issue->id)
            ->assertSee('Cable down near the lake')
            ->assertSee('Report info')
            ->call('updateStatus', $issue->id, 'in_progress')
            ->assertHasNoErrors()
            ->call('closeDetail')
            ->assertSet('viewMode', 'index')
            ->assertSet('detailIssueId', null);
    }

    public function test_issue_attachment_can_only_be_streamed_by_the_owning_tenant(): void
    {
        Storage::fake('local');
        $owner = $this->tenant('iss-i', 'Zeta Net');
        $stranger = $this->tenant('iss-j', 'Eta Net');
        $ownerStaff = User::factory()->create(['tenant_id' => $owner->id, 'role' => User::ROLE_SUPPORT]);
        $strangerStaff = User::factory()->create(['tenant_id' => $stranger->id, 'role' => User::ROLE_SUPPORT]);

        $issue = Issue::create([
            'tenant_id' => $owner->id, 'reporter_name' => 'Farid', 'subject' => 'Router photo',
            'category' => 'connection', 'status' => 'new', 'source' => 'public',
        ]);
        Storage::disk('local')->put('attachments/'.$owner->id.'/issues/x.jpg', 'fake-jpeg');
        $attachment = Attachment::create([
            'tenant_id' => $owner->id, 'attachable_type' => Issue::class, 'attachable_id' => $issue->id,
            'disk' => 'local', 'path' => 'attachments/'.$owner->id.'/issues/x.jpg',
            'original_name' => 'x.jpg', 'mime_type' => 'image/jpeg', 'size' => 9,
        ]);

        $this->get(route('attachments.show', $attachment))->assertRedirect(route('login'));
        $this->actingAs($ownerStaff)->get(route('attachments.show', $attachment))->assertOk();
        $this->actingAs($strangerStaff)->get(route('attachments.show', $attachment))->assertForbidden();
    }

    public function test_public_report_requires_active_tenant(): void
    {
        $tenant = $this->tenant('iss-f', 'Hidden Net');
        $tenant->update(['status' => 'suspended']);

        $this->get('/r/'.$tenant->slug.'/report')->assertNotFound();
    }

    public function test_admin_can_assign_issue_to_a_member_who_can_then_reply(): void
    {
        $tenant = $this->tenant('iss-l', 'Iota Net');
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $engineer = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_NETWORK_ENGINEER]);
        $issue = Issue::create([
            'tenant_id' => $tenant->id, 'created_by' => $admin->id, 'reporter_name' => 'Rafi',
            'subject' => 'Weak signal on floor 3', 'category' => 'network',
            'status' => 'new', 'source' => 'public',
        ]);

        Livewire::actingAs($admin)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->assertSee('Assigned to fix')
            ->assertSet('assignToUserId', null)
            ->set('assignToUserId', $engineer->id)
            ->call('saveAssignment')
            ->assertHasNoErrors();

        $issue->refresh();
        $this->assertSame($engineer->id, $issue->assigned_to);

        Livewire::actingAs($engineer)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->assertSet('assignToUserId', $engineer->id)
            ->assertSee($engineer->name)
            ->set('replyMessage', 'Replaced the access point, testing now.')
            ->call('reply')
            ->assertHasNoErrors()
            ->assertSet('replyMessage', '');

        $this->assertDatabaseHas('issue_replies', [
            'issue_id' => $issue->id,
            'user_id' => $engineer->id,
            'message' => 'Replaced the access point, testing now.',
        ]);

        $issue->refresh();
        $this->assertSame('in_progress', $issue->status);
    }

    public function test_unassigned_member_cannot_reply_or_assign(): void
    {
        $tenant = $this->tenant('iss-m', 'Kappa Net');
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $engineer = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_NETWORK_ENGINEER]);
        $stranger = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_SUPPORT]);
        $issue = Issue::create([
            'tenant_id' => $tenant->id, 'created_by' => $admin->id, 'reporter_name' => 'Sami',
            'subject' => 'Billing question', 'category' => 'billing',
            'status' => 'new', 'source' => 'public',
        ]);

        Livewire::actingAs($stranger)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->assertDontSee('Reassign')
            ->set('assignToUserId', $engineer->id)
            ->call('saveAssignment')
            ->assertStatus(403);

        Livewire::actingAs($stranger)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->set('replyMessage', 'Not my issue but let me reply')
            ->call('reply')
            ->assertStatus(403);

        $issue->refresh();
        $this->assertNull($issue->assigned_to);
        $this->assertDatabaseCount('issue_replies', 0);
    }

    public function test_admin_can_unassign_and_reply_directly(): void
    {
        $tenant = $this->tenant('iss-n', 'Lambda Net');
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_TENANT_ADMIN]);
        $engineer = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_NETWORK_ENGINEER]);
        $issue = Issue::create([
            'tenant_id' => $tenant->id, 'created_by' => $admin->id, 'assigned_to' => $engineer->id,
            'reporter_name' => 'Jamil', 'subject' => 'Router keeps rebooting', 'category' => 'connection',
            'status' => 'new', 'source' => 'staff',
        ]);

        Livewire::actingAs($admin)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->set('replyMessage', 'We are checking with the supplier.')
            ->call('reply')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('issue_replies', [
            'issue_id' => $issue->id,
            'user_id' => $admin->id,
            'message' => 'We are checking with the supplier.',
        ]);

        Livewire::actingAs($admin)
            ->test(IspIssues::class)
            ->call('viewIssue', $issue->id)
            ->call('unassign')
            ->assertHasNoErrors();

        $issue->refresh();
        $this->assertNull($issue->assigned_to);
    }
}
