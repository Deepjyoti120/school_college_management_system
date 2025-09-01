<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class ToggleStatusController extends Controller
{
    public function __invoke(Request $request, Category $category)
    {
       
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $category->is_active = $validated['is_active'];
        $category->save();
        return back()->with('success', 'Category status updated successfully.');
    }
}
