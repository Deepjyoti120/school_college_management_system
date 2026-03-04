<?php

namespace App\Http\Controllers\Users;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'class_id' => ['required_unless:role,teacher', 'nullable', 'exists:school_classes,id'],
            'section_id' => ['required_unless:role,teacher', 'nullable', 'exists:school_class_sections,id'],
            'roll_number' => ['required_unless:role,teacher', 'nullable', 'string'],
            'subject_ids' => ['required_if:role,' . UserRole::STUDENT->value, 'array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ];

        // Password required only on create
        if (!$isEdit) {
            $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
        } elseif ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);
        $schoolId = auth()->user()->school_id;
        $subjectIds = $validated['subject_ids'] ?? [];
        unset($validated['subject_ids']);

        if (($validated['role'] ?? null) === UserRole::STUDENT->value) {
            $subjectCount = Subject::query()
                ->where('school_id', $schoolId)
                ->where('class_id', $validated['class_id'])
                ->whereIn('id', $subjectIds)
                ->count();

            if ($subjectCount !== count($subjectIds)) {
                return back()->withErrors([
                    'subject_ids' => 'Selected subjects are invalid for the selected class.',
                ])->withInput();
            }
        }

        $message = DB::transaction(function () use (
            $isEdit,
            $userId,
            $request,
            $validated,
            $subjectIds,
            $schoolId
        ) {
            $payload = $validated;

            if ($isEdit) {
                $user = User::findOrFail($userId);
                if ($request->filled('password')) {
                    $payload['password'] = bcrypt($request->password);
                }
                $user->update($payload);
                if (($payload['role'] ?? null) === UserRole::STUDENT->value) {
                    $user->subjects()->sync($subjectIds);
                } else {
                    $user->subjects()->detach();
                }
                return 'User updated successfully.';
            } else {
                $payload['country_code'] = '+91';
                $payload['school_id'] = $schoolId;
                $payload['is_active'] = false;
                $user = User::create($payload);
                if (($payload['role'] ?? null) === UserRole::STUDENT->value) {
                    $user->subjects()->sync($subjectIds);
                }
                return 'User created successfully.';
            }
        });

        return redirect()->route('users.index')->with('success', $message);
    }
}
