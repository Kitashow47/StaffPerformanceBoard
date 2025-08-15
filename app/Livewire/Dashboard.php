<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public string $range = 'today'; // today|week|month

    public function setRange(string $r)
    {
        $this->range = in_array($r, ['today','week','month']) ? $r : 'today';
    }

    public function render()
    {
        // 表示は日本時間基準
        $nowJst = Carbon::now('Asia/Tokyo');

        [$start, $end] = match ($this->range) {
            'week'  => [$nowJst->copy()->startOfWeek(),  $nowJst->copy()->endOfWeek()],
            'month' => [$nowJst->copy()->startOfMonth(), $nowJst->copy()->endOfMonth()],
            default => [$nowJst->copy()->startOfDay(),   $nowJst->copy()->endOfDay()],
        };

        // staff_daily_totals（JST日付）から集計
        $rows = DB::table('staff_daily_totals')
            ->select('staff_code as staff', DB::raw('SUM(gross_amount) as total'))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('staff')
            ->orderByDesc('total')
            ->limit(50)
            ->get();

        return view('livewire.dashboard', ['rows' => $rows]);
    }
}
