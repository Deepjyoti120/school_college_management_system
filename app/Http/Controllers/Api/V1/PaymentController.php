<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function pendingPayments(Request $request)
    {
        $user = auth()->user();
        $defaultAcademicYear = AcademicYear::getOrCreateCurrentAcademicYear($user->school_id);
        $feeStructures = FeeStructure::with(['school','academicYear','class'])->where('class_id', $user->class_id)
            ->where('is_active', true)
            ->where('academic_year_id', $defaultAcademicYear->id)
            // ->with(['payment'])
            ->get();
        return ApiResponse::success($feeStructures, 'success');
    }
}
