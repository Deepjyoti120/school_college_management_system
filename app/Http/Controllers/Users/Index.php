<?php

namespace App\Http\Controllers\Users;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function __invoke(Request $request)
    {
        $users = User::query()
            // ->when($request->search, function ($q) use ($request) {
            //     $search = strtolower($request->search);
            //     $q->where(function ($q) use ($search) {
            //         $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
            //             ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
            //     });
            // })
            ->when($request->search, function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            })
            ->where('id', '!=', auth()->id())
            ->whereIn('role', UserRole::allowedForUser(auth()->user()->role))
            ->when($request->role && $request->role !== 'all', fn($q) => $q->where('role', $request->role))
            ->orderBy('created_at')
            ->paginate(10)
            ->withQueryString();
        // sleep(1);
        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
            'roles' => UserRole::optionsForUser(auth()->user()->role),
            'canUserCreate' => UserRole::PRINCIPAL === auth()->user()->role,
        ]);
    }
}
