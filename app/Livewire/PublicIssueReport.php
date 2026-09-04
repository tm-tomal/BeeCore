<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Issue;
use App\Models\Tenant;
use App\Support\FileAttachments;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Public problem report page for an ISP's own customers (no login needed).
 * A reporter can attach photos/videos of the issue; upload starts as soon as
 * the file is picked, so large clips upload while the rest of the form is
 * still being filled in.
 */
class PublicIssueReport extends Component
{
    use WithFileUploads;

    public int $tenantId = 0;

    public string $name = '';

    public string $phone = '';

    public string $category = Issue::CATEGORY_CONNECTION;

    public string $subject = '';

    public string $description = '';

    /** @var array<int, mixed> */
    public array $files = [];

    public function mount(Tenant $tenant): void
    {
        abort_unless($tenant->status === 'active' && ! $tenant->archived_at, 404);

        $this->tenantId = (int) $tenant->id;
    }

    public function removeFile(int $index): void
    {
        unset($this->files[$index]);
        $this->files = array_values($this->files);
    }

    public function save()
    {
        $data = $this->validate(array_merge([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'category' => ['required', Rule::in([Issue::CATEGORY_CONNECTION, Issue::CATEGORY_NETWORK, Issue::CATEGORY_SERVICE, Issue::CATEGORY_BILLING, Issue::CATEGORY_OTHER])],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], FileAttachments::uploadRules()));

        $tenant = Tenant::query()->findOrFail($this->tenantId);

        // Link the report to an existing subscriber when the phone matches exactly.
        $cleanPhone = preg_replace('/\D+/', '', $data['phone']);
        $customer = $cleanPhone
            ? Customer::query()
                ->where('tenant_id', $tenant->id)
                ->get()
                ->first(fn ($c) => preg_replace('/\D+/', '', (string) $c->phone) === $cleanPhone)
            : null;

        $issue = DB::transaction(function () use ($tenant, $customer, $data) {
            return Issue::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer?->id,
                'reporter_name' => $data['name'],
                'reporter_phone' => $data['phone'],
                'subject' => $data['subject'],
                'category' => $data['category'],
                'priority' => 'medium',
                'status' => Issue::STATUS_NEW,
                'source' => 'public',
                'description' => $data['description'] ?: null,
            ]);
        });

        if (! empty($this->files)) {
            FileAttachments::attach($issue, array_values($this->files), (int) $tenant->id);
        }

        return redirect()->route('issues.public.report', ['tenant' => $tenant->slug])
            ->with('status', __('Report sent — the ISP will look into it.'));
    }

    public function render()
    {
        return view('livewire.public-issue-report', [
            'tenant' => Tenant::query()->find($this->tenantId),
        ]);
    }
}
