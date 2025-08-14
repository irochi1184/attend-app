<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('attendance_sheet_id')->constrained()->cascadeOnDelete();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete(); // 受講者
            $t->enum('status', ['present','absent','late','remote','excused'])
            ->default('present'); // 出席, 欠席, 遅刻, リモート, 公欠
            $t->timestamps();
            $t->unique(['attendance_sheet_id','user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_entries');
    }
};
