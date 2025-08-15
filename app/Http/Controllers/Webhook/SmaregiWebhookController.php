<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessSmaregiTransaction;

class SmaregiWebhookController extends Controller
{
    public function transactions(Request $req)
    {
    // --- 送信元制限（使っていれば既存のまま残す） ---
    if ($cidrs = env('WEBHOOK_ACCEPT_FROM_IPS')) {
        $ok = false;
        foreach (array_map('trim', explode(',', $cidrs)) as $cidr) {
            if ($this->ipInCidr($req->ip(), $cidr)) { $ok = true; break; }
        }
        if (!$ok) return response()->json(['ok'=>false], 403);
    }

    // --- Content-Type が JSON かチェック（; charset=utf-8 を含む場合もOKに） ---
    $ct = $req->header('Content-Type', '');
    if (! str_contains(strtolower($ct), 'application/json')) {
        Log::warning('Smaregi webhook: unsupported content-type', ['content_type' => $ct]);
        return response()->json(['ok'=>false,'error'=>'unsupported content-type'], 415);
    }

    // --- 署名検証（使っていれば） ---
    if ($secret = env('SMAREGI_WEBHOOK_SECRET')) {
        $raw  = $req->getContent();
        $sig  = $req->header('X-Signature', '');
        $calc = 'sha256='.hash_hmac('sha256', $raw, $secret);
        if (! hash_equals($calc, $sig)) {
            Log::warning('Smaregi webhook: signature mismatch', ['sig'=>$sig]);
            return response()->json(['ok'=>false], 401);
        }
    }

    // --- JSON を取得 ---
    $payload = $req->json()->all() ?? [];
    $headId  = data_get($payload, 'transactionHeadId');

    if (! $headId) {
        Log::warning('Smaregi webhook: missing transactionHeadId', [
            'content_type' => $ct,
            'payload' => $payload,
        ]);
        return response()->json(['ok'=>false,'error'=>'missing transactionHeadId'], 400);
    }

    // --- 冪等化 & ログ保存 ---
    $fp = hash('sha256', 'transactions'."\n".$req->getContent());
    try {
        DB::table('webhook_events')->insert([
            'source'      => 'smaregi-transactions',
            'event_id'    => data_get($payload, 'eventId'),
            'fingerprint' => $fp,
            'payload'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    } catch (\Illuminate\Database\QueryException $e) {
        return response()->json(['ok'=>true,'dup'=>true]);
    }

    // 即ACK → 後処理（取引詳細取得→集計）
    \App\Jobs\ProcessSmaregiTransaction::dispatchAfterResponse($headId);

    return response()->json(['ok'=>true]);
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) return $ip === $cidr;
        [$subnet, $mask] = explode('/', $cidr, 2);
        return (ip2long($ip) & ~((1 << (32-$mask)) - 1)) === ip2long($subnet);
    }
}
