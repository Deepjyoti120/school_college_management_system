<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $charts = [
            'statusDistribution' => $this->getStatusDistribution(),
            'stageProgress' => $this->getStageProgress(),
            'orderVolume' => $this->getOrderVolume(),
            'productSales' => $this->getProductSales(),
            'processingTime' => $this->getProcessingTime(),
            'ordersByDayOfWeek' => $this->getOrdersByDayOfWeek(),
            'ordersByHourOfDay' => $this->getOrdersByHourOfDay(),
        ];
        return Inertia::render('Dashboard', [
            'charts' => $charts,
            'stats' => $this->getQuickStats(),
            'showRevenue' => auth()->user()->role === UserRole::COM || auth()->user()->role === UserRole::GM,
        ]);
    }

    protected function getStatusDistribution()
    {
        return Order::select('status', DB::raw('count(*) as count'))
            ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->forFacUser()
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->status_label,
                    'value' => $item->count,
                    'color' => $item->status_color
                ];
            });
    }

    protected function getStageProgress()
    {
        return Order::select('stage', DB::raw('count(*) as count'))
            ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->forFacUser()
            ->groupBy('stage')
            ->orderBy('stage')
            ->get()
            ->map(function ($item) {
                $stageLabels = [
                    Order::DEL_STAGE => 'Delivery',
                    Order::COM_STAGE => 'Commercial',
                    Order::GM_STAGE => 'General Manager',
                    Order::FAC_STAGE => 'Factory'
                ];
                return [
                    'stage' => $stageLabels[$item->stage] ?? 'Unknown',
                    'count' => $item->count
                ];
            });
    }

    protected function getOrderVolume()
    {
        return Order::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )->forFacUser()->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->groupBy('date')
            ->orderBy('date')
            ->where('created_at', '>=', now()->subDays(30))
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('M d'),
                    'count' => $item->count
                ];
            });
    }

    protected function getProductSales()
    {
        return Order::with('product')
            ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->forFacUser()
            ->select('product_id', DB::raw('sum(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'product' => $item->product->name ?? 'Deleted Product',
                    'quantity' => $item->total_quantity
                ];
            });
    }

    protected function getProcessingTime()
    {
        $data = Order::query()
            ->from('orders as o')
            // ->forFacUser()
            ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->join('order_progress as op', function ($join) {
                $join->on('o.id', '=', 'op.order_id')
                    ->where('op.stage', '=', Order::FAC_STAGE);
            })
            ->select(
                'o.id as order_id',
                DB::raw('EXTRACT(DAY FROM (op.created_at - o.created_at)) as days_to_process')
            )
            ->where('o.status', OrderStatus::DISPATCHED->value)
            ->get()
            ->groupBy(function ($item) {
                return floor($item->days_to_process);
            })
            ->map(function ($group, $key) {
                return [
                    'days' => "$key-" . ($key + 1),
                    'count' => count($group),
                    'days_start' => (int)$key
                ];
            })
            ->values();

        $maxDay = $data->isNotEmpty() ? $data->max('days_start') : 0;

        $fullRange = collect(range(0, $maxDay))
            ->map(function ($day) use ($data) {
                $existing = $data->firstWhere('days_start', $day);
                return $existing ?? [
                    'days' => "$day-" . ($day + 1),
                    'count' => 0,
                    'days_start' => $day
                ];
            });

        return $fullRange->sortBy('days_start')->values();
    }

    protected function getQuickStats()
    {
        return [
            'totalOrders' => Order::when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
                ->when(auth()->user()->role === UserRole::FAC, fn($q) => $q->where('created_by', auth()->id()))->forFacUser()
                ->count(),
            'pendingOrders' => Order::when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
                ->when(auth()->user()->role !== UserRole::FAC, fn($q) => $q->where('status',  OrderStatus::PENDING))
                ->when(auth()->user()->role === UserRole::FAC, function ($q) {
                    return $q->whereHas(
                        'updater',
                        fn($u) =>
                        $u->whereIn('role', [UserRole::GM])
                    );
                })
                ->forFacUser()->count(),
            'rejectedOrders' => Order::where('status', OrderStatus::REJECTED)->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))->forFacUser()->count(),
            'dispatchedOrders' => Order::where('status', OrderStatus::DISPATCHED)->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))->forFacUser()->count(),
            'dispatchedToday' => Order::where('status', OrderStatus::DISPATCHED)
                ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))->forFacUser()
                ->whereDate('updated_at', today())
                ->count(),
            'avgProcessingTime' => number_format(DB::table('orders as o')
                ->join('order_progress as op', function ($join) {
                    $join->on('o.id', '=', 'op.order_id')
                        ->where('op.stage', '=', Order::FAC_STAGE);
                })
                ->where('o.status', OrderStatus::DISPATCHED->value)
                ->avg(DB::raw('EXTRACT(DAY FROM (op.created_at - o.created_at))')), 0),
            'revenueThisMonth' => Order::forFacUser()->where('status', OrderStatus::DISPATCHED->value)
                ->whereMonth('updated_at', now()->month)
                ->sum('total_price'),
            'avgOrderValue' => number_format(Order::forFacUser()->where('status', OrderStatus::DISPATCHED->value)
                ->avg('total_price'), 2),
            'fastestProcessingTime' => DB::table('orders as o')
                ->join('order_progress as op', function ($join) {
                    $join->on('o.id', '=', 'op.order_id')
                        ->where('op.stage', '=', Order::FAC_STAGE);
                })
                ->where('o.status', OrderStatus::DISPATCHED->value)
                ->min(DB::raw('EXTRACT(DAY FROM (op.created_at - o.created_at))')),
            'ordersByCurrentUser' => auth()->check()
                ? Order::where('created_by', auth()->id())->forFacUser()->count()
                : 0,
        ];
    }
    protected function getOrdersByDayOfWeek()
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $data = Order::select(
            DB::raw('EXTRACT(DOW FROM created_at) as day_of_week'), // PostgreSQL uses DOW (0-6 where 0=Sunday)
            DB::raw('count(*) as count')
        )->forFacUser()
            ->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->mapWithKeys(function ($item) use ($days) {
                // PostgreSQL DOW returns 0=Sunday to 6=Saturday
                return [$days[$item->day_of_week] => $item->count];
            });

        // Ensure all days are represented even if no orders
        return collect($days)->map(function ($day, $index) use ($data) {
            return [
                'day' => $day,
                'count' => $data[$day] ?? 0
            ];
        });
    }

    protected function getOrdersByHourOfDay()
    {
        $data = Order::forFacUser()->select(
            DB::raw('EXTRACT(HOUR FROM created_at) as hour'), // PostgreSQL uses EXTRACT
            DB::raw('count(*) as count')
        )->when(auth()->user()->role === UserRole::DEL, fn($q) => $q->where('created_by', auth()->id()))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(function ($item) {
                return [str_pad($item->hour, 2, '0', STR_PAD_LEFT) => $item->count];
            });
        // Generate all 24 hours with counts
        return collect(range(0, 23))->map(function ($hour) use ($data) {
            $hourKey = str_pad($hour, 2, '0', STR_PAD_LEFT);
            return [
                'hour' => $hourKey . ':00',
                'count' => $data[$hourKey] ?? 0
            ];
        });
    }
}
