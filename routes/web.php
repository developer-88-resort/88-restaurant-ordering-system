<?php

use App\Enums\UserRole;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\CustomerWelcomeController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpaceCategoryController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\Superadmin\AuditLogController as SuperadminAuditLogController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\ReportController as SuperadminReportController;
use App\Http\Controllers\Superadmin\SettingController as SuperadminSettingController;
use App\Http\Controllers\Superadmin\UserController as SuperadminUserController;
use App\Http\Controllers\Superadmin\WelcomeQrController as SuperadminWelcomeQrController;
use Illuminate\Support\Facades\Route;

Route::post('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        UserRole::Superadmin => redirect()->route('superadmin.dashboard'),
        default => redirect()->route('profile.edit'),
    };
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
});

Route::prefix('superadmin')->name('superadmin.')->middleware(['auth', 'role:superadmin'])->group(function () {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', SuperadminUserController::class)->except(['show', 'destroy']);
    Route::post('users/{user}/resend-invitation', [SuperadminUserController::class, 'resendInvitation'])->name('users.resend-invitation');
    Route::post('users/{user}/send-password-reset', [SuperadminUserController::class, 'sendPasswordReset'])->name('users.send-password-reset');
    Route::post('users/{user}/deactivate', [SuperadminUserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('users/{user}/reactivate', [SuperadminUserController::class, 'reactivate'])->name('users.reactivate');
    Route::get('/settings', [SuperadminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SuperadminSettingController::class, 'update'])->name('settings.update');
    Route::get('/audit-logs', [SuperadminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/reports', [SuperadminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/pdf', [SuperadminReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/welcome-qr', [SuperadminWelcomeQrController::class, 'print'])->name('welcome-qr.print');
    Route::get('/welcome-qr/image', [SuperadminWelcomeQrController::class, 'image'])->name('welcome-qr.image');
});

Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::resource('menu-categories', MenuCategoryController::class)->except('show');
    Route::patch('menu-categories/{menuCategory}/toggle-status', [MenuCategoryController::class, 'toggleStatus'])
        ->name('menu-categories.toggle-status');
    Route::resource('menu-items', MenuItemController::class)->except('show');
    Route::patch('menu-items/{menuItem}/toggle-availability', [MenuItemController::class, 'toggleAvailability'])
        ->name('menu-items.toggle-availability');
    Route::get('spaces', [SpaceController::class, 'index'])->name('spaces.index');
    Route::get('spaces/create', [SpaceController::class, 'create'])->name('spaces.create');
    Route::post('spaces', [SpaceController::class, 'store'])->name('spaces.store');
    Route::post('spaces-bulk', [SpaceController::class, 'storeBulk'])->name('spaces.store-bulk');
    Route::get('spaces/{space}/edit', [SpaceController::class, 'edit'])->name('spaces.edit');
    Route::put('spaces/{space}', [SpaceController::class, 'update'])->name('spaces.update');
    Route::delete('spaces/{space}', [SpaceController::class, 'destroy'])->name('spaces.destroy');
    Route::patch('spaces/{space}/status', [SpaceController::class, 'updateStatus'])->name('spaces.update-status');
    Route::get('spaces/{space}/qr-code', [SpaceController::class, 'qrCode'])->name('spaces.qr-code');
    Route::get('spaces/{space}/print', [SpaceController::class, 'print'])->name('spaces.print');

    Route::resource('areas', AreaController::class)->except('show');

    Route::get('space-categories/create/{area}', [SpaceCategoryController::class, 'create'])->name('space-categories.create');
    Route::post('space-categories', [SpaceCategoryController::class, 'store'])->name('space-categories.store');
    Route::get('space-categories/{spaceCategory}/edit', [SpaceCategoryController::class, 'edit'])->name('space-categories.edit');
    Route::put('space-categories/{spaceCategory}', [SpaceCategoryController::class, 'update'])->name('space-categories.update');
    Route::delete('space-categories/{spaceCategory}', [SpaceCategoryController::class, 'destroy'])->name('space-categories.destroy');
    Route::patch('space-categories/{spaceCategory}/sessions/{spaceSession}/end', [SpaceCategoryController::class, 'endSession'])->name('space-categories.sessions.end');

    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/mark-as-paid', [OrderController::class, 'markAsPaid'])->name('orders.mark-as-paid');
    Route::patch('orders/{order}/void-payment', [OrderController::class, 'voidPayment'])->name('orders.void-payment');
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('orders/{order}/receipt/pdf', [OrderController::class, 'receiptPdf'])->name('orders.receipt.pdf');

    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
});

Route::get('/order/status/{token}', [CustomerOrderController::class, 'status'])->name('customer.orders.status');
Route::get('/order/receipt/{token}', [CustomerOrderController::class, 'receipt'])->name('customer.orders.receipt');
Route::get('/order/{space:qr_token}', [CustomerOrderController::class, 'show'])->name('customer.spaces.show');
Route::post('/order/{space:qr_token}', [CustomerOrderController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('customer.orders.store');

// The general "lobby QR" welcome flow — see CustomerWelcomeController's
// class doc comment for how this differs from the per-table QR flow above.
Route::get('/welcome', [CustomerWelcomeController::class, 'show'])->name('customer.welcome.show');
Route::get('/welcome/seats', [CustomerWelcomeController::class, 'seats'])->name('customer.welcome.seats');
Route::get('/welcome/takeout', [CustomerWelcomeController::class, 'takeoutMenu'])->name('customer.welcome.takeout');
Route::post('/welcome/takeout', [CustomerWelcomeController::class, 'storeTakeout'])
    ->middleware('throttle:20,1')
    ->name('customer.welcome.takeout.store');
Route::get('/welcome/menu', [CustomerWelcomeController::class, 'menu'])->name('customer.welcome.menu');
Route::get('/welcome/call-staff', [CustomerWelcomeController::class, 'callStaffForm'])->name('customer.welcome.call-staff.form');
Route::post('/welcome/call-staff', [CustomerWelcomeController::class, 'callStaff'])
    ->middleware('throttle:10,1')
    ->name('customer.welcome.call-staff.send');

require __DIR__.'/auth.php';
