<?php

namespace App\Http\Controllers\Category;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function __invoke(Request $request)
    {
        $categories = Category::query()
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(slug) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(description) LIKE ?', ["%{$search}%"]);
                });
            })->orderBy('created_at')
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('category/Index', [
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }
}
