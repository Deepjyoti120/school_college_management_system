<?php

namespace App\Http\Controllers;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use App\Enums\OrderStatus;
use App\Enums\RazorpayPaymentStatus;
use App\Enums\UserRole;
use App\Helpers\ApiResponse;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Discount;
use App\Models\FeeGenerate;
use App\Models\FeeStructure;
use App\Models\Holiday;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\OrderProgress;
use App\Models\Product;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AttendanceController extends Controller
{

    public function classesAtendances(Request $request)
    {
        $user = auth()->user();
        $schoolClasses = SchoolClass::query()->with(['school', 'students'])
            ->where('school_id',  $user->school_id)
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('attendance/ClassIndex', [
            'schoolClasses' => $schoolClasses,
            'filters' => $request->only(['search']),
            'canCreate' => UserRole::PRINCIPAL === $user->role,
        ]);
    }
    public function index(Request $request, SchoolClass $schoolClass, $school_id)
    {
        $user = auth()->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $academicYearId = $request->input('academic_year_id');
        $attendances = Attendance::with(['user'])
            ->where('school_id', $school_id)
            ->where('class_id', $schoolClass->id)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();
        $users = $attendances->groupBy('user_id')->map(function ($records) {
            $user = $records->first()->user;
            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'attendances' => $records->map(fn($a) => [
                    'date' => $a->date->format('Y-m-d'),
                    'status' => $a->status->value,
                    'status_label' => $a->status_label,
                    'status_color' => $a->status_color,
                ]),
            ];
        })->values();
        $daysInMonth = collect(range(1, Carbon::create($year, $month)->daysInMonth))->map(fn($day) => [
            'day' => Carbon::create($year, $month, $day)->format('D'),
            'date' => Carbon::create($year, $month, $day)->format('Y-m-d'),
            'day_number' => $day,
        ]);
        $academicYears = AcademicYear::where('school_id', $user->school_id)->get();
        return Inertia::render('attendance/Index', [
            'users' => $users,
            'days' => $daysInMonth,
            'filters' => [
                'month' => $month,
                'year' => $year,
                'academic_year_id' => $academicYearId,
            ],
            'academicYears' => $academicYears,
            'canCreate' => in_array($user->role, [UserRole::PRINCIPAL, UserRole::ADMIN]),
        ]);
    }
    public function teachersAttendance(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $academicYearId = $request->input('academic_year_id');
        $attendances = Attendance::with(['user'])
            ->where('school_id', $user->school_id)
            ->where('role',UserRole::TEACHER)
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();
        $users = $attendances->groupBy('user_id')->map(function ($records) {
            $user = $records->first()->user;
            return [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'attendances' => $records->map(fn($a) => [
                    'date' => $a->date->format('Y-m-d'),
                    'status' => $a->status->value,
                    'status_label' => $a->status_label,
                    'status_color' => $a->status_color,
                ]),
            ];
        })->values();
        $daysInMonth = collect(range(1, Carbon::create($year, $month)->daysInMonth))->map(fn($day) => [
            'day' => Carbon::create($year, $month, $day)->format('D'),
            'date' => Carbon::create($year, $month, $day)->format('Y-m-d'),
            'day_number' => $day,
        ]);
        $academicYears = AcademicYear::where('school_id', $user->school_id)->get();
        return Inertia::render('attendance/TeacherAttendance', [
            'users' => $users,
            'days' => $daysInMonth,
            'filters' => [
                'month' => $month,
                'year' => $year,
                'academic_year_id' => $academicYearId,
            ],
            'academicYears' => $academicYears,
            'canCreate' => in_array($user->role, [UserRole::PRINCIPAL, UserRole::ADMIN]),
        ]);
    }


    public function create()
    {
        return Inertia::render('attendance/Create');
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
        ]);
        if (config('services.demo.mode')) {
            return back()->with('error', 'This action is not allowed in demo mode');
        }
        $academicYear = AcademicYear::getOrCreateCurrentAcademicYear(auth()->user()->school_id);
        $year = null;
        $month = null;
        if (!empty($validated['month'])) {
            [$year, $month] = explode('-', $validated['month']);
        }
        Holiday::create([
            'school_id' => auth()->user()->school_id,
            'academic_year_id' => $academicYear->id,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'date' => $validated['date'],
        ]);
        return back()->with('success', 'Successfully Saved.');
    }
    public function activeToggle(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);
        if (config('services.demo.mode')) {
            return back()->with('error', 'This action is not allowed in demo mode');
        }
        $holiday->is_active = $validated['is_active'];
        $holiday->save();
        return back()->with('success', 'Status updated successfully.');
    }
}
