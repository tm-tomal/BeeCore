<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\TenantSmsBalance;
use App\Models\User;
use App\Services\SmsGateway;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CustomerProfile extends Component
{
    use AuthorizesRoles;

    public int $customerId;

    public string $composeChannel = 'sms';
    public string $composeSubject = '';
    public string $composeMessage = '';

    public function boot(): void
    {
        \App\Support\TenantPermissions::assert('customers');
    }

    public function mount(int $customer): void
    {
        $this->customerId = $customer;
    }

    private function tenantId(): int
    {
        return app(CurrentTenant::class)->id();
    }

    public function customer(): Customer
    {
        return Customer::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($this->customerId);
    }

    public function toggleSms(): void
    {
        $customer = $this->customer();
        $customer->update(['notify_sms' => ! $customer->notify_sms]);
        AuditLog::record('customer.notification_pref', $customer, ['channel' => 'sms', 'enabled' => $customer->notify_sms], tenantId: $customer->tenant_id);
        session()->flash('message', $customer->notify_sms ? __('SMS notifications are now ON for this customer.') : __('SMS notifications are now OFF for this customer.'));
    }

    public function toggleEmail(): void
    {
        $customer = $this->customer();
        $customer->update(['notify_email' => ! $customer->notify_email]);
        AuditLog::record('customer.notification_pref', $customer, ['channel' => 'email', 'enabled' => $customer->notify_email], tenantId: $customer->tenant_id);
        session()->flash('message', $customer->notify_email ? __('Email notifications are now ON for this customer.') : __('Email notifications are now OFF for this customer.'));
    }

    public function selectChannel(string $channel): void
    {
        $this->composeChannel = in_array($channel, ['sms', 'email'], true) ? $channel : 'sms';
        $this->resetValidation('composeSubject', 'composeMessage');
    }

    public function sendMessage(): void
    {
        $customer = $this->customer();

        if ($this->composeChannel === 'sms') {
            $this->sendSms($customer);

            return;
        }

        $this->sendEmail($customer);
    }

    private function sendSms(Customer $customer): void
    {
        $this->validate([
            'composeMessage' => ['required', 'string', 'max:918'],
        ]);

        if (! $customer->phone) {
            session()->flash('error', __('This customer has no mobile number — add one before sending an SMS.'));

            return;
        }

        if (! $customer->notify_sms) {
            session()->flash('error', __('SMS notifications are switched off for this customer. Turn them on to send an SMS.'));

            return;
        }

        $tenant = Tenant::query()->find($customer->tenant_id);
        $result = SmsGateway::sendForTenant($tenant, $customer->phone, $this->composeMessage);

        if (! $result['ok']) {
            session()->flash('error', __('SMS not sent: :reason', ['reason' => $result['message']]));

            return;
        }

        AuditLog::record('customer.message.sms', $customer, ['recipient' => $customer->phone, 'log_id' => $result['log_id'] ?? null], tenantId: $customer->tenant_id);
        $this->composeMessage = '';
        session()->flash('message', __('SMS sent to :name.', ['name' => $customer->name]));
    }

    private function sendEmail(Customer $customer): void
    {
        $this->validate([
            'composeSubject' => ['required', 'string', 'max:190'],
            'composeMessage' => ['required', 'string', 'max:5000'],
        ]);

        if (! $customer->email) {
            session()->flash('error', __('This customer has no email address — add one before sending an email.'));

            return;
        }

        if (! $customer->notify_email) {
            session()->flash('error', __('Email notifications are switched off for this customer. Turn them on to send an email.'));

            return;
        }

        $subject = $this->composeSubject;
        $body = $this->composeMessage;

        try {
            Mail::raw($body, function ($message) use ($customer, $subject) {
                $message->to($customer->email, $customer->name)->subject($subject);
            });
        } catch (\Throwable $e) {
            session()->flash('error', __('Email not sent: :reason', ['reason' => $e->getMessage()]));

            return;
        }

        AuditLog::record('customer.message.email', $customer, ['recipient' => $customer->email, 'subject' => $subject], tenantId: $customer->tenant_id);
        $this->composeSubject = '';
        $this->composeMessage = '';
        session()->flash('message', __('Email sent to :name.', ['name' => $customer->name]));
    }

    public function render()
    {
        $customer = $this->customer()->load([
            'activeSubscription.package',
            'subscriptions' => fn ($query) => $query->with('package')->latest()->limit(10),
        ]);

        $invoices = Invoice::query()
            ->where('customer_id', $customer->id)
            ->with(['payments' => fn ($query) => $query->where('status', 'successful')])
            ->latest('id')
            ->limit(6)
            ->get();

        $payments = Payment::query()
            ->where('customer_id', $customer->id)
            ->latest('payment_date')
            ->limit(6)
            ->get();

        $tenant = Tenant::query()->find($customer->tenant_id);
        $smsBalance = (int) (TenantSmsBalance::query()->where('tenant_id', $customer->tenant_id)->value('balance') ?? 0);
        $outstanding = (float) $invoices->sum(fn (Invoice $invoice) => in_array($invoice->status, ['pending', 'overdue'], true) ? max(0, (float) $invoice->total - (float) $invoice->paid_amount) : 0);

        return view('livewire.customer-profile', [
            'customer' => $customer,
            'subscription' => $customer->activeSubscription,
            'subscriptions' => $customer->subscriptions,
            'invoices' => $invoices,
            'payments' => $payments,
            'smsBalance' => $smsBalance,
            'outstanding' => $outstanding,
        ]);
    }
}
