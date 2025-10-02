<?php

namespace App\Http\Controllers;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use App\Enums\OrderStatus;
use App\Enums\RazorpayPaymentStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\FeeGenerate;
use App\Models\FeeStructure;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\OrderProgress;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SchoolClass;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $defaultAcademicYear = AcademicYear::getOrCreateCurrentAcademicYear($schoolId);
        $request->academicYear = $request->academicYear ?? $defaultAcademicYear->id;
        $request->paymentStatus = $request->paymentStatus ?? 'paid';
        $payments = Payment::query()->with(['feeStructure.class', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
                    })
                        ->orWhereHas('feeStructure', function ($sub) use ($search) {
                            $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        });
                });
            })
            ->when($request->feeType && $request->feeType !== 'all', function ($q) use ($request) {
                $q->whereHas('feeStructure', fn($sub) => $sub->where('type', $request->feeType));
            })
            ->when($request->paymentStatus && $request->paymentStatus !== 'all', fn($q) => $q->where('status', $request->paymentStatus))
            ->when($request->academicYear, fn($q) => $q->where('academic_year_id', $request->academicYear))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('payment/Index', [
            'payments' => $payments,
            'filters' => [
                'search',
                'feeType' => $request->feeType,
                'academicYear' => (string) $request->academicYear,
                'paymentStatus' => $request->paymentStatus,
            ],
            'feeTypes' => FeeType::options(),
            'frequency' => FrequencyType::options(),
            'paymentStatuses' => RazorpayPaymentStatus::options(),
            'academicYears' => AcademicYear::where('school_id', $schoolId)
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name'])->map(fn($m) => [
                    'label' => $m->name,
                    'value' => $m->id,
                ])
        ]);
    }
}
