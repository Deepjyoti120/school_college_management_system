<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class ToggleStatusController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        if (config('services.demo.mode')) {
            return back()->with('error', 'This action is not allowed in demo mode');
        }
        $user->is_active = $validated['is_active'];
        $user->save();
        return back()->with('success', 'User status updated successfully.');
    }
}
