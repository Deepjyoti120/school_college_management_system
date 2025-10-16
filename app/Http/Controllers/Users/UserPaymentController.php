<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Category;
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
        $payments = Payment::query()->with(['feeStructure.class', 'user'])
            ->where('user_id', $user->id)
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($sub) use ($search) {
                        $sub->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                            ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$search}%"]);
                    })->orWhereHas('feeStructure', function ($sub) use ($search) {
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
        return ApiResponse::paginated($payments, 'Users fetched successfully.');
    }
}
