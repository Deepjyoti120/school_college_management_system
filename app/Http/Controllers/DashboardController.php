<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\RazorpayPaymentStatus;
use App\Enums\UserRole;
use App\Models\FeeStructure;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $charts = [
            'paymentStatusDistribution' => $this->getPaymentStatusDistribution(),
            'revenueByMonth' => $this->getRevenueByMonth(),
            'feeCollectionByClass' => $this->getFeeCollectionByClass(),
            'paymentsLast7Days' => $this->getPaymentsLast7Days(),
        ];
        return Inertia::render('Dashboard', [
            'charts' => null,
            'stats' => null,
            'showRevenue' => true,
            'charts' => $charts,
            'stats' => $this->getQuickStats(),
            // 'showRevenue' => auth()->user()->role === UserRole::COM || auth()->user()->role === UserRole::GM,
        ]);
    }


    protected function getQuickStats()
    {
        $user = auth()->user();
        $schoolId = $user->school_id;
        $totalStudent = User::where('school_id', $schoolId)
            ->where('role', 'student')
            ->count();
        $totalFeeStructures = FeeStructure::where('school_id', $schoolId)->count();
        $totalPayments = Payment::where('school_id', $schoolId)->count();
        $pendingPayments = Payment::where('school_id', $schoolId)
                ->where('status', RazorpayPaymentStatus::PENDING)
                ->count();
        return [
            // total students in the same school
            'totalStudents' => $totalStudent,

            'totalFeeStructures' => $totalFeeStructures,

            'totalPayments' => $totalPayments,

            'pendingPayments' => ($totalFeeStructures * $totalStudent) - $totalPayments,

            'successfulPayments' => Payment::where('school_id', $schoolId)
                ->whereIn('status', [RazorpayPaymentStatus::PAID, RazorpayPaymentStatus::CAPTURED])
                ->count(),

            'revenueThisMonth' => Payment::where('school_id', $schoolId)
                ->whereIn('status', [RazorpayPaymentStatus::PAID, RazorpayPaymentStatus::CAPTURED])
                ->whereMonth('payment_date', now()->month)
                ->sum('total_amount'),

            'avgPaymentValue' => number_format(
                Payment::where('school_id', $schoolId)
                    ->whereIn('status', [RazorpayPaymentStatus::PAID, RazorpayPaymentStatus::CAPTURED])
                    ->avg('total_amount'),
                2
            ),

            'firstPaymentThisMonth' => Payment::where('school_id', $schoolId)
                ->whereMonth('payment_date', now()->month)
                ->orderBy('payment_date', 'asc')
                ->first()?->payment_date,

            'paymentsToday' => Payment::where('school_id', $schoolId)
                ->whereDate('payment_date', today())
                ->count(),

            'myPayments' => Payment::where('school_id', $schoolId)
                ->where('user_id', $user->id)
                ->count(),
        ];
    }

    protected function getPaymentsLast7Days()
    {
        return Payment::select(
            DB::raw('DATE(payment_date) as date'),
            DB::raw('count(*) as count')
        )
            ->where('school_id', auth()->user()->school_id)
            ->where('payment_date', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($item) => [
                'date' =>  Carbon::parse($item->date)->format('M d'),
                'count' => $item->count,
            ]);
    }

    protected function getFeeCollectionByClass()
    {
        return Payment::query()
            ->join('school_classes', 'payments.class_id', '=', 'school_classes.id') // adjust table name if different
            ->where('payments.school_id', auth()->user()->school_id)
            ->whereIn('payments.status', [
                RazorpayPaymentStatus::PAID->value,
                RazorpayPaymentStatus::CAPTURED->value,
            ])
            ->select('school_classes.name as class', DB::raw('SUM(payments.total_amount) as amount'))
            ->groupBy('school_classes.name')
            ->orderBy('school_classes.name')
            ->get()
            ->map(fn($item) => [
                'class' => $item->class ?? 'Unknown',
                'amount' => (float)$item->amount,
            ]);
    }


    protected function getPaymentStatusDistribution()
    {
        return Payment::select('status', DB::raw('count(*) as count'))
            ->where('school_id', auth()->user()->school_id)
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->status->name,
                    'value' => $item->count,
                    'color' => $item->status_color,
                ];
            });
    }

    protected function getRevenueByMonth()
    {
        return Payment::select(
            DB::raw('EXTRACT(MONTH FROM payment_date) as month'),
            DB::raw('EXTRACT(YEAR FROM payment_date) as year'),
            DB::raw('SUM(total_amount) as total')
        )
            ->where('school_id', auth()->user()->school_id)
            ->whereIn('status', [
                RazorpayPaymentStatus::PAID->value,
                RazorpayPaymentStatus::CAPTURED->value,
            ])
            ->groupBy(DB::raw('EXTRACT(YEAR FROM payment_date), EXTRACT(MONTH FROM payment_date)'))
            ->orderBy(DB::raw('year, month'))
            ->get()
            ->map(fn($item) => [
                'month'   => Carbon::createFromDate($item->year, $item->month, 1)->format('M Y'),
                'revenue' => (float)$item->total,
            ]);
    }





    // protected function getStatusDistribution()
    // {
    //     return Order::select('status', DB::raw('count(*) as count'))
    //         ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->forFacUser()
    //         ->groupBy('status')
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'label' => $item->status_label,
    //                 'value' => $item->count,
    //                 'color' => $item->status_color
    //             ];
    //         });
    // }

    // protected function getStageProgress()
    // {
    //     return Order::select('stage', DB::raw('count(*) as count'))
    //         ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->forFacUser()
    //         ->groupBy('stage')
    //         ->orderBy('stage')
    //         ->get()
    //         ->map(function ($item) {
    //             $stageLabels = [
    //                 Order::DEL_STAGE => 'Delivery',
    //                 Order::COM_STAGE => 'Commercial',
    //                 Order::GM_STAGE => 'General Manager',
    //                 Order::FAC_STAGE => 'Factory'
    //             ];
    //             return [
    //                 'stage' => $stageLabels[$item->stage] ?? 'Unknown',
    //                 'count' => $item->count
    //             ];
    //         });
    // }

    // protected function getOrderVolume()
    // {
    //     return Order::select(
    //         DB::raw('DATE(created_at) as date'),
    //         DB::raw('count(*) as count')
    //     )->forFacUser()->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->groupBy('date')
    //         ->orderBy('date')
    //         ->where('created_at', '>=', now()->subDays(30))
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'date' => Carbon::parse($item->date)->format('M d'),
    //                 'count' => $item->count
    //             ];
    //         });
    // }

    // protected function getProductSales()
    // {
    //     return Order::with('product')
    //         ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->forFacUser()
    //         ->select('product_id', DB::raw('sum(quantity) as total_quantity'))
    //         ->groupBy('product_id')
    //         ->orderByDesc('total_quantity')
    //         ->limit(10)
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'product' => $item->product->name ?? 'Deleted Product',
    //                 'quantity' => $item->total_quantity
    //             ];
    //         });
    // }

    // protected function getProcessingTime()
    // {
    //     $data = Order::query()
    //         ->from('orders as o')
    //         // ->forFacUser()
    //         ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->join('order_progress as op', function ($join) {
    //             $join->on('o.id', '=', 'op.order_id')
    //                 ->where('op.stage', '=', Order::FAC_STAGE);
    //         })
    //         ->select(
    //             'o.id as order_id',
    //             DB::raw('EXTRACT(DAY FROM (op.created_at - o.created_at)) as days_to_process')
    //         )
    //         ->where('o.status', OrderStatus::DISPATCHED->value)
    //         ->get()
    //         ->groupBy(function ($item) {
    //             return floor($item->days_to_process);
    //         })
    //         ->map(function ($group, $key) {
    //             return [
    //                 'days' => "$key-" . ($key + 1),
    //                 'count' => count($group),
    //                 'days_start' => (int)$key
    //             ];
    //         })
    //         ->values();

    //     $maxDay = $data->isNotEmpty() ? $data->max('days_start') : 0;

    //     $fullRange = collect(range(0, $maxDay))
    //         ->map(function ($day) use ($data) {
    //             $existing = $data->firstWhere('days_start', $day);
    //             return $existing ?? [
    //                 'days' => "$day-" . ($day + 1),
    //                 'count' => 0,
    //                 'days_start' => $day
    //             ];
    //         });

    //     return $fullRange->sortBy('days_start')->values();
    // }

    // protected function getOrdersByDayOfWeek()
    // {
    //     $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    //     $data = Order::select(
    //         DB::raw('EXTRACT(DOW FROM created_at) as day_of_week'), // PostgreSQL uses DOW (0-6 where 0=Sunday)
    //         DB::raw('count(*) as count')
    //     )->forFacUser()
    //         ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->groupBy('day_of_week')
    //         ->orderBy('day_of_week')
    //         ->get()
    //         ->mapWithKeys(function ($item) use ($days) {
    //             // PostgreSQL DOW returns 0=Sunday to 6=Saturday
    //             return [$days[$item->day_of_week] => $item->count];
    //         });

    //     // Ensure all days are represented even if no orders
    //     return collect($days)->map(function ($day, $index) use ($data) {
    //         return [
    //             'day' => $day,
    //             'count' => $data[$day] ?? 0
    //         ];
    //     });
    // }

    // protected function getOrdersByHourOfDay()
    // {
    //     $data = Order::forFacUser()->select(
    //         DB::raw('EXTRACT(HOUR FROM created_at) as hour'), // PostgreSQL uses EXTRACT
    //         DB::raw('count(*) as count')
    //     )->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
    //         ->groupBy('hour')
    //         ->orderBy('hour')
    //         ->get()
    //         ->mapWithKeys(function ($item) {
    //             return [str_pad($item->hour, 2, '0', STR_PAD_LEFT) => $item->count];
    //         });
    //     // Generate all 24 hours with counts
    //     return collect(range(0, 23))->map(function ($hour) use ($data) {
    //         $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
    //         return [
    //             'hour' => $hourKey . ':00',
    //             'count' => $data[$hourKey] ?? 0
    //         ];
    //     });
    // }
}
