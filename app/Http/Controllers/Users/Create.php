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
        $role = UserRole::DEL;

        return [[
            'label' => $role->label(),
            'value' => $role->value,
        ]];
    }
    public function __invoke(Request $request)
    {
        // $users = User::query()
        //     ->when($request->search, function ($q) use ($request) {
        //         $search = strtolower($request->search);
        //         $q->where(function ($q) use ($search) {
        //             $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
        //                 ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
        //         });
        //     })
        //     ->where('id', '!=', auth()->id())
        //     ->when($request->role && $request->role !== 'all', fn($q) => $q->where('role', $request->role))
        //     ->paginate(10)
        //     ->withQueryString();
        //     // sleep(1);
        return Inertia::render('users/Create', [
            // 'users' => $users,
            // 'filters' => $request->only(['search', 'role']),
            'roles' => self::getFilterRoles(),
            // 'roles' => UserRole::options(),
        ]);
    }
}
