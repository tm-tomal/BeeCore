<?php

use App\Livewire\Dashboard;
use App\Models\AuditLog;
use App\Models\BlockedIp;
use App\Models\LoginAttempt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (BlockedIp::where('ip_address', $request->ip())->exists()) {
            return back()->withErrors(['email' => 'This IP address has been blocked from signing in.'])->onlyInput('email');
        }

        $success = Auth::attempt([...$credentials, 'status' => 'active'], $request->boolean('remember'));

        LoginAttempt::create([
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $success,
            'user_id' => $success ? Auth::id() : User::where('email', $credentials['email'])->value('id'),
            'created_at' => now(),
        ]);

        if ($success) {
            $request->session()->regenerate();
            AuditLog::record('auth.login');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });
});

Route::middleware('guest')->group(function () {
    // ISP self-registration
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', function (Request $request) {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'operationMode' => ['required', Rule::in(['automatic', 'manual'])],
            'businessAddress' => ['required', 'string', 'max:500'],
            'ownerName' => ['required', 'string', 'max:255'],
            'ownerEmail' => ['required', 'email', 'max:255', 'unique:users,email'],
            'ownerPhone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $slug = Str::slug($validated['name']);
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = Str::slug($validated['name']).'-'.Str::lower(Str::random(4));
        }

        $tenant = DB::transaction(function () use ($validated, $slug) {
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'status' => 'active',
                'operation_mode' => $validated['operationMode'],
                'currency' => 'BDT',
                'timezone' => 'Asia/Dhaka',
                'language' => 'en',
                'owner_name' => $validated['ownerName'],
                'owner_email' => $validated['ownerEmail'],
                'owner_phone' => $validated['ownerPhone'],
                'contact_address' => $validated['businessAddress'],
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['ownerName'],
                'email' => $validated['ownerEmail'],
                'password' => $validated['password'],
                'role' => User::ROLE_TENANT_ADMIN,
                'status' => 'active',
            ]);

            AuditLog::record('tenant.self_registered', $tenant, ['owner_user_id' => $user->id], tenantId: $tenant->id);

            return $tenant;
        });

        return redirect()->route('login')->with('status', 'Your ISP workspace was created. Sign in with your owner account to continue.');
    });

    // Forgot password
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    })->name('password.email');

    // Password reset form
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    })->name('password.update');
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
    Route::get('/my-profile', App\Livewire\MyProfile::class)->name('my-profile');

    Route::post('/locale', function (Request $request) {
        $language = \App\Models\Language::query()->where('code', (string) $request->input('locale'))->where('is_active', true)->first();

        abort_unless($language, 422, 'Unsupported language.');

        session(['locale' => $language->code]);
        app()->setLocale($language->code);

        if (Auth::check()) {
            Auth::user()->forceFill(['language' => $language->code])->save();
        }

        return redirect()->back();
    })->name('locale.switch');

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
        Route::get('/sms-management', App\Livewire\SmsManagement::class)->name('sms-management');
        Route::get('/email-management', App\Livewire\EmailManagement::class)->name('email-management');
        Route::get('/notifications', App\Livewire\Notifications::class)->name('notifications');
        Route::get('/announcements', App\Livewire\Announcements::class)->name('announcements');
        Route::get('/network-integrations', App\Livewire\NetworkIntegrations::class)->name('network-integrations');
        Route::get('/api-management', App\Livewire\ApiManagement::class)->name('api-management');
        Route::get('/system-settings', App\Livewire\SystemSettings::class)->name('system-settings');
        Route::get('/system-health', App\Livewire\SystemHealth::class)->name('system-health');
        Route::get('/queue-jobs', App\Livewire\QueueJobs::class)->name('queue-jobs');
        Route::get('/data-management', App\Livewire\DataManagement::class)->name('data-management');
        Route::get('/support-tickets', App\Livewire\SupportTickets::class)->name('support-tickets');
        Route::get('/reports-analytics', App\Livewire\ReportsAnalytics::class)->name('reports-analytics');
        Route::get('/platform-analytics', App\Livewire\PlatformAnalytics::class)->name('platform-analytics');
        Route::get('/platform-users', App\Livewire\PlatformUsers::class)->name('platform-users');
        Route::get('/roles-permissions', App\Livewire\RolesPermissions::class)->name('roles-permissions');
        Route::get('/security-center', App\Livewire\SecurityCenter::class)->name('security-center');
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
        Route::get('/billing/invoices/{invoice}/print', function (\App\Models\Invoice $invoice) {
            $tenantId = app(\App\Support\CurrentTenant::class)->id();
            abort_unless((int) $invoice->tenant_id === (int) $tenantId, 404, 'Invoice not found in this workspace.');

            $invoice->load(['customer', 'items', 'payments' => fn ($query) => $query->where('status', 'successful')]);

            return view('billing.invoice-print', [
                'invoice' => $invoice,
                'branding' => \App\Models\TenantBranding::query()->where('tenant_id', $tenantId)->first(),
            ]);
        })->middleware('role:super_admin,tenant_admin,finance')->name('billing.invoice-print');
        Route::get('/payments', App\Livewire\Payments::class)->middleware('role:super_admin,tenant_admin,finance')->name('payments');
        Route::get('/network', App\Livewire\Network::class)->middleware('role:super_admin,tenant_admin,network_engineer')->name('network');
        Route::get('/resellers', App\Livewire\Resellers::class)->middleware('role:super_admin,tenant_admin')->name('resellers');
        Route::get('/reports', App\Livewire\Reports::class)->middleware('role:super_admin,tenant_admin,finance,support,network_engineer')->name('reports');
        Route::get('/reports/print', function () {
            $tenantId = app(\App\Support\CurrentTenant::class)->id();
            $from = \Carbon\Carbon::parse(request('from', now()->startOfMonth()->toDateString()))->startOfDay();
            $to = \Carbon\Carbon::parse(request('to', now()->toDateString()))->endOfDay();

            $workspace = \App\Models\Tenant::query()->findOrFail($tenantId);
            $snapshot = \App\Support\ReportSnapshot::forWorkspace($tenantId, $from, $to);

            return view('reports.report-print', [
                'workspace' => $workspace,
                'period' => $snapshot['period'],
                'metrics' => $snapshot['metrics'],
                'paymentMethods' => $snapshot['paymentMethods'],
                'invoiceStatuses' => $snapshot['invoiceStatuses'],
            ]);
        })->middleware('role:super_admin,tenant_admin,finance,support,network_engineer')->name('reports.print');
        Route::get('/settings', App\Livewire\IspSettings::class)->middleware('role:super_admin,tenant_admin')->name('isp-settings');
        Route::get('/gateway', App\Livewire\IspGateway::class)->middleware('role:super_admin,tenant_admin')->name('isp-gateway');
        Route::get('/subscription', App\Livewire\IspSubscription::class)->middleware('role:super_admin,tenant_admin')->name('isp-subscription');
    });
});

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});
