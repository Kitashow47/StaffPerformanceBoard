<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmaregiClient
{
    protected string $base;
    protected string $contractId;
    protected ?string $accessToken;

    public function __construct()
    {
        // config/services.php に smaregi.api_base が無ければ env を使う
        $this->base        = rtrim(config('services.smaregi.api_base', env('SMAREGI_API_BASE', 'https://api.smaregi.dev')), '/');
        $this->contractId  = (string) env('SMAREGI_CONTRACT_ID', '');
        $this->accessToken = env('SMAREGI_ACCESS_TOKEN'); // OAuth導入前の暫定
    }

    /**
     * 取引ヘッダIDから取引詳細を取得
     * 先に STUB（ダミー）を返すので、配線確認が終わったら実API呼び出しに切替えます。
     */
    public function getTransactionByHeadId(string $headId): array
    {
        // --- ダミーデータ（まずは疎通確認用） ---
        if (env('SMAREGI_STUB', true)) {
            return [
                'transactionHeadId' => $headId,
                'soldAt'            => now('Asia/Tokyo')->format('Y-m-d H:i:s'),
                'totalAmount'       => 120000,
                'staffId'           => 'STF001',   // ← これが無い場合は破棄ロジックへ
                'storeId'           => 'STORE01',
                'lines'             => [],
            ];
        }

        // --- 実API呼び出し（OAuth導入後に有効化） ---
        $resp = Http::acceptJson()
            ->withHeaders(['X-contract-id' => $this->contractId])
            ->withToken($this->accessToken ?? '')
            ->timeout(10)
            ->get($this->base . '/pos/transactions/' . $headId);

        $resp->throw(); // 2xx以外は例外に
        return $resp->json();
    }
}
