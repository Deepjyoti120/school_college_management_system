<?php

namespace App\Http\Controllers\Users;

use App\Enums\RazorpayPaymentStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Category;
use App\Models\FeeStructure;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserPaymentController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        // $schoolId = $user->school_id;
        // $defaultAcademicYear = AcademicYear::getOrCreateCurrentAcademicYear($schoolId);
        // $request->academicYear = $request->academicYear ?? $defaultAcademicYear->id;
        // $request->paymentStatus = $request->paymentStatus ?? 'paid';
        // 
        // $payments = Payment::query()->with(['feeStructure.class', 'user'])
        //     ->where('user_id', $user->id)
        //     ->when($request->search, function ($q) use ($request) {
        //         $search = strtolower($request->search);
        //         $q->where(function ($query) use ($search) {
        //             $query->whereHas('user', function ($sub) use ($search) {
        //                 $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
        //                     ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
        //                     ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
        //             })->orWhereHas('feeStructure', function ($sub) use ($search) {
        //                 $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        //             });
        //         });
        //     })
        //     ->when($request->feeType && $request->feeType !== 'all', function ($q) use ($request) {
        //         $q->whereHas('feeStructure', fn($sub) => $sub->where('type', $request->feeType));
        //     })
        //     ->when($request->paymentStatus && $request->paymentStatus !== 'all', fn($q) => $q->where('status', $request->paymentStatus))
        //     ->when($request->academicYear, fn($q) => $q->where('academic_year_id', $request->academicYear))
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(10)
        //     ->withQueryString();
        $fees = FeeStructure::query()
            ->with(['school', 'class'])
            ->where('school_id', $user->school_id)
            ->where('class_id', $user->class_id)
            // ->withCount(['users as user_count'])
            // ->withSum(['payments as total_paid' => function ($q) {
            //     $q->where('status', RazorpayPaymentStatus::PAID);
            // }], 'total_amount')
            ->when($request->paymentStatus && $request->paymentStatus !== 'all', function ($q) use ($request, $user) {
                if ($request->paymentStatus === 'paid') {
                    $q->whereHas('payments', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id)
                            ->where('status', 'paid');
                    });
                } elseif ($request->paymentStatus === 'pending') {
                    $q->whereDoesntHave('payments', function ($sub) use ($user) {
                        $sub->where('user_id', $user->id)
                            ->where('status', 'paid');
                    });
                } else {
                    $q->whereHas('payments', function ($sub) use ($user, $request) {
                        $sub->where('user_id', $user->id)
                            ->where('status', $request->paymentStatus);
                    });
                }
            })
            ->withExists(['payments as is_paid' => function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', RazorpayPaymentStatus::PAID->value);
            }])
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
            })
            ->when($request->feeType && $request->feeType !== 'all', fn($q) => $q->where('type', $request->feeType))
            ->when($request->academicYear, fn($q) => $q->where('academic_year_id', $request->academicYear))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            // ->through(function ($fee) {
            //     // $fee->total_payable = $fee->total_amount * $fee->user_count;
            //     // $fee->total_paid = $fee->total_paid ?? 0;
            //     // $fee->pending_amount = $fee->total_payable - $fee->total_paid;
            //     // $fee->status = $fee->is_paid;
            //     return $fee;
            // })
            ->withQueryString();
        return ApiResponse::paginated($fees, 'Users fetched successfully.');
    }
}
