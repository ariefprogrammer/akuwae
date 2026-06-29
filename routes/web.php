<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\RegisterForm;
use App\Livewire\Tenant\Dashboard as TenantDashboard;
use App\Livewire\Tenant\OnboardingForm as TenantOnboardingForm;
use App\Livewire\Tenant\StoreSettings;
use App\Livewire\Tenant\Menu\MenuIndex;
use App\Livewire\Tenant\Menu\MenuForm;
use App\Livewire\Tenant\Report;
use App\Livewire\Tenant\Order\OrderList as TenantOrderList;

use App\Livewire\Driver\Dashboard as DriverDashboard;
use App\Livewire\Driver\OnboardingForm as DriverOnboardingForm;
use App\Livewire\Driver\ActiveOrder;
use App\Livewire\Driver\OrderHistory;
use App\Livewire\Driver\Profile as DriverProfile;

use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\Order\AntarOrder;
use App\Livewire\Customer\Order\OrderList;
use App\Livewire\Customer\Order\OrderTracking;
use App\Livewire\Customer\Order\MakanOrder;
use App\Livewire\Customer\Order\CustomOrder;
use App\Livewire\Customer\Profile;
use App\Livewire\Customer\SavedAddresses;

use App\Livewire\Driver\WorkingBalance as DriverWorkingBalance;
use App\Livewire\Tenant\WorkingBalance as TenantWorkingBalance;

use App\Http\Controllers\Api\RouteController;

Route::middleware('guest')->group(function () {
    Route::get('/', LoginForm::class)->name('login');
    Route::get('/register', RegisterForm::class)->name('register');
});

// ========== AUTHENTICATED ROUTES ==========
Route::middleware('auth')->group(function () {

    Route::get('/home', function () {
        return match(auth()->user()->role) {
            'customer' => redirect()->route('customer.dashboard'),
            'tenant'   => redirect()->route('tenant.dashboard'),
            'driver'   => redirect()->route('driver.dashboard'),
            default    => redirect()->route('login'),
        };
    })->name('home');

    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');
    // TEMPORARY: hapus setelah ada tombol logout di UI
    Route::get('/logout-dev', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout.dev');

    // Customer
    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::get('/dashboard', CustomerDashboard::class)->name('dashboard');
        Route::get('/orders', OrderList::class)->name('order.index');
        Route::get('/order/antar', AntarOrder::class)->name('order.antar');
        Route::get('/order/makan', MakanOrder::class)->name('order.makan');
        Route::get('/order/custom', CustomOrder::class)->name('order.custom');
        Route::get('/order/{order}/tracking', OrderTracking::class)->name('order.tracking');
        Route::get('/profile', Profile::class)->name('profile');
        Route::get('/addresses', SavedAddresses::class)->name('addresses');
    });

    // Tenant
    Route::middleware('role:tenant')->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/dashboard', TenantDashboard::class)->name('dashboard');
        Route::get('/onboarding', TenantOnboardingForm::class)->name('onboarding');
        Route::get('/menu', MenuIndex::class)->name('menu.index');
        Route::get('/menu/create', MenuForm::class)->name('menu.create');
        Route::get('/menu/{menu}/edit', MenuForm::class)->name('menu.edit');
        Route::get('/orders', TenantOrderList::class)->name('order.index');
        Route::get('/report', Report::class)->name('report');
        Route::get('/settings', StoreSettings::class)->name('settings');
        Route::get('/balance', TenantWorkingBalance::class)->name('balance');
    });

    // Driver
    Route::middleware('role:driver')->prefix('driver')->name('driver.')->group(function () {
        Route::get('/dashboard', DriverDashboard::class)->name('dashboard');
        Route::get('/onboarding', DriverOnboardingForm::class)->name('onboarding');
        Route::get('/order/{order}', ActiveOrder::class)->name('active-order');
        Route::get('/history', OrderHistory::class)->name('order.history');
        Route::get('/balance', DriverWorkingBalance::class)->name('balance');
        Route::get('/profile', DriverProfile::class)->name('profile');
    });

    Route::post('/push-subscribe', [App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('/push-unsubscribe', [App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    Route::post('/api/calculate-route', [RouteController::class, 'calculate'])->name('api.calculate-route');
});