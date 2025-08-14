<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ProfileController;
use App\Models\Course;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 出席登録画面
    Route::get('/courses/{course}/attendance', [AttendanceController::class, 'create'])
        ->name('attendance.create');

    // 登録確定（保存）
    Route::post('/courses/{course}/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    // 一覧画面
    Route::get('/courses/{course}/attendance/overview', [AttendanceController::class, 'overview'])
        ->name('attendance.overview');

    // 追加: コース一覧
    // Route::get('/courses', function () {
    //     $courses = Course::select('id','name','start_date','end_date')->orderBy('id')->get();
    //     return view('courses.index', compact('courses'));
    // })->name('courses.index');

    // マイページ（仮）
    Route::view('/mypage', 'mypage')->name('mypage');

    // 出席登録（単一コース前提で最初のコースへ）
    Route::get('/attendance/register', function () {
        $course = Course::select('id')->firstOrFail();
        return redirect()->route('attendance.create', $course);
    })->name('attendance.register');

    // 出席一覧（年パラメータはあれば引き継ぐ）
    Route::get('/attendance/list', function () {
        $course = Course::select('id')->firstOrFail();
        $year = request('year') ?? now()->year;
        return redirect()->route('attendance.overview', ['course' => $course, 'year' => $year]);
    })->name('attendance.list');
});

require __DIR__ . '/auth.php';
