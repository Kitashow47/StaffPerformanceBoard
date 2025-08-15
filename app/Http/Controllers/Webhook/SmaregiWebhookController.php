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
        // 送信元IPの制限（任意）
        if ($cidrs = env('WEBHOOK_ACCEPT_FROM_IPS')) {
            $ok = false;
            foreach (array_map('trim', explode(',', $cidrs)) as $cidr) {
                if ($this->ipInCidr($req->ip(), $cidr)) { $ok = true; break; }
            }
            if (!$ok) return response()->json(['ok'=>false], 403);
        }

        // 署名検証（任意）
        if ($secret = env('SMAREGI_WEBHOOK_SECRET')) {
            $sig  = $req->header('X-Signature', '');
            $calc = 'sha256='.hash_hmac('sha256', $req->getContent(), $secret);
            if (! hash_equals($calc, $sig)) {
                Log::warning('transactions signature mismatch', ['sig'=>$sig]);
                return response()->json(['ok'=>false], 401);
            }
        }

        $payload = $req->json()->all();
        $headId  = data_get($payload, 'transactionHeadId');

        if (!$headId) {
            return response()->json(['ok'=>false,'error'=>'missing transactionHeadId'], 400);
        }

        // 冪等化（同一本文の重複を弾く）
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
            // duplicate（既受信）はOKとしてACK
            return response()->json(['ok'=>true,'dup'=>true]);
        }

        // 即ACK（レスポンス後に処理を走らせる）
        ProcessSmaregiTransaction::dispatchAfterResponse($headId);

        return response()->json(['ok'=>true]);
    }

    private function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) return $ip === $cidr;
        [$subnet, $mask] = explode('/', $cidr, 2);
        return (ip2long($ip) & ~((1 << (32-$mask)) - 1)) === ip2long($subnet);
    }
}
