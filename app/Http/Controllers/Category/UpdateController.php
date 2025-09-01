<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function __invoke(Request $request, Category $category)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category->id),
                'regex:/^[a-z0-9\-]+$/',
            ],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'image', 'mimes:jpeg,png,jpg,svg,webp', 'max:2048'],
        ]);
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        if ($request->hasFile('icon') && $request->file('icon')->isValid()) {
            $file = $request->file('icon');
            $originalName = strtolower($file->getClientOriginalName());
            $extension = strtolower($file->getClientOriginalExtension());
            if ($originalName !== 'blob' && $extension !== '') {
                if ($category->icon) {
                    Storage::disk('public')->delete($category->icon);
                }
                $validated['icon'] = $file->store('categories', 'public');
            } else {
                unset($validated['icon']);
            }
        } elseif ($request->has('icon') && $request->input('icon') === null) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = null;
        } else {
            unset($validated['icon']);
        }
        $category->update($validated);
        return back()->with('success', 'Category updated successfully.');
    }
}
