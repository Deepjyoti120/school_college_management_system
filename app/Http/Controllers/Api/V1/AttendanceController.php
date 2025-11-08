<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AttendanceStatus;
use App\Enums\RazorpayPaymentStatus;
use App\GeneratesUniqueNumber;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\FeeStructure;
use App\Models\Holiday;
use App\Models\Payment;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Razorpay\Api\Api;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $attendances = Attendance::where('user_id', $userId)
            ->with(['school'])
            ->orderBy('created_at', 'desc')
            ->limit(31)
            ->get();
        return ApiResponse::success($attendances, 'success');
    }

    public function checkInOut(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);
        $nearestSchool = School::select('id')
            ->where('id', $user->school_id)
            ->selectRaw("
                ST_DistanceSphere(
                    ST_MakePoint(longitude, latitude),
                    ST_MakePoint(?, ?), 4326
                ) as distance
                ", [$validated['lng'], $validated['lat']])
            ->orderBy('distance', 'asc')
            ->first();
        if (!$nearestSchool || $nearestSchool->distance > 10) {
            return ApiResponse::error(message: "You are not within 10 meters of the school premises.", status: Response::HTTP_FORBIDDEN);
        }
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();
        if (!$attendance) {
            return ApiResponse::error(message: 'Attendance record not found for today', status: Response::HTTP_NOT_FOUND);
        }
        if (is_null($attendance->check_in)) {
            $attendance->update([
                'check_in' => now(),
                'check_in_lat' => $validated['lat'],
                'check_in_lng' => $validated['lng'],
                'status' => AttendanceStatus::PRESENT,
                'school_id' => $nearestSchool->id,
                'role' => $user->role,
            ]);
            return ApiResponse::success(
                data: $attendance,
                message: 'Checked in successfully'
            );
        }
        if (is_null($attendance->check_out)) {
            $totalWorkedHours = (int) $attendance->check_in->diffInMinutes(now());
            $attendance->update([
                'check_out' => now(),
                'check_out_lat'  => $validated['lat'],
                'check_out_lng'  => $validated['lng'],
                'work_minutes' => $totalWorkedHours,
            ]);
            return ApiResponse::success(
                data: $attendance,
                message: 'Checked out successfully'
            );
        }
        return ApiResponse::created('You have already checked in and checked out today.');
    }
}
