<?php
namespace App\Livewire;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Network;
use App\Models\Payment;
use App\Models\Reseller;
use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Reports extends Component {
    use AuthorizesRoles;

    public string $from = '';
    public string $to = '';

    public function mount(): void {
        $this->from = now()->startOfMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function boot(): void {
        $this->authorizeRoles(User::ROLE_SUPER_ADMIN, User::ROLE_TENANT_ADMIN, User::ROLE_FINANCE, User::ROLE_SUPPORT, User::ROLE_NETWORK_ENGINEER);
    }

    public function render() {
        $tenantId = app(CurrentTenant::class)->id();
        $from = Carbon::parse($this->from ?: now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($this->to ?: now())->endOfDay();
        $payments = Payment::query()->where('tenant_id', $tenantId)->whereBetween('payment_date', [$from, $to]);
        $invoices = Invoice::query()->where('tenant_id', $tenantId)->whereBetween('created_at', [$from, $to]);
        $successful = (clone $payments)->where('status', 'successful');
        $paymentMethods = (clone $successful)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as transactions')
            ->groupBy('payment_method')->orderByDesc('total')->get();
        $invoiceStatuses = (clone $invoices)
            ->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('livewire.reports', [
            'metrics' => [
                'collections' => (float) (clone $successful)->sum('amount'),
                'transactions' => (clone $successful)->count(),
                'invoiced' => (float) (clone $invoices)->sum('total'),
                'customers' => Customer::query()->where('tenant_id', $tenantId)->count(),
                'active_customers' => Customer::query()->where('tenant_id', $tenantId)->where('status', 'active')->count(),
                'online_devices' => Network::query()->where('tenant_id', $tenantId)->where('status', 'online')->count(),
                'resellers' => Reseller::query()->where('tenant_id', $tenantId)->where('status', 'active')->count(),
            ],
            'paymentMethods' => $paymentMethods,
            'invoiceStatuses' => $invoiceStatuses,
            'maxPaymentMethod' => max(1, (float) $paymentMethods->max('total')),
        ]);
    }
}
