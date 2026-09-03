<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Network;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Reseller;
use App\Models\Tenant;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach ([Tenant::class, Customer::class, CustomerSubscription::class, Invoice::class, InvoiceItem::class, Package::class, Payment::class, Network::class, Reseller::class] as $model) {
            $model::observe(AuditableObserver::class);
        }

        // Apply the user's chosen language before each web request.
        $this->app['router']->pushMiddlewareToGroup('web', \App\Http\Middleware\SetLocale::class);
    }
}
