<?php

namespace App\Http\Controllers;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\FeeGenerate;
use App\Models\FeeStructure;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\OrderProgress;
use App\Models\Product;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $fees = FeeGenerate::query()->with(['school'])
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->whereHas('product', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('fee/Index', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'status']),
            'feeTypes' => FeeType::options(),
        ]);
    }
    public function feeStructure(Request $request)
    {
        $fees = FeeStructure::query()->with(['school'])
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->whereHas('product', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('fee/IndexStructure', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'status']),
            'feeTypes' => FeeType::options(),
            'frequency' => FrequencyType::options(),
        ]);
    }
    public function feeGenerate(Request $request)
    {
        // try {
        //   Carbon::setTestNow(Carbon::create(2027, 5, 15));
        if (!in_array($request->type, FeeType::values(), true)) {
            return back()->with('error', 'Fee type is required');
        }
        $academicYear = AcademicYear::getOrCreateCurrentAcademicYear(auth()->user()->school_id);
        // if (!$academicYear) {
        //     $lastYear = AcademicYear::latest('end_date')->first();
        //     if ($lastYear) {
        //         $startDate = Carbon::parse($lastYear->end_date)->addDay();
        //         $endDate   = $startDate->copy()->addYear()->subDay();
        //     } else {
        //         $startDate = Carbon::create(null, 3, 1);
        //         $endDate   = $startDate->copy()->addYear()->subDay();
        //     }
        //     $academicYear = AcademicYear::create([
        //         'name' => $startDate->format('Y') . '-' . $endDate->format('Y'),
        //         'start_date' => $startDate,
        //         'end_date' => $endDate,
        //         'is_current' => true,
        //         'school_id' => auth()->user()->school_id,
        //     ]);
        // }
        if ($request->type === FeeType::ADMISSION->value) {
            $exists = FeeGenerate::where('academic_year_id', $academicYear->id)
                ->where('type', FeeType::ADMISSION)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Admission fee already generated for this academic year.');
            }

            FeeGenerate::create([
                'school_id' => auth()->user()->school_id,
                'academic_year_id' => $academicYear->id,
                'month' => $academicYear->start_date->month,
                'year' => $academicYear->start_date->year,
                'type' => FeeType::ADMISSION,
            ]);
        } else {
            $exists = FeeGenerate::where('academic_year_id', $academicYear->id)
                ->where('type', FeeType::MONTHLY)
                ->exists();

            if ($exists) {
                return back()->with('error', 'Monthly fee already generated for this academic year.');
            }
            $startMonth = Carbon::parse($academicYear->start_date);
            $endMonth   = Carbon::parse($academicYear->end_date);
            for ($date = $startMonth->copy(); $date->lte($endMonth); $date->addMonth()) {
                $exists = FeeGenerate::where('academic_year_id', $academicYear->id)
                    ->where('month', $date->format('m'))
                    ->where('year', $date->format('Y'))
                    ->where('type', $request->type)
                    ->exists();
                if (!$exists) {
                    FeeGenerate::create([
                        'school_id' => auth()->user()->school_id,
                        'academic_year_id' => $academicYear->id,
                        'month' => $date->format('m'),
                        'year' => $date->format('Y'),
                        'type' => $request->type,
                    ]);
                }
            }
        }
        return back()->with('success', 'Fee Generated Successfully');
        // } catch (\Exception $e) {
        //     return back()->with('error', 'An error occurred: ' . $e->getMessage());
        // }
    }


    public function feeCreate()
    {
        $schoolId = auth()->user()->school_id;
        return Inertia::render('fee/Create', [
            'classes' => SchoolClass::where('school_id', $schoolId)
                ->get(['id', 'name'])
                ->map(fn($c) => ['label' => $c->name, 'value' => $c->id]),
            'feeTypes' => collect(FeeType::cases())->map(fn($t) => [
                'label' => $t->label(),
                'value' => $t->value,
            ]),
            'frequencyTypes' => collect(FrequencyType::cases())->map(fn($f) => [
                'label' => $f->label(),
                'value' => $f->value,
            ]),
        ]);
    }

    public function feeStore(Request $request)
    {
        $validated = $request->validate([
            'class_id'=> ['required', 'exists:school_classes,id'],
            'name'=> ['required', 'string', 'max:255'],
            'type'=> ['required', 'in:' . implode(',', FeeType::values())],
            'amount'=> ['required', 'numeric', 'min:0'],
            'frequency'=> ['required', 'in:' . implode(',', FrequencyType::values())],
            'description'=> ['nullable', 'string', 'max:1000'],
        ]);
        $academicYear = AcademicYear::getOrCreateCurrentAcademicYear(auth()->user()->school_id);

        FeeStructure::create([
            'school_id' => auth()->user()->school_id,
            'academic_year_id' => $academicYear->id,
            'class_id' => $validated['class_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'amount'=> $validated['amount'],
            'frequency'=> $validated['frequency'],
            'description'=> $validated['description'] ?? null,
        ]);
        return back()->with('success', 'Successfully Saved.');
    }

    public function approve(Request $request, Order $order)
    {
        // try {
        $countryCode = $request->input('country_code', '+91');
        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
            'vehicle_number' => [
                Rule::requiredIf($order->new_status === OrderStatus::DISPATCHED),
                'nullable',
                'string',
            ],
            'driver_phone' => [
                Rule::requiredIf($order->new_status === OrderStatus::DISPATCHED),
                'nullable',
                'digits:10',
                Rule::unique('orders')->where(function ($query) use ($countryCode) {
                    return $query->where('country_code', $countryCode);
                }),
            ],
        ]);
        // $order->new_status == OrderStatus::DISPATCHED;
        DB::transaction(function () use ($validated, $order) {
            OrderProgress::create([
                'order_id' => $order->id,
                'updated_by' => auth()->id(),
                'stage' => $order->stage + 1,
                'status' => $order->new_status,
                'title' => 'Order ' . $order->new_status->value,
                'remarks' => $validated['remarks'],
            ]);
            $order->update([
                ...$validated,
                'stage' => $order->stage + 1,
                'status' => $order->new_status,
                'updated_by' => auth()->id(),
            ]);
        });
        return back()->with('success', 'Successfully Saved.');
        // } catch (\Exception $e){
        //     dd($e->getMessage());
        // }
    }

    public function reject(Request $request, Order $order)
    {
        $validated = $request->validate([
            'remarks' => ['required', 'string'],
        ]);
        DB::transaction(function () use ($validated, $order) {
            OrderProgress::create([
                ...$validated,
                'order_id' => $order->id,
                'updated_by' => auth()->id(),
                'stage' => $order->stage + 1,
                'status' => OrderStatus::REJECTED,
                'title' => 'Order Rejected',
                // 'remarks' => $validated['remarks'],
            ]);
            $order->update([
                'stage' => $order->stage + 1,
                'status' => OrderStatus::REJECTED,
                'updated_by' => auth()->id(),
            ]);
        });
        return back()->with('success', 'Successfully Saved.');
    }
}
