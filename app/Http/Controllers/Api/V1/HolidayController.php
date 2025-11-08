<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RazorpayPaymentStatus;
use App\GeneratesUniqueNumber;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Razorpay\Api\Api;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $academicYears = AcademicYear::where('school_id', $user->school_id)->get();
        return ApiResponse::success($academicYears, 'success');
    }
}
