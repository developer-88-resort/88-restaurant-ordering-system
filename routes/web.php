<?php

use App\Enums\UserRole;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MenuCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superadmin\AuditLogController as SuperadminAuditLogController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\ReportController as SuperadminReportController;
use App\Http\Controllers\Superadmin\SettingController as SuperadminSettingController;
use App\Http\Controllers\Superadmin\UserController as SuperadminUserController;
use App\Http\Controllers\TableController;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\Route;

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
    Route::resource('users', SuperadminUserController::class)->except('show');
    Route::get('/settings', [SuperadminSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SuperadminSettingController::class, 'update'])->name('settings.update');
    Route::get('/audit-logs', [SuperadminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/reports', [SuperadminReportController::class, 'index'])->name('reports.index');
});

Route::middleware(['auth', 'role:superadmin,admin'])->group(function () {
    Route::resource('menu-categories', MenuCategoryController::class)->except('show');
    Route::patch('menu-categories/{menuCategory}/toggle-status', [MenuCategoryController::class, 'toggleStatus'])
        ->name('menu-categories.toggle-status');
    Route::resource('menu-items', MenuItemController::class)->except('show');
    Route::patch('menu-items/{menuItem}/toggle-availability', [MenuItemController::class, 'toggleAvailability'])
        ->name('menu-items.toggle-availability');
    Route::resource('tables', TableController::class)->except('show');
    Route::post('tables-bulk', [TableController::class, 'storeBulk'])->name('tables.store-bulk');
    Route::patch('tables/{table}/status', [TableController::class, 'updateStatus'])->name('tables.update-status');
    Route::get('tables/{table}/qr-code', [TableController::class, 'qrCode'])->name('tables.qr-code');
    Route::get('tables/{table}/print', [TableController::class, 'print'])->name('tables.print');

    Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/mark-as-paid', [OrderController::class, 'markAsPaid'])->name('orders.mark-as-paid');
    Route::patch('orders/{order}/void-payment', [OrderController::class, 'voidPayment'])->name('orders.void-payment');
    Route::get('orders/{order}/receipt', [OrderController::class, 'receipt'])->name('orders.receipt');
    Route::get('orders/{order}/receipt/pdf', [OrderController::class, 'receiptPdf'])->name('orders.receipt.pdf');

    Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
});

Route::get('/order/{table:qr_token}', function (RestaurantTable $table) {
    return view('customer.table-placeholder', ['table' => $table]);
})->name('customer.tables.show');

require __DIR__.'/auth.php';
