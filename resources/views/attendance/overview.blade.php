<x-app-layout>
  <div class="container mx-auto p-4">
    <div class="flex items-center justify-between mb-4">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold">出席一覧：{{ $course->name }}</h1>

        {{-- 年選択（「2024」をクリック → セレクト表示） --}}
        <div class="relative">
          <select name="year"
                  class="border rounded pl-3 pr-8 py-1"
                  onchange="this.form.submit()">
            @for ($y = now()->year - 1; $y <= now()->year + 5; $y++)
              <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endfor
          </select>
        </div>
      </div>
      <div class="text-xs text-gray-500">
        アンカー（日曜始まり）：{{ \Carbon\Carbon::parse($anchor)->format('Y/m/d') }}
      </div>
    </div>

    <div class="overflow-auto border rounded">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="sticky top-0">
            <th class="p-2 text-left w-28 sticky left-0 z-20 bg-gray-50 shadow-[2px_0_0_rgba(0,0,0,0.05)]">
              ID
            </th>
            <th class="p-2 text-left min-w-[200px] sticky left-8 z-20 bg-gray-50 shadow-[2px_0_0_rgba(0,0,0,0.05)]">
              名前
            </th>
            @foreach ($weeks as $wk)
              <th class="p-2 text-center min-w-14">{{ $wk['label'] }}</th>
            @endforeach
          </tr>
        </thead>

        <tbody>
          @foreach ($students as $s)
            <tr class="border-t hover:bg-gray-50">
              <td class="p-2 w-28 sticky left-0 z-10 bg-white">
                {{ $s['id'] }}
              </td>
              <td class="p-2 min-w-[200px] sticky left-8 z-10 bg-white">
                {{ $s['name'] }}
              </td>
              @foreach ($weeks as $wk)
                @php $st = $matrix[$s['id']][$wk['key']] ?? 'none'; @endphp
                <td class="p-2 text-center">
                  @switch($st)
                    @case('present')
                      <span class="inline-block rounded-full w-6 h-6 leading-6 text-center bg-green-200 text-green-800">○</span>
                      @break
                    @case('partial')
                      <span class="inline-block rounded-full w-6 h-6 leading-6 text-center bg-yellow-100 text-yellow-800">△</span>
                      @break
                    @default
                      <span class="inline-block text-gray-400">-</span>
                  @endswitch
                </td>
              @endforeach
            </tr>
          @endforeach
        </tbody>

      </table>
    </div>

    <div class="mt-3 text-xs text-gray-600">
      <span class="inline-flex items-center mr-4">
        <span class="inline-block w-3 h-3 rounded-full bg-green-400 mr-1"></span>出席（⚪︎）
      </span>
      <span class="inline-flex items-center mr-4">
        <span class="inline-block w-3 h-3 rounded-full bg-yellow-400 mr-1"></span>一部出席（△）
      </span>
      <span class="inline-flex items-center">
        <span class="inline-block w-3 h-3 rounded-full bg-gray-300 mr-1"></span>未出席（-）
      </span>
    </div>
  </div>
</x-app-layout>