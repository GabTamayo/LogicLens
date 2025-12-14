<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseEnrollmentRequest;
use App\Services\StudentCourseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StudentCourseController extends Controller
{
    public function index(Request $request, StudentCourseService $service): Response
    {
        $data = $service->getEnrolledCourses(
            $request->input('search'),
            $request->input('sort')
        );

        return Inertia::render('Student/Courses', $data);
    }

    public function enrollCourse()
    {
        return Inertia::modal('Student/EnrollCourse');
    }

    public function storeEnrollment(CourseEnrollmentRequest $request, StudentCourseService $service)
    {
        $result = $service->enrollInCourse($request->validated('access_code'));

        if (! $result['success']) {
            return back()->withErrors([
                'access_code' => $result['error'],
            ]);
        }

        return redirect()->route('student.courses.index')->with('success', $result['message']);
    }
}
