<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Csrf;
use App\Http\Controllers\Webhook\SmaregiWebhookController;
use App\Http\Controllers\Webhook\SmaregiContractController;

// 契約通知（アプリ購入時の契約ID等）
Route::post('/webhooks/smaregi/contract-notify', [SmaregiContractController::class, 'handle'])
    ->withoutMiddleware([Csrf::class]);   // ← これを付与

// 取引Webhook（transactions）
Route::post('/webhooks/smaregi/transactions', [SmaregiWebhookController::class, 'transactions'])
    ->withoutMiddleware([Csrf::class]);   // ← これを付与

Route::get('/', fn () => redirect()->route('dashboard'));
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});

require __DIR__.'/auth.php';
