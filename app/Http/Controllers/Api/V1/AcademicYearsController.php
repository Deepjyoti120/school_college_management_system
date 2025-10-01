<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class AcademicYearsController extends Controller
{
    public function academicYears(Request $request)
    {
        $user = auth()->user();
        $academicYears = AcademicYear::where('school_id', $user->school_id)->get();
        return ApiResponse::success($academicYears, 'success');
    }
}
