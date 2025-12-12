<?php

namespace App\Services;

use App\Models\Section;
use Illuminate\Support\Facades\Auth;

class SectionService
{
    public function getSectionsList(?string $search = null, ?string $sort = null): array
    {
        return [
            'sections' => fn () => Section::where('user_id', Auth::id())
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('access_code', 'like', '%'.$search.'%');
                })
                ->selectedAttributes()
                ->when($sort, function ($query) use ($sort) {
                    match ($sort) {
                        'name_asc' => $query->orderBy('name', 'asc'),
                        'name_desc' => $query->orderBy('name', 'desc'),
                        'oldest' => $query->oldest(),
                        default => $query->latest(),
                    };
                }, function ($query) {
                    $query->latest();
                })
                ->paginate(9)
                ->withQueryString(),
            'filters' => [
                'search' => $search ?? '',
                'sort' => $sort ?? 'newest',
            ],
        ];
    }
}
