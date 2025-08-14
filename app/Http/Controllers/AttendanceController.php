<?php

namespace App\Http\Controllers;

use App\Models\{Course, AttendanceSheet, AttendanceEntry};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public function create(Request $request, Course $course)
    {
        $date = $request->date
            ? \Carbon\Carbon::parse($request->date)->toDateString()
            : now()->toDateString();


        // 受講者一覧（軽量化）
        $students = $course->students()
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.id')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'email' => $s->email])
            ->values();


        // 既存出席簿（あれば）
        $sheet = AttendanceSheet::where('course_id', $course->id)->where('date', $date)->first();
        $existing = [];
        if ($sheet) {
            $existing = $sheet->entries()->pluck('status', 'user_id')->toArray(); // [user_id => status]
        }

        return view('attendance.create', compact('course', 'date', 'students', 'existing', 'sheet'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'entries' => ['nullable', 'array'],
            'entries.*.user_id' => ['required', 'integer'],
            'entries.*.status' => ['required', Rule::in(['present', 'partial', 'none'])],
            'wipe_all' => ['nullable', 'boolean'], // ★追加（hiddenフラグ）
        ]);

        $date = Carbon::parse($validated['date'])->toDateString();

        DB::transaction(function () use ($course, $date, $validated) {
            // シート取得/作成（この時点で確実に存在させる）
            $sheet = AttendanceSheet::firstOrCreate(
                ['course_id' => $course->id, 'date' => $date],
                ['locked' => false]
            );

            // ★ クリア要求が来ていたら該当日の全エントリを削除して終了
            if (!empty($validated['wipe_all'])) {
                AttendanceEntry::where('attendance_sheet_id', $sheet->id)->delete();
                return;
            }

            // 既存をロード（差分更新用）
            $current = AttendanceEntry::where('attendance_sheet_id', $sheet->id)
                ->pluck('id', 'user_id'); // Collection: [user_id => entry_id]

            $rows = $validated['entries'] ?? [];

            foreach ($rows as $row) {
                $userId = (int) $row['user_id'];
                $status = $row['status'];

                if ($status === 'none') {
                    if ($current->has($userId)) {
                        AttendanceEntry::whereKey($current->get($userId))->delete();
                        $current->forget($userId); // ★ ここがポイント
                    }
                    continue;
                }

                AttendanceEntry::updateOrCreate(
                    ['attendance_sheet_id' => $sheet->id, 'user_id' => $userId],
                    ['status' => $status]
                );

                $current->forget($userId); // ★ ここがポイント
            }

            // ★ 今回送られてこなかった人（= noneにした/初期状態に戻した人）を削除
            if ($current->isNotEmpty()) {
                AttendanceEntry::whereIn('id', $current->values())->delete();
            }

        });

        return back()->with('status', '出席簿を保存しました。');
    }

    public function overview(Request $request, Course $course)
    {

        // ① 年はクエリから。未指定なら「今の年」
        $year = (int)($request->query('year', now()->year));

        // ② その年の 1/1 から 12/31 を決定
        $yearStart = Carbon::create($year, 1, 1)->startOfDay();
        $yearEnd   = Carbon::create($year, 12, 31)->endOfDay();

        // ③ 1月の最初の日曜日（1/1 が日曜なら 1/1、それ以外なら次の日曜）
        $firstSunday = $yearStart->copy();
        if (!$firstSunday->isSunday()) {
            $firstSunday = $firstSunday->next(Carbon::SUNDAY);
        }

        // ④ 12/31 を含む最後の「列」は、その年の最後の日曜日（12/31 が日曜を過ぎていても、12/31 以前の最後の日曜）
        $lastSunday = $yearEnd->copy()->startOfWeek(Carbon::SUNDAY); // その週の日曜
        if ($lastSunday->lt($firstSunday)) {
            // 極端なケースの保険（通常は起きない）
            $lastSunday = $firstSunday->copy();
        }

        // ⑤ 週列（見出しは各週の「日曜」）
        $weeks = [];
        for ($ws = $firstSunday->copy(); $ws->lte($lastSunday); $ws->addWeek()) {
            $weeks[] = [
                'start' => $ws->toDateString(),               // 週のキー（日曜）
                'end'   => $ws->copy()->endOfWeek(Carbon::SATURDAY)->toDateString(),
                'label' => $ws->format('n/j'),                // 例: 1/7, 1/14, ...
                'key'   => $ws->toDateString(),
            ];
        }

        // ⑥ 受講生（ID昇順）
        $students = $course->students()
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.id')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'email' => $s->email])
            ->values();
        $studentIds = Arr::pluck($students, 'id');

        // ⑦ 対象年の出席レコードだけ取得（その年の 1/1〜12/31）
        $entries = \App\Models\AttendanceEntry::query()
            ->select('attendance_entries.user_id', 'attendance_entries.status', 'attendance_entries.attendance_sheet_id')
            ->whereIn('attendance_entries.user_id', $studentIds)
            ->whereHas('sheet', function ($q) use ($course, $yearStart, $yearEnd) {
                $q->where('course_id', $course->id)
                    ->whereBetween('date', [$yearStart->toDateString(), $yearEnd->toDateString()]);
            })
            ->with(['sheet:id,date'])
            ->get();

        // ⑧ 週×ユーザーの状態作成（優先度：present > partial > none）
        $priority = ['present' => 2, 'partial' => 1, 'none' => 0];

        $matrix = [];
        foreach ($students as $s) {
            $row = [];
            foreach ($weeks as $wk) {
                $row[$wk['key']] = 'none';
            }
            $matrix[$s['id']] = $row;
        }

        foreach ($entries as $e) {
            $d = Carbon::parse($e->sheet->date);
            $wsKey = $d->copy()->startOfWeek(Carbon::SUNDAY)->toDateString();
            if (!isset($matrix[$e->user_id][$wsKey])) continue;

            $cur = $matrix[$e->user_id][$wsKey] ?? 'none';
            if (($priority[$e->status] ?? 0) > ($priority[$cur] ?? 0)) {
                $matrix[$e->user_id][$wsKey] = $e->status;
            }
        }

        return view('attendance.overview', [
            'course'   => $course,
            'year'     => $year,
            // anchor は不要になったけど、画面に表示していたなら year の 1/最初の日曜を渡しておく
            'anchor'   => $firstSunday->toDateString(),
            'weeks'    => $weeks,
            'students' => $students,
            'matrix'   => $matrix,
        ]);
    }
}
