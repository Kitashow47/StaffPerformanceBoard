<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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

// --- アプリ本体 ---
Route::get('/', fn () => redirect()->route('dashboard'));
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
});

// --- 運用用：Shellなしで安全にartisanを叩く専用ルート（必ずJSONで返す） ---
Route::post('/ops/artisan', function (\Illuminate\Http\Request $req) {
    // 認証（Railway Variables の OPS_TOKEN を一致させる）
    if (! hash_equals(env('OPS_TOKEN', ''), $req->header('X-Ops-Token', ''))) {
        return response()->json(['ok' => false, 'error' => 'unauthorized'], 403);
    }

    // 許可コマンド（必要に応じて増やす）
    $cmd = $req->input('cmd');
    $allowed = [
        'migrate'         => fn() => Artisan::call('migrate', ['--force' => true]),
        'storage-link'    => fn() => Artisan::call('storage:link'),
        'optimize-clear'  => fn() => Artisan::call('optimize:clear'),
    ];
    if (! isset($allowed[$cmd])) {
        return response()->json(['ok' => false, 'error' => 'command not allowed'], 400);
    }

    $exit = $allowed[$cmd]();
    return response()->json([
        'ok'     => $exit === 0,
        'output' => Artisan::output(),
    ]);
})
->middleware('throttle:3,1') // 1分3回に制限
->withoutMiddleware([Csrf::class]); // CSRF除外（外部POSTのため）

// --- ヘルスチェック（切り分け用・任意） ---
Route::get('/healthz', fn() => response('OK', 200));

// 診断: 500切り分け用（デプロイ後に一時アクセスして確認）
Route::get('/_diag', function () {
    $info = [
        'php'            => PHP_VERSION,
        'laravel'        => app()->version(),
        'env'            => config('app.env'),
        'debug'          => config('app.debug'),
        'db_default'     => config('database.default'),
        'can_db'         => false,
        'vite_manifest'  => file_exists(public_path('build/manifest.json')),
        'storage_link'   => is_link(public_path('storage')),
        'has_dashboard'  => (bool) Route::has('dashboard'),
    ];
    try { DB::select('select 1'); $info['can_db'] = true; } catch (\Throwable $e) { $info['db_error'] = $e->getMessage(); }
    return response()->json($info);
});

require __DIR__.'/auth.php';
