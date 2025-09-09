<?php

namespace App\Http\Controllers;

use App\Enums\FeeType;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\FeeGenerate;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\OrderProgress;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FeeController extends Controller
{
    public function index(Request $request)
    {
        $fees = FeeGenerate::query()->with(['school'])
            ->when($request->search, function ($q) use ($request) {
                $search = strtolower($request->search);
                $q->whereHas('product', function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($request->status && $request->status !== 'all', fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();
        return Inertia::render('fee/Index', [
            'fees' => $fees,
            'filters' => $request->only(['search', 'status']),
            'feeTypes' => FeeType::options(),
        ]);
    }
    public function feeGenerate(Request $request)
    {
        if (!in_array($request->type, FeeType::values(), true)) {
            return back()->with('error', 'Fee type is required');
        }
        return back()->with('success', 'Fee Generated Successfully');
    }

    public function create()
    {
        $products = Product::all();
        // ->map(fn($product) => [
        //     'label' => $product->name,
        //     'value' => $product->id,
        // ]);
        return Inertia::render('order/Create', [
            'roles' => UserRole::options(),
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'total_price' => ['required', 'numeric', 'min:0'],
            'documents' => ['nullable', 'array', 'max:2'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        DB::transaction(function () use ($validated, $request) {
            $order = Order::create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'total_price' => $validated['total_price'],
                'created_by' => auth()->id(),
                // 'updated_by' => auth()->id(),
                'stage' => 0,
                'status' => 'pending',
            ]);
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('orders/documents', 'public');
                    OrderDocument::create([
                        'order_id' => $order->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
            OrderProgress::create([
                'order_id' => $order->id,
                'updated_by' => auth()->id(),
                'stage' => 0,
                'status' => OrderStatus::PENDING,
                'title' => 'Order created',
                'remarks' => 'Order created',
            ]);
        });
        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function approve(Request $request, Order $order)
    {
        // try {
        $countryCode = $request->input('country_code', '+91');
        $validated = $request->validate([
            'remarks' => ['nullable', 'string'],
            'vehicle_number' => [
                Rule::requiredIf($order->new_status === OrderStatus::DISPATCHED),
                'nullable',
                'string',
            ],
            'driver_phone' => [
                Rule::requiredIf($order->new_status === OrderStatus::DISPATCHED),
                'nullable',
                'digits:10',
                Rule::unique('orders')->where(function ($query) use ($countryCode) {
                    return $query->where('country_code', $countryCode);
                }),
            ],
        ]);
        // $order->new_status == OrderStatus::DISPATCHED;
        DB::transaction(function () use ($validated, $order) {
            OrderProgress::create([
                'order_id' => $order->id,
                'updated_by' => auth()->id(),
                'stage' => $order->stage + 1,
                'status' => $order->new_status,
                'title' => 'Order ' . $order->new_status->value,
                'remarks' => $validated['remarks'],
            ]);
            $order->update([
                ...$validated,
                'stage' => $order->stage + 1,
                'status' => $order->new_status,
                'updated_by' => auth()->id(),
            ]);
        });
        return back()->with('success', 'Successfully Saved.');
        // } catch (\Exception $e){
        //     dd($e->getMessage());
        // }
    }

    public function reject(Request $request, Order $order)
    {
        $validated = $request->validate([
            'remarks' => ['required', 'string'],
        ]);
        DB::transaction(function () use ($validated, $order) {
            OrderProgress::create([
                ...$validated,
                'order_id' => $order->id,
                'updated_by' => auth()->id(),
                'stage' => $order->stage + 1,
                'status' => OrderStatus::REJECTED,
                'title' => 'Order Rejected',
                // 'remarks' => $validated['remarks'],
            ]);
            $order->update([
                'stage' => $order->stage + 1,
                'status' => OrderStatus::REJECTED,
                'updated_by' => auth()->id(),
            ]);
        });
        return back()->with('success', 'Successfully Saved.');
    }
}
