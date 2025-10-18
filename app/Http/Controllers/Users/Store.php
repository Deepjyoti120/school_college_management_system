<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class Store extends Controller
{
    public function __invoke(Request $request)
    {
        $userId = $request->input('id');
        $isEdit = !empty($userId);

        $rules = [
            'name' => ['required', 'string'],
            'role' => ['required', 'string'],
            'board' => ['nullable', 'string'],
            'dob' => ['required', 'date'],
            'doj' => ['required', 'date'],
            'email' => ['required', 'email', 'unique:users,email' . ($isEdit ? ',' . $userId : '')],
            'phone' => ['required', 'digits:10'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:school_class_sections,id'],
            'roll_number' => ['required', 'string'],
        ];

        // Password required only on create
        if (!$isEdit) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        } elseif ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        if ($isEdit) {
            $user = User::findOrFail($userId);
            if ($request->filled('password')) {
                $validated['password'] = bcrypt($request->password);
            }
            $user->update($validated);
            $message = 'User updated successfully.';
        } else {
            $validated['country_code'] = '+91';
            $validated['school_id'] = auth()->user()->school_id;
            $validated['is_active'] = false;
            User::create($validated);
            $message = 'User created successfully.';
        }

        return redirect()->route('users.index')->with('success', $message);
    }
}
