<?php

use App\Http\Controllers\ClientLookupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderPrintController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/booking', [HomeController::class, 'booking'])->name('booking');

// ─── Личный кабинет клиента ─────────────────────────────────────────────
// Публичные роуты, защищены OTP-кодом на email.
Route::prefix('my-visits')->name('lookup.')->group(function () {
    Route::get('/', [ClientLookupController::class, 'showForm'])->name('form');

    // Лимит: 5 запросов кода в час с одного IP (защита от спама письмами)
    Route::post('/send-code', [ClientLookupController::class, 'sendCode'])
        ->middleware('throttle:5,60')
        ->name('send-code');

    Route::get('/verify', [ClientLookupController::class, 'showCodeForm'])->name('verify-form');

    // Лимит: 10 попыток ввода кода в час (защита от перебора)
    Route::post('/verify', [ClientLookupController::class, 'verify'])
        ->middleware('throttle:10,60')
        ->name('verify');

    Route::get('/history', [ClientLookupController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [ClientLookupController::class, 'logout'])->name('logout');
});

// ─── Печать заказ-наряда (для сотрудников) ──────────────────────────────
Route::middleware(['auth'])
    ->get('/orders/{order}/print', [OrderPrintController::class, 'print'])
    ->name('orders.print');

// ─── Отчёты (для сотрудников) ───────────────────────────────────────────
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/orders', [ReportsController::class, 'orders'])->name('orders');
    Route::get('/payments', [ReportsController::class, 'payments'])->name('payments');
    Route::get('/appointments', [ReportsController::class, 'appointments'])->name('appointments');
    Route::get('/parts', [ReportsController::class, 'parts'])->name('parts');
    Route::get('/income', [ReportsController::class, 'income'])->name('income');
    Route::get('/clients', [ReportsController::class, 'clients'])->name('clients');
});
