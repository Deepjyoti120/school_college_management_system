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

class PaymentController extends Controller
{
    use GeneratesUniqueNumber;
    
    protected function initRazorPay()
    {
        return new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
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
        $feeStructure =  FeeStructure::findOrFail($id);
        $user = auth()->user();
        $amount = $feeStructure->total_amount;
        $name =   $user->name;
        $phone = $user->phone;
        $email = $user->email;
        $amount = round($amount * 100);
        $api = $this->initRazorPay();
        $order = $api->order->create([
            'receipt' => $this->generateUniqueRandomNumber('receipt'),
            'amount' => $amount,
            'currency' => 'INR'
        ]);
        $payment =   Payment::updateOrCreate(
            [
                'user_id' => $user->id,
                'class_id' => $user->class_id,
                'school_id' => $user->school_id,
                'academic_year_id' => $feeStructure->academic_year_id,
                'fee_structure_id' => $feeStructure->id,
            ],
            [
                'month' => now()->month,
                'year' => now()->year,
                'status' => RazorpayPaymentStatus::PENDING,
                'amount' => $feeStructure->amount,
                'gst_amount' => $feeStructure->gst_amount,
                'total_amount' => $feeStructure->total_amount,
                'payment_date' => now(),
                'razorpay_order_id' => $order['id'],
                // 'razorpay_payment_id' => null,
                // 'razorpay_signature'  => null,
            ]
        );
        if ($payment) {
            $data = [
                'key' =>  config('services.razorpay.key'),
                'order_id' => $order['id'],
                'amount' => $amount,
                'name' => $name,
                'description' => '',
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
            return  ApiResponse::success($data, 'success');
        }
        return ApiResponse::error('Unable to create payment record. Please try again.');
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
