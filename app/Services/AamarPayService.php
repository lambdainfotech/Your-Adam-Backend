<?php

namespace App\Services;

use App\Models\Order as LegacyOrder;
use App\Modules\Sales\Models\Order as ModuleOrder;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AamarPayService
{
    private string $storeId;
    private string $signatureKey;
    private bool $isSandbox;
    private string $baseUrl;

    public function __construct()
    {
        $settings = Setting::allSettings();
        
        $this->storeId = $settings['aamarpay_store_id'] ?? env('AAMARPAY_STORE_ID', '');
        $this->signatureKey = $settings['aamarpay_signature_key'] ?? env('AAMARPAY_SIGNATURE_KEY', '');
        $mode = $settings['aamarpay_mode'] ?? env('AAMARPAY_SANDBOX', 'sandbox');
        $this->isSandbox = $mode === 'sandbox' || $mode === 'true';
        
        $this->baseUrl = $this->isSandbox 
            ? 'https://sandbox.aamarpay.com'
            : 'https://secure.aamarpay.com';
    }

    /**
     * Initiate payment
     */
    public function initiatePayment($order, array $customerInfo): array
    {
        try {
            $settings = Setting::allSettings();
            
            // Use exact order_number as transaction ID so callbacks can find the order
            $tranId = $order->order_number;
            
            $payload = [
                'store_id' => $this->storeId,
                'signature_key' => $this->signatureKey,
                'tran_id' => $tranId,
                'amount' => number_format($order->total_amount, 2, '.', ''),
                'currency' => $order->currency ?? 'BDT',
                'desc' => 'Order #' . $order->order_number,
                'cus_name' => $customerInfo['name'] ?? 'Customer',
                'cus_email' => $customerInfo['email'] ?? 'customer@example.com',
                'cus_add1' => $customerInfo['address'] ?? 'Dhaka',
                'cus_add2' => $customerInfo['address2'] ?? '',
                'cus_city' => $customerInfo['city'] ?? 'Dhaka',
                'cus_state' => $customerInfo['state'] ?? '',
                'cus_postcode' => $customerInfo['postcode'] ?? '1200',
                'cus_country' => $customerInfo['country'] ?? 'Bangladesh',
                'cus_phone' => $customerInfo['phone'] ?? '01700000000',
                'opt_a' => $order->id, // Store order ID
                'opt_b' => '',
                'opt_c' => '',
                'opt_d' => '',
                'success_url' => $this->getCallbackUrl('success'),
                'fail_url' => $this->getCallbackUrl('fail'),
                'cancel_url' => $this->getCallbackUrl('cancel'),
                'type' => 'json',
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/jsonpost.php", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['payment_url']) && ($data['result'] ?? '') === 'true') {
                    return [
                        'success' => true,
                        'paymentUrl' => $data['payment_url'],
                        'orderId' => $order->order_number,
                        'amount' => $order->total_amount,
                        'gateway' => 'aamarpay',
                    ];
                }
            }

            Log::error('AamarPay initiation failed', [
                'order' => $order->order_number,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => 'Payment initiation failed',
                'error' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('AamarPay error: ' . $e->getMessage(), [
                'order' => $order->order_number,
            ]);

            return [
                'success' => false,
                'message' => 'Payment service error',
            ];
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment(string $orderNumber, string $requestId): array
    {
        try {
            $url = "{$this->baseUrl}/api/v1/trxcheck/request.php";
            
            $params = [
                'request_id' => $requestId,
                'store_id' => $this->storeId,
                'signature_key' => $this->signatureKey,
                'type' => 'json',
            ];

            $response = Http::get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['pay_status'] ?? 'unknown',
                    'amount' => $data['amount'] ?? 0,
                    'transactionId' => $data['pg_txnid'] ?? null,
                    'orderNumber' => $data['mer_txnid'] ?? $orderNumber,
                    'paymentMethod' => $data['payment_type'] ?? null,
                    'raw' => $data,
                ];
            }

            return [
                'success' => false,
                'message' => 'Verification failed',
            ];

        } catch (\Exception $e) {
            Log::error('AamarPay verification error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Verification error',
            ];
        }
    }

    /**
     * Handle payment success callback
     */
    public function handleSuccess(array $data): array
    {
        $orderNumber = $data['mer_txnid'] ?? null;
        $payStatus = $data['pay_status'] ?? null;
        $amount = $data['amount'] ?? 0;
        $storeId = $data['store_id'] ?? null;
        
        if (!$orderNumber) {
            return [
                'success' => false,
                'message' => 'Invalid callback data',
            ];
        }

        // Verify store_id matches configuration
        if ($storeId && $storeId !== $this->storeId) {
            Log::warning('AamarPay callback store_id mismatch', ['expected' => $this->storeId, 'received' => $storeId]);
            return [
                'success' => false,
                'message' => 'Invalid store configuration',
            ];
        }

        // Aamarpay returns mer_txnid = tran_id. We now use exact order_number as tran_id,
        // but for backward compatibility also handle the old format (order_number + '-' + suffix).
        $searchOrderNumber = preg_replace('/-[a-f0-9]{4,}$/i', '', $orderNumber);
        $order = $this->findOrder($searchOrderNumber);
        
        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found',
            ];
        }

        // Idempotency: already paid
        if ($order->payment_status === 'paid') {
            return [
                'success' => true,
                'order' => $order,
                'message' => 'Payment already processed',
            ];
        }

        // Verify amount matches order total (allow tiny float tolerance)
        if (abs((float) $amount - (float) $order->total_amount) > 0.01) {
            Log::warning('AamarPay amount mismatch', [
                'order' => $orderNumber,
                'expected' => $order->total_amount,
                'received' => $amount,
            ]);
            return [
                'success' => false,
                'message' => 'Amount mismatch',
            ];
        }

        // Server-side verification via AamarPay API
        $requestId = $data['pg_txnid'] ?? $data['opt_a'] ?? null;
        if ($requestId && $this->signatureKey) {
            $verification = $this->verifyPayment($orderNumber, $requestId);
            if (!$verification['success'] || ($verification['status'] ?? '') !== 'Successful') {
                Log::warning('AamarPay server verification failed', [
                    'order' => $orderNumber,
                    'verification' => $verification,
                ]);
                return [
                    'success' => false,
                    'message' => 'Payment verification failed',
                ];
            }
        }

        // Update order status
        if ($payStatus === 'Successful') {
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $data['payment_type'] ?? 'aamarpay',
                'transaction_id' => $data['pg_txnid'] ?? null,
            ]);

            return [
                'success' => true,
                'order' => $order,
                'message' => 'Payment successful',
            ];
        }

        return [
            'success' => false,
            'order' => $order,
            'message' => 'Payment not successful',
            'status' => $payStatus,
        ];
    }

    /**
     * Handle payment failure
     */
    public function handleFailure(array $data): array
    {
        $orderNumber = $data['mer_txnid'] ?? null;
        
        if ($orderNumber) {
            $searchOrderNumber = preg_replace('/-[a-f0-9]{4,}$/i', '', $orderNumber);
            $order = $this->findOrder($searchOrderNumber);
            
            if ($order) {
                $order->update([
                    'payment_status' => 'failed',
                    'admin_notes' => 'Payment failed: ' . ($data['pay_status'] ?? 'Unknown'),
                ]);

                return [
                    'success' => false,
                    'order' => $order,
                    'message' => 'Payment failed',
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Payment failed',
        ];
    }

    /**
     * Build backend callback URL for aamarpay redirects.
     * Aamarpay redirects directly to the backend, and the backend
     * then redirects the browser to the frontend success/fail/cancel pages.
     */
    private function getCallbackUrl(string $type): string
    {
        return route("api.payment.aamarpay.{$type}");
    }

    /**
     * Find an order by order_number across both legacy and module tables.
     * First tries exact match, then strips old 4-char tran_id suffix for backward compatibility.
     */
    private function findOrder(string $orderNumber)
    {
        // 1. Try exact match first
        $order = LegacyOrder::where('order_number', $orderNumber)->first();
        if ($order) {
            return $order;
        }

        $order = ModuleOrder::where('order_number', $orderNumber)->first();
        if ($order) {
            return $order;
        }

        // 2. Backward compatibility: old code appended '-XXXX' (4 hex chars) to tran_id
        $stripped = preg_replace('/-[a-f0-9]{4}$/i', '', $orderNumber);
        if ($stripped !== $orderNumber) {
            $order = LegacyOrder::where('order_number', $stripped)->first();
            if ($order) {
                return $order;
            }

            return ModuleOrder::where('order_number', $stripped)->first();
        }

        return null;
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus($order): array
    {
        return [
            'orderNumber' => $order->order_number,
            'paymentStatus' => $order->payment_status,
            'paymentMethod' => $order->payment_method,
            'amount' => $order->total_amount,
            'transactionId' => $order->transaction_id,
        ];
    }
}
