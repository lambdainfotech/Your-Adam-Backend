<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order as LegacyOrder;
use App\Models\Setting;
use App\Modules\Sales\Models\Order as ModuleOrder;
use App\Services\AamarPayService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    use ApiResponse;
    private AamarPayService $aamarPayService;

    public function __construct(AamarPayService $aamarPayService)
    {
        $this->aamarPayService = $aamarPayService;
    }

    /**
     * Initiate aamarPay payment
     */
    public function initiate(int $orderId, Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return $this->error('Authentication required', 401);
            }

            $order = LegacyOrder::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                $order = ModuleOrder::where('id', $orderId)
                    ->where('user_id', $user->id)
                    ->first();
            }

            if (!$order) {
                return $this->error('Order not found', 404);
            }

            if ($order->payment_status === 'paid') {
                return $this->error('Order already paid', 422);
            }

            // Block payment initiation for COD orders
            if ($order->payment_method === 'cod') {
                return $this->error('COD orders do not require online payment', 422);
            }

            $customerInfo = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $request->get('phone', $user->phone ?? '01700000000'),
                'address' => $request->get('address'),
                'city' => $request->get('city', 'Dhaka'),
                'postcode' => $request->get('postcode', '1200'),
                'country' => 'Bangladesh',
            ];

            $result = $this->aamarPayService->initiatePayment($order, $customerInfo);

            if (!$result['success']) {
                return $this->error($result['message'], 500);
            }

            return $this->success($result, 'Payment initiated successfully');
        } catch (\Exception $e) {
            Log::error('Payment initiation failed: ' . $e->getMessage(), ['order_id' => $orderId]);

            return $this->error('Payment initiation failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle aamarPay success callback.
     * Aamarpay redirects the browser here (POST). We process the payment
     * and then redirect the user to the frontend checkout/success page.
     */
    public function aamarPaySuccess(Request $request): RedirectResponse|JsonResponse
    {
        Log::info('AamarPay success callback', $request->all());

        $data = $request->all();
        $orderNumber = $data['mer_txnid'] ?? null;

        // Direct order lookup bypassing AamarPayService to avoid cached old code
        $order = null;
        if ($orderNumber) {
            $search = preg_replace('/-[a-f0-9]{4}$/i', '', $orderNumber);
            $order = LegacyOrder::where('order_number', $search)->first();
            if (!$order) {
                $order = ModuleOrder::where('order_number', $search)->first();
            }
        }

        if (!$order) {
            if ($request->expectsJson()) {
                return $this->error('Order not found', 400);
            }
            return $this->redirectToFrontend('fail', null, 'Order not found');
        }

        $payStatus = $data['pay_status'] ?? null;
        $amount = $data['amount'] ?? 0;

        // Idempotency
        if ($order->payment_status === 'paid') {
            if ($request->expectsJson()) {
                return $this->success($order, 'Payment already processed');
            }
            return $this->redirectToFrontend('success', $order);
        }

        // Amount check
        if (abs((float) $amount - (float) $order->total_amount) > 0.01) {
            Log::warning('AamarPay amount mismatch', [
                'order' => $orderNumber,
                'expected' => $order->total_amount,
                'received' => $amount,
            ]);
            if ($request->expectsJson()) {
                return $this->error('Amount mismatch', 400);
            }
            return $this->redirectToFrontend('fail', $order, 'Amount mismatch');
        }

        // Update order
        if ($payStatus === 'Successful') {
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $data['payment_type'] ?? 'aamarpay',
                'transaction_id' => $data['pg_txnid'] ?? null,
            ]);

            if ($request->expectsJson()) {
                return $this->success($order, 'Payment successful');
            }
            return $this->redirectToFrontend('success', $order);
        }

        if ($request->expectsJson()) {
            return $this->error('Payment not successful: ' . $payStatus, 400);
        }
        return $this->redirectToFrontend('fail', $order, 'Payment not successful: ' . $payStatus);
    }

    /**
     * Handle aamarPay failure callback.
     * Redirects browser to frontend checkout/fail page.
     */
    public function aamarPayFail(Request $request): RedirectResponse|JsonResponse
    {
        Log::info('AamarPay fail callback', $request->all());

        $data = $request->all();
        $orderNumber = $data['mer_txnid'] ?? null;
        $order = null;

        if ($orderNumber) {
            $search = preg_replace('/-[a-f0-9]{4}$/i', '', $orderNumber);
            $order = LegacyOrder::where('order_number', $search)->first();
            if (!$order) {
                $order = ModuleOrder::where('order_number', $search)->first();
            }
        }

        if ($order && $order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'failed',
                'admin_notes' => 'Payment failed: ' . ($data['pay_status'] ?? 'Unknown'),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'data' => $order ?? null,
            ], 200);
        }

        return $this->redirectToFrontend('fail', $order);
    }

    /**
     * Handle aamarPay cancel callback.
     * Redirects browser to frontend checkout/cancel page.
     */
    public function aamarPayCancel(Request $request): RedirectResponse|JsonResponse
    {
        Log::info('AamarPay cancel callback', $request->all());

        $orderNumber = $request->get('mer_txnid');
        $order = null;
        
        if ($orderNumber) {
            $order = LegacyOrder::where('order_number', $orderNumber)->first();
            if (!$order) {
                $order = ModuleOrder::where('order_number', $orderNumber)->first();
            }
            if (!$order) {
                $stripped = preg_replace('/-[a-f0-9]{4}$/i', '', $orderNumber);
                if ($stripped !== $orderNumber) {
                    $order = LegacyOrder::where('order_number', $stripped)->first();
                    if (!$order) {
                        $order = ModuleOrder::where('order_number', $stripped)->first();
                    }
                }
            }
            
            if ($order) {
                if ($order->payment_status !== 'paid') {
                    $order->update([
                        'payment_status' => 'cancelled',
                    ]);
                }
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment cancelled by user',
            ], 200);
        }

        return $this->redirectToFrontend('cancel', $order);
    }

    /**
     * Redirect browser to frontend checkout page after aamarpay callback.
     */
    private function redirectToFrontend(string $type, $order = null, string $message = null): RedirectResponse
    {
        $settings = Setting::allSettings();
        $frontendUrl = $settings['frontend_url'] ?? null;

        if ($frontendUrl) {
            $url = rtrim($frontendUrl, '/') . "/checkout/{$type}";
        } else {
            $url = route('api.payment.aamarpay.' . $type);
        }

        if ($order) {
            $url .= '?order=' . urlencode($order->order_number);
            if ($message) {
                $url .= '&message=' . urlencode($message);
            }
        } elseif ($message) {
            $url .= '?message=' . urlencode($message);
        }

        return redirect()->away($url);
    }

    /**
     * Get payment status
     */
    public function status(int $orderId): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return $this->error('Unauthorized. Please login to view payment status.', 401);
        }

        $order = LegacyOrder::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            $order = ModuleOrder::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$order) {
            return $this->error('Order not found', 404);
        }

        $status = $this->aamarPayService->getPaymentStatus($order);

        return $this->success($status, 'Payment status retrieved successfully');
    }
}
