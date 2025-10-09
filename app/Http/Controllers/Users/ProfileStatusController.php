<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileStatusController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        return Inertia::render('users/Profile', [
            'user' => $user->loadMissing(['class', 'section', 'school']),
        ]);
    }
}
