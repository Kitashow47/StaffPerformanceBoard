<x-layouts.app title="ダッシュボード">
    <div class="max-w-4xl mx-auto px-6">
        <h1 class="text-2xl font-bold mb-4">スタッフ売上ランキング（仮）</h1>
        
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
                    @foreach ($rows as $i => $r)
                        <tr>
                            <td class="px-4 py-2">{{ $i + 1 }}</td>
                            <td class="px-4 py-2">{{ $r['staff'] }}</td>
                            <td class="px-4 py-2 text-right">¥{{ number_format($r['sales']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <p class="text-sm text-gray-500 mt-4">
            *いまはダミーデータです。次にスマレジAPIの実データへ差し替えます。
        </p>
    </div>
</x-layouts.app>
