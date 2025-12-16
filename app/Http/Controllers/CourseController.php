<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request, CourseService $courseService)
    {
        $data = $courseService->getCoursesList(
            $request->input('search'),
            $request->input('sort')
        );

        return Inertia::render('Courses/Index', $data);
    }

    public function create()
    {
        return Inertia::render('Courses/Create');
    }

    public function store(CourseRequest $request)
    {
        Auth::user()->courses()->create($request->validated());

        return redirect()->route('courses.index');
    }

    public function show(string $courseId, Request $request, CourseService $courseService)
    {
        $course = Auth::user()->courses()
            ->selectedAttributes()
            ->with('user:id,name')
            ->findOrFail($courseId);

        $activeTab = $request->get('tab', 'activities');

        $baseData = [
            'course' => $course,
            'activeTab' => $activeTab,
        ];

        $tabData = $activeTab === 'students'
            ? ['students' => Inertia::defer(fn() => $courseService->queryStudents($course, $request->only(['page']))), 'activities' => null]
            : ['activities' => Inertia::defer(fn() => $courseService->queryActivities($course, $request->only(['page']))), 'students' => null];

        return Inertia::render('Courses/Show', [
            ...$baseData,
            ...$tabData,
        ]);
    }

    public function removeStudent(string $courseId, int $studentId)
    {
        $course = Auth::user()->courses()->findOrFail($courseId);
        $course->students()->detach($studentId);

        return back();
    }
}
