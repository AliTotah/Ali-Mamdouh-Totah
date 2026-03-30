<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('student_id'); // رقم الطالب مرتبط بجدول students
            $table->string('course_code');    // رمز المادة
            $table->string('course_name');    // اسم المادة
            $table->string('day')->nullable();       // اليوم
            $table->string('time')->nullable();      // الوقت
            $table->string('room')->nullable();      // القاعة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_courses');
    }
};