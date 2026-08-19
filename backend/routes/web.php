<?php

use App\Livewire\Dashboard;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt([...$credentials, 'status' => 'active'], $request->boolean('remember'))) {
            $request->session()->regenerate();
            AuditLog::record('auth.login');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });
});

Route::post('/logout', function (Request $request) {
    AuditLog::record('auth.logout');
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::middleware('super-admin')->group(function () {
        Route::get('/tenants', App\Livewire\Tenants::class)->name('tenants');
        Route::get('/tenants/{tenant}', App\Livewire\TenantDetails::class)->name('tenant-details');
        Route::get('/isp-onboarding', App\Livewire\IspOnboarding::class)->name('isp-onboarding');
        Route::get('/saas-plans', App\Livewire\SaasPlans::class)->name('saas-plans');
        Route::get('/subscriptions', App\Livewire\Subscriptions::class)->name('subscriptions');
        Route::get('/saas-billing', App\Livewire\SaasBilling::class)->name('saas-billing');
        Route::get('/saas-payments', App\Livewire\SaasPayments::class)->name('saas-payments');
        Route::get('/payment-gateways', App\Livewire\PaymentGateways::class)->name('payment-gateways');
        Route::get('/add-ons', App\Livewire\AddOns::class)->name('add-ons');
        Route::get('/feature-modules', App\Livewire\FeatureModules::class)->name('feature-modules');
        Route::get('/multi-language', App\Livewire\MultiLanguage::class)->name('multi-language');
        Route::get('/multi-currency', App\Livewire\MultiCurrency::class)->name('multi-currency');
        Route::get('/white-label', App\Livewire\WhiteLabel::class)->name('white-label');
        Route::get('/customer-app', App\Livewire\CustomerApp::class)->name('customer-app');
        Route::get('/media-server', App\Livewire\MediaServerConsole::class)->name('media-server');
        Route::get('/platform-users', App\Livewire\PlatformUsers::class)->name('platform-users');
        Route::get('/audit-activity', App\Livewire\AuditActivity::class)->name('audit-activity');
        Route::get('/super-admin/{slug}', App\Livewire\ComingSoon::class)->name('super-admin.coming-soon');
        Route::get('/leave-impersonation', function (Request $request) {
            $tenant = Tenant::find($request->session()->get('impersonated_tenant_id'));
            AuditLog::record('tenant.impersonation.ended', $tenant, tenantId: $tenant?->id);
            $request->session()->forget(['impersonated_tenant_id', 'impersonated_tenant_name']);
            $request->session()->regenerate();

            return redirect()->route('tenants');
        })->name('leave-impersonation');
    });

    Route::middleware('tenant-context')->group(function () {
        Route::get('/customers', App\Livewire\Customers::class)->middleware('role:super_admin,tenant_admin,support,network_engineer')->name('customers');
        Route::get('/packages', App\Livewire\Packages::class)->middleware('role:super_admin,tenant_admin')->name('packages');
        Route::get('/billing', App\Livewire\Billing::class)->middleware('role:super_admin,tenant_admin,finance')->name('billing');
        Route::get('/payments', App\Livewire\Payments::class)->middleware('role:super_admin,tenant_admin,finance')->name('payments');
        Route::get('/network', App\Livewire\Network::class)->middleware('role:super_admin,tenant_admin,network_engineer')->name('network');
        Route::get('/resellers', App\Livewire\Resellers::class)->middleware('role:super_admin,tenant_admin')->name('resellers');
        Route::get('/reports', App\Livewire\Reports::class)->middleware('role:super_admin,tenant_admin,finance,support,network_engineer')->name('reports');
    });
});

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});
