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

        $user->is_active = $validated['is_active'];
        $user->save();
        return back()->with('success', 'User status updated successfully.');
    }
}
