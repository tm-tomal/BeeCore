<?php
namespace App\Livewire;

use App\Models\User;
use App\Support\AuthorizesRoles;
use App\Support\CurrentTenant;
use App\Support\ReportSnapshot;
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
        \App\Support\TenantPermissions::assert('reports');
    }

    public function render() {
        $tenantId = app(CurrentTenant::class)->id();
        $from = Carbon::parse($this->from ?: now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($this->to ?: now())->endOfDay();

        $snapshot = ReportSnapshot::forWorkspace($tenantId, $from, $to);

        return view('livewire.reports', [
            'period' => $snapshot['period'],
            'metrics' => $snapshot['metrics'],
            'paymentMethods' => $snapshot['paymentMethods'],
            'invoiceStatuses' => $snapshot['invoiceStatuses'],
            'maxPaymentMethod' => max(1, (float) collect($snapshot['paymentMethods'])->max('total')),
            'printUrl' => route('reports.print', [
                'from' => $snapshot['period']['from'],
                'to' => $snapshot['period']['to'],
            ]),
        ]);
    }
}
