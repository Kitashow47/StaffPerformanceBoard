<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SmaregiContractController extends Controller
{
    public function handle(Request $req)
    {
        // 署名検証（任意）: X-Signature を HMAC で検証したい場合は下記を使う
        if ($secret = env('SMAREGI_WEBHOOK_SECRET')) {
            $sig = $req->header('X-Signature', '');
            $calc = 'sha256='.hash_hmac('sha256', $req->getContent(), $secret);
            if (!hash_equals($calc, $sig)) {
                Log::warning('contract-notify signature mismatch', ['sig'=>$sig]);
                return response()->json(['ok'=>false], 401);
            }
        }

        $payload = $req->json()->all() ?: $req->post(); // JSON or x-www-form-urlencoded
        // 受信内容は仕様に合わせて取り出す（ここでは汎用保存）
        DB::table('webhook_events')->insert([
            'source'      => 'smaregi-contract',
            'event_id'    => $payload['eventId'] ?? null,
            'fingerprint' => hash('sha256', 'contract'."\n".$req->getContent()),
            'payload'     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 例：契約テーブルに upsert（後述の migration で作成）
        if (!empty($payload['contractId'] ?? null)) {
            DB::table('contracts')->updateOrInsert(
                ['contract_id' => (string)$payload['contractId']],
                [
                    'raw'        => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return response()->json(['ok' => true]);
    }
}
