<?php

namespace App\Http\Controllers;

use App\Enums\RazorpayPaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;

class WebhookController extends Controller
{

    public function __invoke(Request $request)
    {
        $webhookSecret = config('freshman.razor_pay_webhook_secret');
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        try {
            $api = new  Api(
                config('freshman.razor_pay_key'),
                config('freshman.razor_pay_secret')
            );
            $api->utility->verifyWebhookSignature($payload, $signature, $webhookSecret);
            $event = $request->input('event');
            switch ($event) {
                case 'payment.authorized':
                    $payloadData = $request->input('payload.payment.entity');
                    $orderId = $payloadData['order_id'];
                    $paymentId = $payloadData['id'];
                    $paymentMethod = $payloadData['method'];
                    $payment = Payment::where('razorpay_order_id', $orderId)->firstOrFail();
                    $payment->update([
                        'description' => 'Payment Authorized',
                        'payment_status' => RazorpayPaymentStatus::AUTHORIZED,
                        'razorpay_payment_id' => $paymentId,
                        'payment_date' => now(),
                        'event' => $event,
                        'is_webhook' => true,
                    ]);
                    break;
                case 'payment.captured':
                    $payloadData = $request->input('payload.payment.entity');
                    $orderId = $payloadData['order_id'];
                    $paymentId = $payloadData['id'];
                    $paymentMethod = $payloadData['method'];
                    DB::transaction(function () use ($orderId, $paymentId, $paymentMethod, $event) {
                        $monthlyBilling = MonthlyBilling::where('razorpay_order_id', $orderId)->where('paid', false)->latest('id')->first();
                        if ($monthlyBilling) {
                            $monthlyBilling->update([
                                'razorpay_payment_id' => $paymentId,
                                'paid' => true,
                                'payment_status' => RazorpayPaymentStatus::PAID,
                                'updated_at' => now(),
                            ]);
                        } else {
                            $arrearBilling = ArrearBilling::where('razorpay_order_id', $orderId)->where('paid', false)->latest('id')->first();
                            $arrearBilling->update([
                                'razorpay_payment_id' => $paymentId,
                                'paid' => true,
                                'payment_status' => RazorpayPaymentStatus::PAID,
                                'updated_at' => now(),
                            ]);
                        }
                        $payment = Payment::where('razorpay_order_id', $orderId)->where('paid', false)->firstOrFail();
                        $payment->update([
                            'description' => 'Payment Captured',
                            'payment_status' => RazorpayPaymentStatus::PAID,
                            'payment_date' => now(),
                            'razorpay_payment_id' => $paymentId,
                            'paid' => true,
                            'payment_method' => $paymentMethod,
                            'event' => $event,
                            'is_webhook' => true,
                        ]);
                    });
                    break;
                case 'order.paid':
                    // $orderData = $request->input('payload.order.entity');
                    // $orderId = $orderData['id'];
                    // $paymentId = $orderData['payments'][0]['id'] ?? null;
                    // $paymentMethod = $orderData['method'];
                    $payloadData = $request->input('payload.payment.entity');
                    $orderId = $payloadData['order_id'];
                    $paymentId = $payloadData['id'];
                    $paymentMethod = $payloadData['method'];
                    $payment = Payment::where('razorpay_order_id', $orderId)->where('paid', false)->firstOrFail();
                    $payment->update([
                        'description' => 'Payment Order Paid',
                        'payment_status' => RazorpayPaymentStatus::PAID,
                        'razorpay_payment_id' => $paymentId,
                        'payment_date' => now(),
                        'event' => $event,
                        'payment_method' => $paymentMethod,
                        'is_webhook' => true,
                    ]);
                    break;
                case 'payment.failed':
                    $payloadData = $request->input('payload.payment.entity');
                    $orderId = $payloadData['order_id'];
                    $paymentId = $payloadData['id'];
                    $paymentMethod = $payloadData['method'];
                    DB::transaction(function () use ($orderId, $paymentId, $paymentMethod, $event) {
                        $monthlyBilling = MonthlyBilling::where('razorpay_order_id', $orderId)->where('paid', false)->latest('id')->first();
                        if ($monthlyBilling) {
                            $monthlyBilling->update([
                                'razorpay_payment_id' => $paymentId,
                                'paid' => false,
                                'payment_status' => RazorpayPaymentStatus::FAILED,
                                'updated_at' => now(),
                            ]);
                        } else {
                            $arrearBilling = ArrearBilling::where('razorpay_order_id', $orderId)->where('paid', false)->latest('id')->first();
                            $arrearBilling->update([
                                'razorpay_payment_id' => $paymentId,
                                'paid' => false,
                                'payment_status' => RazorpayPaymentStatus::FAILED,
                                'updated_at' => now(),
                            ]);
                        }
                        $payment = Payment::where('razorpay_order_id', $orderId)->where('paid', false)->firstOrFail();
                        $payment->update([
                            'description' => 'Webhook Payment Failed',
                            'payment_status' => RazorpayPaymentStatus::FAILED,
                            'payment_date' => now(),
                            'razorpay_payment_id' => $paymentId,
                            'paid' => false,
                            'payment_method' => $paymentMethod,
                            'event' => $event,
                            'is_webhook' => true,
                        ]);
                    });
                    break;
                default:
                    break;
            }
            return $this->respondOk();
        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage());
        }
    }
}