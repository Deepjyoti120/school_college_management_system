<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AttendanceStatus;
use App\Enums\RazorpayPaymentStatus;
use App\Enums\UserRole;
use App\GeneratesUniqueNumber;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\FeeStructure;
use App\Models\Holiday;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Razorpay\Api\Api;
use Symfony\Component\Uid\Ulid;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $classId = $request->input('class_id');
        $attendances = Attendance::with(['school', 'user'])
            // ->where('user_id', $userId)
            ->when(!$classId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('role', $classId ? UserRole::STUDENT : UserRole::TEACHER)
            ->where('school_id', $request->user()->school_id)
            ->when($classId, function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
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
        return ApiResponse::error(message: 'You have already checked in and checked out today.');
    }
    public function classList(Request $request)
    {
        $user = $request->user();
        $schoolClass = SchoolClass::where('school_id', $user->school_id)->get();
        return ApiResponse::success($schoolClass, 'success');
    }

    public function studentAttendanceGenerate(Request $request)
    {
        $classId = $request->input('class_id');
        if (!$classId) {
            return ApiResponse::error(message: 'class_id is required', status: Response::HTTP_BAD_REQUEST);
        }
        $today = now()->toDateString();
        $students = User::where('role', UserRole::STUDENT)
            ->where('school_id', $request->user()->school_id)
            ->where('class_id', $classId)
            ->whereDoesntHave('attendances', function ($q) use ($today) {
                $q->whereDate('date', $today);
            })
            ->get();
        $academicYear = AcademicYear::where('school_id', $request->user()->school_id)
            ->where('is_current', true)
            ->first();
        $attendanceData = $students->map(function ($student) use ($today, $academicYear) {
            return [
                // 'id' => Ulid::generate(),
                'school_id' => $student->school_id,
                'class_id' => $student->class_id,
                'user_id' => $student->id,
                'date' => $today,
                'status' => AttendanceStatus::PRESENT,
                'role' => $student->role,
                'academic_year_id' => $academicYear?->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        if (!empty($attendanceData)) {
            Attendance::insert($attendanceData);
        }
        // $attendanceData->loadMissing('user', 'school');
        return ApiResponse::success($attendanceData, 'Student attendance generated successfully');
    }
    public function studentAttendanceMarkAbsentPresent(Request $request)
    {
        $attendanceId  = $request->input('attendance_id');
        if (!$attendanceId) {
            return ApiResponse::error(message: 'attendance_id is required', status: Response::HTTP_BAD_REQUEST);
        }
        $attendance = Attendance::where('id', $attendanceId)->where('school_id', $request->user()->school_id)
            ->first();
        if (!$attendance) {
            return ApiResponse::error(message: 'Attendance record not found', status: Response::HTTP_NOT_FOUND);
        }
        $attendance->update([
            'status' => $attendance->status === AttendanceStatus::PRESENT ?  AttendanceStatus::ABSENT : AttendanceStatus::PRESENT,
        ]);
        return ApiResponse::success($attendance, 'Attendance marked as absent successfully');
    }
}
