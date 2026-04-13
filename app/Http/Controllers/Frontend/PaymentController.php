<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AamarPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
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
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order already paid',
            ], 422);
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
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Handle aamarPay success callback
     */
    public function aamarPaySuccess(Request $request): JsonResponse
    {
        Log::info('AamarPay success callback', $request->all());

        $result = $this->aamarPayService->handleSuccess($request->all());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'order' => $result['order'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }

    /**
     * Handle aamarPay failure callback
     */
    public function aamarPayFail(Request $request): JsonResponse
    {
        Log::info('AamarPay fail callback', $request->all());

        $result = $this->aamarPayService->handleFailure($request->all());

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'order' => $result['order'] ?? null,
        ]);
    }

    /**
     * Handle aamarPay cancel callback
     */
    public function aamarPayCancel(Request $request): JsonResponse
    {
        Log::info('AamarPay cancel callback', $request->all());

        $orderNumber = $request->get('mer_txnid');
        
        if ($orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
            
            if ($order) {
                $order->update([
                    'payment_status' => 'cancelled',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment cancelled by user',
        ]);
    }

    /**
     * Get payment status
     */
    public function status(int $orderId): JsonResponse
    {
        $user = Auth::user();
        
        $order = Order::where('id', $orderId)
            ->where('user_id', $user?->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $status = $this->aamarPayService->getPaymentStatus($order);

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }
}
