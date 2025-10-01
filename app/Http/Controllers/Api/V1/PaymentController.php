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
            ->get();
        return ApiResponse::success($feeStructures, 'success');
    }
    public function paymentsHistory(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);
        $user = auth()->user();
        $payments = Payment::with(['school', 'class'])->where('user_id', $user->id)
            ->when($request->academic_year_id, function ($query, $academicYearId) {
                $query->where('academic_year_id', $academicYearId);
            })
            ->get();
        return ApiResponse::success($payments, 'success');
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
        $payment = Payment::updateOrCreate(
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
                    'enabled' => true,
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

    public function paymentSuccess(Request $request)
    {
        $string = $request->razorpay_order_id . '|' . $request->razorpay_payment_id;
        $expectedSignature = hash_hmac('sha256', $string, config('services.razorpay.secret'));
        if (hash_equals($expectedSignature, $request->razorpay_signature) == true) {
            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
            $payment->update([
                'description' => 'Payment Success',
                'status' => RazorpayPaymentStatus::PAID,
                'payment_date' => now(),
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);
            return ApiResponse::success([], 'success');
        } else {
            return ApiResponse::error([], 'Invalid payment signature');
        }
    }
    public function paymentFailed(Request $request)
    {
        $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
        $payment->update([
            'description' => $request->description ?? 'Payment Failed',
            'status' => RazorpayPaymentStatus::FAILED,
            'payment_date' => now(),
            'razorpay_payment_id' => $request->razorpay_payment_id ?? null,
        ]);
        return  ApiResponse::success([], 'success');
    }
}
