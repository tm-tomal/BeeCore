<?php

namespace App\Support;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Network;
use App\Models\Payment;
use App\Models\Reseller;
use Carbon\CarbonInterface;

class ReportSnapshot
{
    /**
     * Gather tenant business-report data for a period, shared by the reports
     * page and the print-to-PDF view.
     *
     * @return array{
     *     period: array{from: string, to: string},
     *     metrics: array<string, mixed>,
     *     paymentMethods: \Illuminate\Support\Collection,
     *     invoiceStatuses: array<int, array{status: string, count: int, value: float}>,
     * }
     */
    public static function forWorkspace(int $tenantId, CarbonInterface $from, CarbonInterface $to): array
    {
        $payments = Payment::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$from, $to]);

        $invoices = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to]);

        $successful = (clone $payments)->where('status', 'successful');

        $paymentMethods = (clone $successful)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as transactions')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        $statusRows = (clone $invoices)
            ->selectRaw('status, COUNT(*) as count, SUM(total) as value')
            ->groupBy('status')
            ->get();

        $order = ['paid', 'pending', 'overdue', 'cancelled', 'draft'];
        $invoiceStatuses = $statusRows
            ->keyBy('status')
            ->pipe(function ($rows) use ($order) {
                return collect($order)
                    ->map(fn ($status) => [
                        'status' => $status,
                        'count' => (int) ($rows[$status]->count ?? 0),
                        'value' => (float) ($rows[$status]->value ?? 0),
                    ])
                    ->filter(fn ($row) => $row['count'] > 0 || $row['value'] > 0)
                    ->values()
                    ->all();
            });

        $collections = (float) (clone $successful)->sum('amount');
        $transactions = (clone $successful)->count();
        $invoiced = (float) (clone $invoices)->sum('total');
        $customerCount = Customer::query()->where('tenant_id', $tenantId)->count();
        $activeCustomers = Customer::query()->where('tenant_id', $tenantId)->where('status', 'active')->count();
        $onlineDevices = Network::query()->where('tenant_id', $tenantId)->where('status', 'online')->count();
        $resellers = Reseller::query()->where('tenant_id', $tenantId)->where('status', 'active')->count();

        $topMethod = $paymentMethods->first();

        return [
            'period' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
            'metrics' => [
                'collections' => $collections,
                'transactions' => $transactions,
                'invoiced' => $invoiced,
                'customers' => $customerCount,
                'active_customers' => $activeCustomers,
                'online_devices' => $onlineDevices,
                'resellers' => $resellers,
                'collection_rate' => $invoiced > 0 ? round($collections / $invoiced * 100, 1) : 0,
                'avg_payment' => $transactions > 0 ? round($collections / $transactions, 2) : 0,
                'top_method' => $topMethod ? (string) $topMethod->payment_method : null,
                'top_method_value' => $topMethod ? (float) $topMethod->total : 0,
            ],
            'paymentMethods' => $paymentMethods->map(function ($method) use ($collections) {
                return [
                    'payment_method' => $method->payment_method,
                    'total' => (float) $method->total,
                    'transactions' => (int) $method->transactions,
                    'share' => $collections > 0 ? round(((float) $method->total / $collections) * 100, 1) : 0,
                ];
            }),
            'invoiceStatuses' => $invoiceStatuses,
        ];
    }
}
