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
        $schoolId = auth()->user()->school_id;
        $defaultAcademicYear = AcademicYear::getOrCreateCurrentAcademicYear($schoolId);
        $request->academicYear = $request->academicYear ?? $defaultAcademicYear->id;
        $fees = FeeStructure::query()
            ->with(['school', 'class'])
            ->withCount(['users as user_count'])
            ->withSum(['payments as total_paid' => function ($q) {
                $q->where('status', RazorpayPaymentStatus::PAID);
            }], 'total_amount')
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            })
            ->when($request->feeType && $request->feeType !== 'all', fn($q) => $q->where('type', $request->feeType))
            ->when($request->academicYear, fn($q) => $q->where('academic_year_id', $request->academicYear))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->through(function ($fee) {
                $fee->total_payable = $fee->total_amount * $fee->user_count;
                $fee->total_paid = $fee->total_paid ?? 0;
                $fee->pending_amount = $fee->total_payable - $fee->total_paid;
                return $fee;
            })
            ->withQueryString();
        return Inertia::render('fee/IndexStructure', [
            'fees' => $fees,
            'filters' => [
                'search',
                'feeType' => $request->feeType,
                'academicYear' => (string) $request->academicYear,
            ],
            'roles' => UserRole::optionsForUser(auth()->user()->role),
            'feeTypes' => FeeType::options(),
            'frequency' => FrequencyType::options(),
            'academicYears' => AcademicYear::where('school_id', $schoolId)
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name'])->map(fn($m) => [
                    'label' => $m->name,
                    'value' => $m->id,
                ])
        ]);
    }
    public function feeToggle(Request $request, FeeStructure $fee)
    {

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $fee->is_active = $validated['is_active'];
        $fee->save();
        return back()->with('success', 'FeeStructure updated successfully.');
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

        $feeTypeFrequencyMap = [
            FeeType::ADMISSION->value => [FrequencyType::ONE_TIME->value],
            FeeType::MONTHLY->value => [FrequencyType::MONTHLY->value, FrequencyType::QUARTERLY->value],
        ];

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
            'feeTypeFrequencyMap' => $feeTypeFrequencyMap,
        ]);
    }

    public function feeStore(Request $request)
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:' . implode(',', FeeType::values())],
            'amount' => ['required', 'numeric', 'min:100'],
            'frequency' => ['required', 'in:' . implode(',', FrequencyType::values())],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        if (config('services.demo.mode')) {
            return back()->with('error', 'This action is not allowed in demo mode');
        }
        $schoolId = auth()->user()->school_id;
        $academicYear = AcademicYear::getOrCreateCurrentAcademicYear($schoolId);
        $feeType = FeeType::from($validated['type']);
        $frequency = FrequencyType::from($validated['frequency']);
        $allowedFrequencyByType = match ($feeType) {
            FeeType::ADMISSION => [FrequencyType::ONE_TIME],
            FeeType::MONTHLY => [FrequencyType::MONTHLY, FrequencyType::QUARTERLY],
        };
        if (!in_array($frequency, $allowedFrequencyByType, true)) {
            return back()->withErrors([
                'frequency' => 'Selected frequency is not allowed for the selected fee type.',
            ])->withInput();
        }

        $startMonth = $academicYear->start_date->copy()->startOfMonth();
        $schedule = match ($frequency) {
            FrequencyType::ONE_TIME, FrequencyType::YEARLY => [$startMonth->copy()],
            FrequencyType::QUARTERLY => collect(range(0, 3))
                ->map(fn($index) => $startMonth->copy()->addMonths($index * 3))
                ->all(),
            FrequencyType::MONTHLY => collect(range(0, 11))
                ->map(fn($index) => $startMonth->copy()->addMonths($index))
                ->all(),
        };

        $createdCount = DB::transaction(function () use ($schedule, $schoolId, $academicYear, $validated) {
            $count = 0;

            foreach ($schedule as $date) {
                $alreadyExists = FeeStructure::query()
                    ->where('school_id', $schoolId)
                    ->where('academic_year_id', $academicYear->id)
                    ->where('class_id', $validated['class_id'])
                    ->where('name', $validated['name'])
                    ->where('type', $validated['type'])
                    ->where('frequency', $validated['frequency'])
                    ->where('year', $date->year)
                    ->where('month', $date->month)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                FeeStructure::create([
                    'school_id' => $schoolId,
                    'academic_year_id' => $academicYear->id,
                    'class_id' => $validated['class_id'],
                    'name' => $validated['name'],
                    'type' => $validated['type'],
                    'amount' => $validated['amount'],
                    'frequency' => $validated['frequency'],
                    'description' => $validated['description'] ?? null,
                    'year' => $date->year,
                    'month' => $date->month,
                ]);

                $count++;
            }

            return $count;
        });

        if ($createdCount === 0) {
            return back()->with('error', 'Fee structure already exists for the selected frequency schedule.');
        }

        return back()->with('success', $createdCount === 1
            ? 'Successfully Saved.'
            : "Successfully Saved ({$createdCount} entries).");
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
    public function feeUsers(Request $request, FeeStructure $fee)
    {
        $users = User::with([
            'payments' => function ($q) use ($fee) {
                $q->where('fee_structure_id', $fee->id)
                    ->where('school_id', $fee->school_id)
                    ->where('academic_year_id', $fee->academic_year_id);
            },
            'discounts' => function ($q) use ($fee) {
                $q->where('fee_structure_id', $fee->id)
                    ->where('is_active', true);
            }
        ])
            ->where('users.school_id', $fee->school_id)
            ->where('users.class_id', $fee->class_id)
            ->whereIn('users.role', UserRole::allowedForUser(auth()->user()->role))
            ->where('users.id', '!=', auth()->id())
            ->when($request->role && $request->role !== 'all', fn($q) => $q->where('users.role', $request->role))
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(users.name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(users.email) LIKE ?', ["%{$search}%"]);
                });
            })
            ->withSum(['discounts as discount_amount' => function ($q) use ($fee) {
                $q->where('fee_structure_id', $fee->id)
                    ->where('is_active', true);
            }], 'amount')
            ->orderBy('users.created_at')
            ->paginate(10)
            ->withQueryString();
        $users->getCollection()->transform(function ($user) {
            $user->payment = $user->payments->first() ?? null;
            unset($user->payments);
            return $user;
        });
        return ApiResponse::paginated($users, 'Users fetched successfully.');
    }

    public function customAmount(Request $request, FeeStructure $fee, User $user)
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0']
        ]);
        // DB::transaction(function () use ($validated, $fee, $user) {
        if ($validated['amount'] == 0) {
            $user->discounts()->where('fee_structure_id', $fee->id)->delete();
            return;
        } else {
            $user->discounts()->updateOrCreate(
                [
                    'fee_structure_id' => $fee->id,
                ],
                [
                    'amount' => $validated['amount']
                ]
            );
        }
        // });
        return back()->with('success', 'Successfully Saved.');
    }
    public function getCustomAmount(Request $request, FeeStructure $fee, User $user)
    {
        $discount = $user->discounts()
            ->where('fee_structure_id', $fee->id)
            ->where('is_active', true)
            ->first();
        if (!$discount) {
            $discount = new Discount();
            $discount->amount = 0;
        }
        return ApiResponse::success($discount, 'Custom amount fetched successfully.');
    }
}
