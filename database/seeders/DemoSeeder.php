<?php

namespace Database\Seeders;

use App\Models\{User, Course};
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder {
    public function run(): void {
        // 講師
        $teacher = User::factory()->create([
            'name' => '有田健一郎',
            'email' => 'ken.office.arita@gmail.com',
            'password' => Hash::make('password'),
        ]);

        // 受講者
        $students = User::factory(20)->create();

        // コース
        $course = Course::create([
            'name' => 'SiiD出席簿',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(4)->endOfMonth(),
        ]);

        // 紐づけ
        $course->instructors()->attach($teacher->id, ['role_in_course' => 'instructor']);
        foreach ($students as $s) {
            $course->students()->attach($s->id, ['role_in_course' => 'student']);
        }
    }
}
