<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Student;
use App\Models\StudentCourse;

class ApiController extends Controller
{
    // دالة تسجيل الدخول وتخزين الطالب
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $response = Http::post('https://quiztoxml.ucas.edu.ps/api/login', [
            'username' => $request->username,
            'password' => $request->password,
        ]);

        $data = $response->json();

        if (!isset($data['success']) || $data['success'] != true) {
            return response()->json([
                'message' => 'كلمة المرور او اسم المستخدم خطا'
            ], 401);
        }

        $student_id = $data['data']['user_id'] ?? null;
        $name = $data['data']['user_en_name'] ?? null;
        $token = $data['Token'] ?? null;

        // حفظ الطالب
        Student::updateOrCreate(
            ['student_id' => $student_id],
            ['name' => $name, 'token' => $token]
        );

        return response()->json([
            'message' => 'Login successful',
            'student' => [
                'id' => $student_id,
                'name' => $name,
                'token' => $token
            ]
        ]);
    }

public function getTable(Request $request)
{
    // التحقق من وجود الرقم الجامعي
    $request->validate([
        'student_id' => 'required|integer'
    ]);

    // جلب الطالب من قاعدة البيانات
    $student = Student::where('student_id', $request->student_id)->first();

    if (!$student) {
        return response()->json([
            'message' => 'الطالب غير موجود'
        ], 404);
    }

    if (!$student->token) {
        return response()->json([
            'message' => 'الطالب لا يملك توكن صالح'
        ], 401);
    }

    // استدعاء API جدول الدراسة
    $response = Http::post('https://quiztoxml.ucas.edu.ps/api/get-table', [
        'user_id' => $student->student_id,
        'token' => $student->token
    ]);

    $data = $response->json();

    // تحقق من نجاح الرد
    if (!isset($data['success']) || $data['success'] != true) {
        return response()->json([
            'message' => 'فشل جلب الجدول الدراسي',
            'api_response' => $data
        ], 401);
    }

    // جلب المواد الدراسية بأمان
    $courses = $data['data'] ?? [];
    if (!is_array($courses)) {
        $courses = [];
    }

    // حذف المواد السابقة لتجنب التكرار
    StudentCourse::where('student_id', $student->student_id)->delete();

    // حفظ المواد الجديدة
    foreach ($courses as $course) {
        StudentCourse::create([
            'student_id' => $student->student_id,
            'course_code' => $course['course_code'] ?? null,
            'course_name' => $course['course_name'] ?? null,
            'day' => $course['day'] ?? null,
            'time' => $course['time'] ?? null,
            'room' => $course['room'] ?? null,
        ]);
    }

    return response()->json([
        'message' => 'تم حفظ الجدول الدراسي بنجاح',
        'courses' => $courses
    ]);
}
 public function showTable(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer'
        ]);

        // جلب الطالب
        $student = Student::where('student_id', $request->student_id)->first();

        if (!$student) {
            return response()->json([
                'message' => 'الطالب غير موجود'
            ], 404);
        }

        // جلب جدول الطالب
        $courses = StudentCourse::where('student_id', $student->student_id)
            ->orderBy('day')
            ->orderBy('time')
            ->get(['course_code', 'course_name', 'day', 'time', 'room', 'lecturer']);

        return response()->json([
            'student_id' => $student->student_id,
            'student_name' => $student->name,
            'courses' => $courses
        ]);
    }
}