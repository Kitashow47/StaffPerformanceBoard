<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class Dashboard extends Component
{
    public string $range = 'today'; // today|week|month

    // ★ 追加: ダミーの更新用カウンタ（値が変わると再描画される）
    public int $tick = 0;

    public function setRange(string $r): void
    {
        $this->range = in_array($r, ['today','week','month']) ? $r : 'today';
    }

    // ★ 追加: 「更新」ボタンが叩くメソッド（安全に再描画）
    public function refreshData(): void
    {
        $this->tick++;
    }

    public function render()
    {
        $nowJst = Carbon::now('Asia/Tokyo');
        [$start, $end] = match ($this->range) {
            'week'  => [$nowJst->copy()->startOfWeek(),  $nowJst->copy()->endOfWeek()],
            'month' => [$nowJst->copy()->startOfMonth(), $nowJst->copy()->endOfMonth()],
            default => [$nowJst->copy()->startOfDay(),   $nowJst->copy()->endOfDay()],
        };

        // ← テーブルが未作成でも500にしない
        if (! Schema::hasTable('staff_daily_totals')) {
            return view('livewire.dashboard', ['rows' => collect()]);
        }

        $rows = DB::table('staff_daily_totals')
            ->selectRaw('COALESCE(staff_code, "（不明）") as staff, SUM(COALESCE(gross_amount,0)) as total')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('staff')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        return view('livewire.dashboard', ['rows' => $rows]);
    }
}
