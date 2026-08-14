<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomMaintenanceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LodgingController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\OtherIncomeController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/loans', [ReportController::class, 'loans'])->name('reports.loans');
Route::get('/reports/periods', [ReportController::class, 'periods'])->name('reports.periods');

// Kamar
Route::resource('rooms', RoomController::class);
Route::patch('/rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.update-status');

// Penyewa
Route::resource('tenants', TenantController::class);

// Pembayaran / Omzet
Route::resource('payments', PaymentController::class);
Route::patch('/payments/settings/due-day', [PaymentController::class, 'updateDueDay'])->name('payments.update-due-day');

// Maintenance Kamar
Route::resource('maintenances', RoomMaintenanceController::class);

// Pengeluaran Kost
Route::resource('expenses', ExpenseController::class);

// Penginapan
Route::resource('lodgings', LodgingController::class);
Route::patch('/lodgings/settings/default-price', [LodgingController::class, 'updateDefaultPrice'])->name('lodgings.update-default-price');

// Pendapatan Lain-lain
Route::resource('other-incomes', OtherIncomeController::class);

// Piutang Kost & Pelunasan
Route::resource('receivables', \App\Http\Controllers\ReceivableController::class)->except(['create', 'edit']);
Route::post('/receivables/{loan}/repayments', [\App\Http\Controllers\ReceivableController::class, 'storeRepayment'])->name('receivables.repayments.store');
Route::delete('/receivables/{loan}/repayments/{repayment}', [\App\Http\Controllers\ReceivableController::class, 'destroyRepayment'])->name('receivables.repayments.destroy');

// Hutang Kost & Pembayaran
Route::resource('payables', \App\Http\Controllers\PayableController::class)->except(['create', 'edit']);
Route::post('/payables/{loan}/repayments', [\App\Http\Controllers\PayableController::class, 'storeRepayment'])->name('payables.repayments.store');
Route::delete('/payables/{loan}/repayments/{repayment}', [\App\Http\Controllers\PayableController::class, 'destroyRepayment'])->name('payables.repayments.destroy');

// Global Repayments Piutang (Saldo Bebas)
Route::post('/receivable-repayments', [\App\Http\Controllers\ReceivableController::class, 'storeGlobalRepayment'])->name('receivable-repayments.store');
Route::patch('/receivable-repayments/{repayment}/link', [\App\Http\Controllers\ReceivableController::class, 'linkGlobalRepayment'])->name('receivable-repayments.link');
Route::delete('/receivable-repayments/{repayment}', [\App\Http\Controllers\ReceivableController::class, 'destroyGlobalRepayment'])->name('receivable-repayments.destroy');

// Global Repayments Hutang (Pengeluaran Bebas)
Route::post('/payable-repayments', [\App\Http\Controllers\PayableController::class, 'storeGlobalRepayment'])->name('payable-repayments.store');
Route::patch('/payable-repayments/{repayment}/link', [\App\Http\Controllers\PayableController::class, 'linkGlobalRepayment'])->name('payable-repayments.link');
Route::delete('/payable-repayments/{repayment}', [\App\Http\Controllers\PayableController::class, 'destroyGlobalRepayment'])->name('payable-repayments.destroy');

// Pengaturan / Master Data
Route::prefix('settings')->name('settings.')->group(function() {
    Route::get('/', [ConfigController::class, 'index'])->name('index');
    
    // Floors CRUD
    Route::post('/floors', [ConfigController::class, 'storeFloor'])->name('floors.store');
    Route::put('/floors/{floor}', [ConfigController::class, 'updateFloor'])->name('floors.update');
    Route::delete('/floors/{floor}', [ConfigController::class, 'destroyFloor'])->name('floors.destroy');

    // Room Types CRUD
    Route::post('/room-types', [ConfigController::class, 'storeRoomType'])->name('room-types.store');
    Route::put('/room-types/{roomType}', [ConfigController::class, 'updateRoomType'])->name('room-types.update');
    Route::delete('/room-types/{roomType}', [ConfigController::class, 'destroyRoomType'])->name('room-types.destroy');

    // Facilities CRUD
    Route::post('/facilities', [ConfigController::class, 'storeFacility'])->name('facilities.store');
    Route::put('/facilities/{facility}', [ConfigController::class, 'updateFacility'])->name('facilities.update');
    Route::delete('/facilities/{facility}', [ConfigController::class, 'destroyFacility'])->name('facilities.destroy');

    // Maintenance Categories CRUD
    Route::post('/maintenance-categories', [ConfigController::class, 'storeMaintenanceCategory'])->name('maintenance-categories.store');
    Route::put('/maintenance-categories/{category}', [ConfigController::class, 'updateMaintenanceCategory'])->name('maintenance-categories.update');
    Route::delete('/maintenance-categories/{category}', [ConfigController::class, 'destroyMaintenanceCategory'])->name('maintenance-categories.destroy');

    // Expense Categories CRUD
    Route::post('/expense-categories', [ConfigController::class, 'storeExpenseCategory'])->name('expense-categories.store');
    Route::put('/expense-categories/{category}', [ConfigController::class, 'updateExpenseCategory'])->name('expense-categories.update');
    Route::delete('/expense-categories/{category}', [ConfigController::class, 'destroyExpenseCategory'])->name('expense-categories.destroy');

    // Tenant Custom Fields CRUD
    Route::post('/tenant-fields', [ConfigController::class, 'storeTenantField'])->name('tenant-fields.store');
    Route::put('/tenant-fields/{tenantField}', [ConfigController::class, 'updateTenantField'])->name('tenant-fields.update');
    Route::delete('/tenant-fields/{tenantField}', [ConfigController::class, 'destroyTenantField'])->name('tenant-fields.destroy');

    // Lodging default price
    Route::patch('/lodging-price', [ConfigController::class, 'updateLodgingPrice'])->name('lodging-price.update');
});
