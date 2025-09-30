<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\RazorpayPaymentStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\FeeStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;
use Razorpay\Api\Api;

class PaymentController extends Controller
{

    protected function initRazorPay()
    {
        return new  Api(
            config('freshman.razor_pay_key'),
            config('freshman.razor_pay_secret')
        );
    }
    /**
     * Display a listing of the resource.
     */
    public function pendingPayments(Request $request)
    {
        $user = auth()->user();
        $defaultAcademicYear = AcademicYear::getOrCreateCurrentAcademicYear($user->school_id);
        $feeStructures = FeeStructure::with(['school', 'academicYear', 'class'])->where('class_id', $user->class_id)
            ->where('is_active', true)
            ->where('academic_year_id', $defaultAcademicYear->id)
            // ->with(['payment'])
            ->get();
        return ApiResponse::success($feeStructures, 'success');
    }
    public function paymentInit(Request $request)
    {
        $id = $request->query('id');
        $type = $request->query('type');
        $amount = 0;
        $name = '';
        $phone = '';
        $email = '';
        $tenant_id = '';
        $late_fine = 0;
        $amount_due = 0;
        $due_date = '';
        if ($type == 'month') {
            $monthlyBilling =  MonthlyBilling::with(['tenant'])->findOrFail($id);
            $amount = $monthlyBilling->amount_due + $monthlyBilling->late_fine;
            $name =   $monthlyBilling->tenant->name;
            $phone = $monthlyBilling->tenant->phone;
            $email = $monthlyBilling->tenant->email;
            $tenant_id = $monthlyBilling->tenant_id;
            $currentDay = now()->day;
            $late_fine = $monthlyBilling->late_fine;
            $amount_due = $monthlyBilling->amount_due;
            $due_date = $monthlyBilling->due_date;
            // if ($currentDay > 7) {
            //     $lateFine = ($monthlyBilling->month_due * MonthlyBilling::LATE_FEE_PERCENTAGE) / 100;
            //     $amount = $amount + $lateFine;
            //     $late_fine = $lateFine;
            // }
        } else if ($type == 'year') {
            $arrearBilling =  ArrearBilling::with(['tenant'])->findOrFail($id);
            $amount = $arrearBilling->amount_due + $arrearBilling->late_fine;
            $name =   $arrearBilling->tenant->name;
            $phone = $arrearBilling->tenant->phone;
            $email = $arrearBilling->tenant->email;
            $tenant_id = $arrearBilling->tenant_id;
            $late_fine = $arrearBilling->late_fine;
            $amount_due = $arrearBilling->amount_due;
            $due_date = $arrearBilling->due_date;
        }
        $amount = round($amount * 100);
        $api = $this->initRazorPay();
        $order = $api->order->create([
            'receipt' => $this->generateUniqueRandomNumber('receipt'),
            'amount' => $amount,
            'currency' => 'INR'
        ]);
        if ($type == 'month') {
            $monthlyBilling->update([
                'razorpay_order_id' => $order['id'],
            ]);
        } else if ($type == 'year') {
            $arrearBilling->update([
                'razorpay_order_id' => $order['id'],
            ]);
        }
        $actualAmount = $amount / 100;
        Payment::create([
            'tenant_id' => $tenant_id,
            'description' => 'Payment for ' . $type,
            'amount_due' => $amount_due,
            'paid_amount' => $actualAmount,
            'total_amount' => $actualAmount,
            'late_fine' => $late_fine,
            'due_date' => $due_date,
            'payment_status' => RazorpayPaymentStatus::PENDING,
            'razorpay_order_id' => $order['id'],
            'razorpay_receipt_id' => $order['receipt'],
            'type' => $type,
        ]);
        // $data = [
        //     'amount' => $amount,
        //     'order_id' => $order['id'],
        //     'name' => $name,
        //     'phone' => $phone,
        //     'previousUrl' => ''
        // ];
        $data = [
            'key' => config('freshman.razor_pay_key'),
            'order_id' => $order['id'],
            'amount' => $amount,
            'name' => $name,
            'description' => '', // 'Payment for ' . ucfirst($type),
            'retry' => [
                'enabled' => false,
                'max_count' => 1
            ],
            'send_sms_hash' => true,
            'prefill' => [
                'contact' => $phone,
                'email' => $email
            ],
            // 'external' => [
            //     'wallets' => ['paytm']
            // ]
        ];
        return $this->respondWithSuccess($data);
    }

     public function paymentSuccessOrFailed(Request $request)
    {
        $string = $request->razorpay_order_id . '|' . $request->razorpay_payment_id;
        $expectedSignature = hash_hmac('sha256', $string, config('freshman.razor_pay_secret'));
        if (hash_equals($expectedSignature, $request->razorpay_signature) == true) {
            // $billingId = '';
            $monthlyBilling =  MonthlyBilling::where('razorpay_order_id', $request->razorpay_order_id)->latest('id')->first();
            if ($monthlyBilling) {
                $monthlyBilling->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature,
                    'updated_at' => now(),
                    'paid' => true,
                    'payment_status' => RazorpayPaymentStatus::PAID,
                ]);
                // $billingId = $monthlyBilling->tenant_id;
                $financialYear = FinancialYear::where('start_after', '<=', now())
                            ->latest('id')->first();
                    $startMonth = $financialYear->start_month;
                    $endMonth = $financialYear->end_month;
                    $currentYear = $monthlyBilling->year;
                    MonthlyBilling::where('paid', false)
                        ->where('lease_id', $monthlyBilling->lease_id)
                        ->where(function ($query) use ($startMonth, $endMonth, $currentYear) {
                            $query->where(function ($subQuery) use ($startMonth, $currentYear) {
                                $subQuery->where('year', $currentYear)
                                         ->whereBetween('month', [$startMonth, 12]);
                            });
                            $query->orWhere(function ($subQuery) use ($endMonth, $currentYear) {
                                $subQuery->where('year', $currentYear + 1)
                                         ->whereBetween('month', [1, $endMonth]);
                            });
                        })
                        ->update([
                            'razorpay_payment_id' => $request->razorpay_payment_id,
                            'razorpay_signature' => $request->razorpay_signature,
                            'updated_at' => now(),
                            'paid' => true,
                            'payment_status' => RazorpayPaymentStatus::PAID,
                        ]);
            } else {
                $arrearBilling = ArrearBilling::where('razorpay_order_id', $request->razorpay_order_id)->latest('id')->first();
                $arrearBilling->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_signature' => $request->razorpay_signature,
                    'updated_at' => now(),
                    'paid' => true,
                    'payment_status' => RazorpayPaymentStatus::PAID,
                ]);
                // $billingId = $arrearBilling->tenant_id;
            }
            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
            $payment->update([
                'description' => 'Payment Success',
                'payment_status' => RazorpayPaymentStatus::PAID,
                'payment_date' => now(),
                'razorpay_payment_id' => $request->razorpay_payment_id, 
                'razorpay_signature' => $request->razorpay_signature,
                'paid' => true,
                // 'payment_method' => '',
            ]);
            $this->respondOk(Response::HTTP_OK, 'Payment Successful');
        } else {
            return $this->respondWithError(Response::HTTP_BAD_REQUEST, 'Invalid payment signature');
        }
    }
}
