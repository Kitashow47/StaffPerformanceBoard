<x-layouts.app title="ダッシュボード">
    {{-- 10秒おきに自動再描画（Webhookで集計が更新される前提） --}}
    <div wire:poll.10s class="max-w-4xl mx-auto px-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">スタッフ売上ランキング</h1>
            <div class="space-x-2">
                <button wire:click="setRange('today')" class="px-3 py-1 rounded {{ $range==='today'?'bg-indigo-600 text-white':'bg-gray-200' }}">今日</button>
                <button wire:click="setRange('week')"  class="px-3 py-1 rounded {{ $range==='week' ?'bg-indigo-600 text-white':'bg-gray-200' }}">今週</button>
                <button wire:click="setRange('month')" class="px-3 py-1 rounded {{ $range==='month'?'bg-indigo-600 text-white':'bg-gray-200' }}">今月</button>
            </div>
        </div>

        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">順位</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">スタッフ</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">売上</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($rows as $i => $r)
                        <tr>
                            <td class="px-4 py-2">{{ $i + 1 }}</td>
                            <td class="px-4 py-2">{{ $r->staff }}</td>
                            <td class="px-4 py-2 text-right">¥{{ number_format($r->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-4 py-8 text-center text-gray-500" colspan="3">データがありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
