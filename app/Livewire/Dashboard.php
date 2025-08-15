<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public array $rows = [];

    public function mount()
    {
        // 仮データ（あとでスマレジAPIの実データに置換）
        $this->rows = collect([
            ['staff' => '田中', 'sales' => 152000],
            ['staff' => '佐藤', 'sales' => 98000],
            ['staff' => '鈴木', 'sales' => 176000],
            ['staff' => '高橋', 'sales' => 121000],
            ['staff' => '伊藤', 'sales' => 86000],
        ])->sortByDesc('sales')->values()->all();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
