<x-app-layout>
<div class="container mx-auto p-4">
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-2xl font-bold">
      出席登録：{{ $course->name }}
    </h1>
    <a class="underline" href="{{ route('attendance.overview', $course) }}">出席一覧（週）</a>
  </div>

  @if (session('status'))
    <div class="p-3 rounded bg-green-100 mb-3">{{ session('status') }}</div>
  @endif

  <form method="GET" action="{{ route('attendance.create', $course) }}" class="flex items-center gap-3 mb-6">
    <label class="text-sm">日付</label>
    <input type="date" name="date" value="{{ $date }}" class="border rounded px-2 py-1">
    <button class="px-3 py-1 border rounded">表示</button>
  </form>

  <div x-data="attendanceApp({
      initial: @js($existing),
      students: @js($students),
      date: @js($date)
    })" x-cloak class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- 左：名簿 -->
    <div class="lg:col-span-2">
      <div class="flex items-center justify-between mb-3">
        <div class="text-sm text-gray-600">クリックで選択。現在のモード：</div>
        <div class="flex gap-2">
          <template x-for="m in modes" :key="m.value">
            <button type="button"
              class="px-3 py-1 rounded border"
              :class="mode===m.value ? 'bg-black text-white' : 'bg-white'"
              x-text="m.label"
              @click="mode=m.value">
            </button>
          </template>
          <button type="button" class="px-3 py-1 rounded border"
                  @click="markAll('present')">全員 出席</button>
        </div>
      </div>

      <div class="overflow-auto border rounded">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="p-2 text-left">ID</th>
              <th class="p-2 text-left">名前</th>
              <th class="p-2">状態</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="s in students" :key="s.id">
              <tr class="border-t hover:bg-gray-50 cursor-pointer"
                  @click="toggle(s.id)">
                <td class="p-2 underline text-blue-700" x-text="s.id"></td>
                <td class="p-2 underline text-blue-700" x-text="s.name"></td>
                <td class="p-2 text-center">
                  <span class="px-2 py-1 rounded"
                        :class="badgeClass(statusOf(s.id))"
                        x-text="labelOf(statusOf(s.id))"></span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 右：確認 & 保存 -->
    <div>
      <form method="POST" action="{{ route('attendance.store', $course) }}" @submit="serialize">
        @csrf
        <input type="hidden" name="date" :value="date">
        <div class="p-4 border rounded space-y-3">
          <div class="font-semibold">本日の出席簿（{{ $date }}）</div>
          <div class="text-xs text-gray-600">「確定する」を押すと保存されます。</div>

          <div class="max-h-80 overflow-auto border rounded">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="p-2 text-left">ID</th>
                  <th class="p-2 text-left">名前</th>
                  <th class="p-2 text-left">状態</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="s in selectedList()" :key="s.id">
                  <tr class="border-t">
                    <td class="p-2" x-text="s.id"></td>
                    <td class="p-2" x-text="s.name"></td>
                    <td class="p-2" x-text="labelOf(s.status)"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <!-- hidden inputs をここに差し込む -->
          <div x-ref="payload"></div>

          <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded bg-black text-white">確定する</button>
            <button type="button" class="px-4 py-2 rounded border" @click="clearAll">クリア</button>
          </div>
        </div>
      </form>

      @if($sheet)
        <div class="text-xs text-gray-500 mt-2">
          既存の出席簿が見つかりました（この画面は上書き保存します）。
        </div>
      @endif
    </div>
  </div>
</div>
</x-app-layout>
