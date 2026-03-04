<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $canCreate = $this->canManage();
        $subjects = Subject::query()
            ->with(['class:id,name'])
            ->where('school_id', $schoolId)
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->where(function ($subQuery) use ($search) {
                    $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(code) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('class', function ($classQuery) use ($search) {
                            $classQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        });
                });
            })
            ->when($request->class_id && $request->class_id !== 'all', fn($q) => $q->where('class_id', $request->class_id))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('subject/Index', [
            'subjects' => $subjects,
            'filters' => $request->only(['search', 'class_id']),
            'classes' => SchoolClass::query()
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($class) => [
                    'label' => $class->name,
                    'value' => $class->id,
                ]),
            'canCreate' => $canCreate,
        ]);
    }

    public function create()
    {
        if (!$this->canManage()) {
            abort(403);
        }

        $schoolId = auth()->user()->school_id;
        return Inertia::render('subject/Create', [
            'classes' => SchoolClass::query()
                ->where('school_id', $schoolId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn($class) => [
                    'label' => $class->name,
                    'value' => $class->id,
                ]),
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->canManage()) {
            abort(403);
        }

        $schoolId = auth()->user()->school_id;
        $validated = $request->validate([
            'class_id' => [
                'required',
                Rule::exists('school_classes', 'id')->where(fn($q) => $q->where('school_id', $schoolId)),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects', 'name')->where(fn($q) => $q
                    ->where('school_id', $schoolId)
                    ->where('class_id', $request->input('class_id'))),
            ],
            'code' => ['nullable', 'string', 'max:50'],
        ]);

        if (config('services.demo.mode')) {
            return back()->with('error', 'This action is not allowed in demo mode');
        }

        Subject::create([
            'school_id' => $schoolId,
            'class_id' => $validated['class_id'],
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('subjects.index')->with('success', 'Subject created successfully.');
    }

    public function toggle(Request $request, Subject $subject)
    {
        if (!$this->canManage()) {
            abort(403);
        }

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ($subject->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        if (config('services.demo.mode')) {
            return back()->with('error', 'This action is not allowed in demo mode');
        }

        $subject->update([
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Subject status updated successfully.');
    }

    private function canManage(): bool
    {
        return in_array(auth()->user()->role, [UserRole::SUPER_ADMIN, UserRole::PRINCIPAL], true);
    }
}
