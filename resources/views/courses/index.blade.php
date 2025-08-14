<x-app-layout>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">コース一覧</h1>
    <div class="overflow-auto border rounded">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="p-2 text-left w-16">ID</th>
            <th class="p-2 text-left">コース名</th>
            <th class="p-2 text-left">期間</th>
            <th class="p-2 text-left w-64">操作</th>
          </tr>
        </thead>
        <tbody>
          @foreach($courses as $c)
            <tr class="border-t">
              <td class="p-2">{{ $c->id }}</td>
              <td class="p-2">{{ $c->name }}</td>
              <td class="p-2">
                {{ optional($c->start_date)->format('Y-m-d') }} 〜 {{ optional($c->end_date)->format('Y-m-d') }}
              </td>
              <td class="p-2 space-x-2">
                <a class="underline" href="{{ route('attendance.create', $c) }}">出席登録</a>
                <a class="underline" href="{{ route('attendance.overview', $c) }}">出席一覧</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</x-app-layout>
