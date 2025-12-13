<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class StudentCourseController extends Controller
{
    /**
     * Display student courses page.
     */
    public function index(): Response
    {
        return Inertia::render('Student/Courses');
    }
}
