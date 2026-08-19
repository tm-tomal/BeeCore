<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Invoice;
use App\Services\RecurringInvoiceGenerator;
use App\Services\SaasSubscriptionBilling;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('billing:mark-overdue', function () {
    $updated = 0;

    Invoice::query()
        ->where('status', 'pending')
        ->whereDate('due_date', '<', today())
        ->chunkById(100, function ($invoices) use (&$updated) {
            foreach ($invoices as $invoice) {
                $invoice->update(['status' => 'overdue']);
                $updated++;
            }
        });

    $this->info("Marked {$updated} invoice(s) overdue.");
})->purpose('Mark past-due pending invoices as overdue');

Artisan::command('billing:generate-recurring', function (RecurringInvoiceGenerator $generator) {
    $generated = $generator->generateDue();

    $this->info("Generated {$generated} recurring invoice(s).");
})->purpose('Generate invoices for due customer subscriptions');

Artisan::command('saas:process-subscriptions', function (SaasSubscriptionBilling $billing) {
    $summary = $billing->processDue();

    $this->info(sprintf(
        'Trials converted: %d, renewed: %d, expired: %d, invoices overdue: %d, suspended: %d.',
        $summary['trials_converted'],
        $summary['renewed'],
        $summary['expired'],
        $summary['invoices_overdue'],
        $summary['suspended'],
    ));
})->purpose('Convert trials, renew or expire subscriptions, and suspend unpaid tenants');

Schedule::command('billing:generate-recurring')->dailyAt('00:01')->withoutOverlapping();
Schedule::command('billing:mark-overdue')->dailyAt('00:05')->withoutOverlapping();
Schedule::command('saas:process-subscriptions')->dailyAt('00:10')->withoutOverlapping();
