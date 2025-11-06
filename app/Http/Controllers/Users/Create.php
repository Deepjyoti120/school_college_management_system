<?php

namespace App\Http\Controllers\Users;

use App\Enums\BoardTypes;
use App\Enums\UserRole;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolClassSection;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Create extends Controller
{
    public function getFilterRoles(): array
    {
        $student = UserRole::STUDENT;
        $teacher = UserRole::TEACHER;
        return [
            [
                'label' => $student->label(),
                'value' => $student->value,
            ],
            [
                'label' => $teacher->label(),
                'value' => $teacher->value,
            ]
        ];
    }

    public function sections(Request $request, $class_id)
    {
        return SchoolClassSection::where('class_id', $class_id)
            ->get(['id', 'name'])
            ->map(fn($c) => [
                'label' => $c->name,
                'value' => $c->id,
            ]);
    }

    public function __invoke(Request $request, $user_id = null)
    {
        $school_id = auth()->user()->school_id;

        $user = $user_id
            ? User::where('school_id', $school_id)->findOrFail($user_id)
            : null;

        return Inertia::render('users/Create', [
            'roles' => self::getFilterRoles(),
            'boards' =>BoardTypes::options(),
            'classes' => SchoolClass::where('school_id', $school_id)
                ->get(['id', 'name'])
                ->map(fn($c) => [
                    'label' => $c->name,
                    'value' => $c->id,
                ]),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'dob' => optional($user->dob)->format('Y-m-d'),
                'doj' => optional($user->doj)->format('Y-m-d'),
                'phone' => $user->phone,
                'role' => $user->role,
                'board' => $user->board,
                'class_id' => $user->class_id,
                'section_id' => $user->section_id,
                'roll_number' => $user->roll_number,
            ] : null,
        ]);
    }
}
