<x-layouts.app title="ダッシュボード">
  <div class="max-w-4xl mx-auto px-4">

    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold">スタッフ売上ランキング（仮）</h1>

      {{-- ✅ 手動更新ボタン --}}
      <div class="space-x-2">
        <button wire:click="refreshData" class="text-sm px-3 py-1 rounded border">更新</button>
        <button wire:click="setRange('today')" class="text-sm px-3 py-1 rounded border">日</button>
        <button wire:click="setRange('week')"  class="text-sm px-3 py-1 rounded border">週</button>
        <button wire:click="setRange('month')" class="text-sm px-3 py-1 rounded border">月</button>
      </div>
    </div> {{-- ← ここが抜けていた！ --}}

    <div class="overflow-x-auto bg-white shadow rounded">
      <table class="min-w-full">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">順位</th>
            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">スタッフ</th>
            <th class="px-4 py-2 text-right text-sm font-medium text-gray-500">売上</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse($rows as $i => $r)
            <tr>
              <td class="px-4 py-2">{{ $i + 1 }}</td>
              <td class="px-4 py-2">
                {{ is_array($r) ? ($r['staff'] ?? '（不明）') : ($r->staff ?? '（不明）') }}
              </td>
              <td class="px-4 py-2 text-right">
                {{ number_format(is_array($r) ? ($r['sales'] ?? ($r['total'] ?? 0)) : ($r->total ?? 0)) }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-4 py-6 text-center text-gray-500">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>
</x-layouts.app>
