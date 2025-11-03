<?php

namespace App\Http\Controllers;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use App\Enums\OrderStatus;
use App\Enums\RazorpayPaymentStatus;
use App\Enums\UserRole;
use App\Helpers\ApiResponse;
use App\Models\AcademicYear;
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

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $holidays = Holiday::query()->with(['school'])
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                // $q->whereHas('product', function ($query) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                // });
            })
            // ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        $user = auth()->user();
        return Inertia::render('holiday/Index', [
            'holidays' => $holidays,
            'filters' => $request->only(['search']),
            'canCreate' => UserRole::PRINCIPAL === $user->role,
        ]);
    }

    public function create()
    {
        return Inertia::render('holiday/Create');
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
