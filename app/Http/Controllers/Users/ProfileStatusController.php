<?php

namespace App\Http\Controllers\Users;

use App\Enums\FeeType;
use App\Enums\FrequencyType;
use App\Enums\RazorpayPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileStatusController extends Controller
{
    public function __invoke(Request $request, User $user)
    {
        return Inertia::render('users/Profile', [
            'user' => $user->loadMissing(['class', 'section', 'school']),
            'feeTypes' => FeeType::options(),
            'frequency' => FrequencyType::options(),
            'paymentStatuses' => RazorpayPaymentStatus::options(),
            'academicYears' => AcademicYear::where('school_id', $user->school_id)
                ->orderBy('start_date', 'desc')
                ->get(['id', 'name'])->map(fn($m) => [
                    'label' => $m->name,
                    'value' => $m->id,
                ])
        ]);
    }
}
