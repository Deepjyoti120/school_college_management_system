<?php

namespace App\Http\Controllers\Users;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Create extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function getFilterRoles(): array
    {
        $role = UserRole::STUDENT;
        return [[
            'label' => $role->label(),
            'value' => $role->value,
        ]];
    }
    public function __invoke(Request $request)
    {
        return Inertia::render('users/Create', [
            'roles' => self::getFilterRoles(),
        ]);
    }
}
