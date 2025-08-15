<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\SmaregiClient;

class ProcessSmaregiTransaction
{
    use Dispatchable, Queueable;

    public function __construct(private string $headId) {}

    public function handle(SmaregiClient $client): void
    {
        $data = $client->getTransactionByHeadId($this->headId);

        $soldAtJst = data_get($data, 'soldAt');
        $soldAtUtc = Carbon::parse($soldAtJst, 'Asia/Tokyo')->utc();
        $storeId   = (string) data_get($data, 'storeId');
        $staff     = data_get($data, 'staffId') ?? data_get($data, 'staffCode');

        // staffId/Code が無ければ破棄
        if (!$staff) return;

        $amount = (int) data_get($data, 'totalAmount', 0);
        $dateJst = $soldAtUtc->copy()->timezone('Asia/Tokyo')->toDateString();

        // 集計テーブルに加算（P0: 正数加算のみ；返品/取消は後で反映）
        DB::table('staff_daily_totals')->updateOrInsert(
            ['date'=>$dateJst, 'store_id'=>$storeId, 'staff_code'=>$staff],
            [
                'staff_name'   => null,
                'gross_amount' => DB::raw('COALESCE(gross_amount,0) + '.intval($amount)),
                'updated_at'   => now(),
            ]
        );
    }
}
