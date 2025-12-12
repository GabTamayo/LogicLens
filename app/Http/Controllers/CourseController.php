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
}
