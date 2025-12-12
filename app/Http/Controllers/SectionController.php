<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Services\SectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SectionController extends Controller
{
    public function index(Request $request, SectionService $sectionService)
    {
        $data = $sectionService->getSectionsList(
            $request->input('search'),
            $request->input('sort')
        );

        return Inertia::render('Sections/Index', $data);
    }

    public function create()
    {
        return Inertia::render('Sections/Create');
    }

    public function store(SectionRequest $request)
    {
        Auth::user()->sections()->create($request->validated());

        return redirect()->route('sections.index');
    }
}
