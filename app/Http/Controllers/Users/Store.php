<?php

namespace App\Http\Controllers\Users;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class Store extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function __invoke(Request $request)
    {
        // try {
        $countryCode = $request->input('country_code', '+91');
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'role' => ['required', 'string'],
            'dob' => ['required', 'date'],
            'doj' => ['required', 'date'],
            'email' => ['required', 'email', 'unique:users,email'],
            // 'phone' => ['required', 'digits:10', 'unique:users,phone'],
            'phone' => [
                'required',
                'digits:10',
                // Rule::unique('users')->where(function ($query) use ($countryCode) {
                //     return $query->where('country_code', $countryCode);
                // }),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:school_class_sections,id'],
            'roll_number' => ['required', 'string'],
        ]);
        $validated['country_code'] = $countryCode;
        User::create([
            ...$validated,
            'is_active' => false,
            'school_id' => auth()->user()->school_id,
        ]);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
        // } catch (\Exception $e) {
        //     dd($e->getMessage());
        // }
    }
}
