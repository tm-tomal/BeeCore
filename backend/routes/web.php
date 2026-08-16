<?php

use App\Livewire\Dashboard;
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });
});

Route::post('/logout', function (Request $request) {
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
    Route::get('/customers', App\Livewire\Customers::class)->name('customers');
    Route::get('/tenants', App\Livewire\Tenants::class)->name('tenants');
    Route::get('/packages', App\Livewire\Packages::class)->name('packages');
    Route::get('/billing', App\Livewire\Billing::class)->name('billing');
    Route::get('/payments', App\Livewire\Payments::class)->name('payments');
    Route::get('/network', App\Livewire\Network::class)->name('network');
    Route::get('/resellers', App\Livewire\Resellers::class)->name('resellers');
    Route::get('/reports', App\Livewire\Reports::class)->name('reports');
});

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});
