<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
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
        return Inertia::modal('Courses/Create', [
            'coverPhotos' => Course::getAvailableCoverPhotos(),
        ]);
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
            ? ['students' => Inertia::defer(fn () => $courseService->queryStudents($course, $request->only(['page']))), 'activities' => null]
            : ['activities' => Inertia::defer(fn () => $courseService->queryActivities($course, $request->only(['page']))), 'students' => null];

        return Inertia::render('Courses/Show', [
            ...$baseData,
            ...$tabData,
        ]);
    }

    public function edit(string $courseId)
    {
        $course = Auth::user()->courses()->findOrFail($courseId);

        return Inertia::modal('Courses/Edit', [
            'course' => $course->only(['id', 'name', 'access_code', 'cover_photo']),
            'coverPhotos' => Course::getAvailableCoverPhotos(),
        ]);
    }

    public function update(CourseRequest $request, string $courseId)
    {
        $course = Auth::user()->courses()->findOrFail($courseId);
        $course->update($request->validated());

        return redirect()->back();
    }

    public function removeStudent(string $courseId, int $studentId)
    {
        $course = Auth::user()->courses()->findOrFail($courseId);
        $course->students()->detach($studentId);

        return back();
    }

    public function destroy(string $courseId)
    {
        $course = Auth::user()->courses()->findOrFail($courseId);
        $course->delete();

        return redirect()->route('courses.index');
    }
}
