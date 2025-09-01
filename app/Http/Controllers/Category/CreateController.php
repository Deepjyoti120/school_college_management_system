<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CreateController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:categories,slug',
                'regex:/^[a-z0-9\-]+$/',
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['required', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('categories', 'public');
            $validated['icon'] = $path;
        }
        Category::create($validated);
        return back()->with('success', 'Category created successfully.');
    }
}
