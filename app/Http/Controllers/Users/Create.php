<?php

namespace App\Http\Controllers\Users;

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
    /**
     * Display a listing of the users.
     */
    public function getFilterRoles(): array
    {
        $student = UserRole::STUDENT;
        // $student = UserRole::TEACHER;
        return [
            [
                'label' => $student->label(),
                'value' => $student->value,
            ],
        ];
    }

    public function sections(Request $request, $class_id)
    {
        $sections = SchoolClassSection::where('class_id', $class_id)
            ->get(['id', 'name'])
            ->map(fn($c) => [
                'label' => $c->name,
                'value' => $c->id,
            ]);
        // return ApiResponse::success($sections);
        return $sections;
    }


    public function __invoke(Request $request)
    {
        $school_id = auth()->user()->school_id;
        return Inertia::render('users/Create', [
            'roles' => self::getFilterRoles(),
            'classes' =>  SchoolClass::where('school_id', $school_id)->get(['id', 'name'])->map(fn($c) => [
                'label' => $c->name,
                'value' => $c->id,
            ]),
        ]);
    }
}
