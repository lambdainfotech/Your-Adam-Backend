<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosSession;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosPayment;
use App\Models\PosHeldCart;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    /**
     * POS Main Interface
     */
    public function index()
    {
        // Check for active session
        $activeSession = PosSession::active()
            ->byUser(Auth::id())
            ->first();

        if (!$activeSession) {
            return redirect()->route('admin.pos.session.create');
        }

        // Get categories for filtering
        $categories = \App\Models\Category::active()->select('id', 'name')->get();
        
        // Get held carts count
        $heldCartsCount = PosHeldCart::byUser(Auth::id())->count();

        return view('admin.pos.index', compact('activeSession', 'categories', 'heldCartsCount'));
    }

    /**
     * Show session opening form
     */
    public function createSession()
    {
        $activeSession = PosSession::active()->byUser(Auth::id())->first();
        
        if ($activeSession) {
            return redirect()->route('admin.pos.index');
        }

        return view('admin.pos.session.create');
    }

    /**
     * Open a new POS session
     */
    public function openSession(Request $request)
    {
        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
            'opening_note' => 'nullable|string|max:500',
        ]);

        // Check if user already has an active session
        if (PosSession::active()->byUser(Auth::id())->exists()) {
            return redirect()->route('admin.pos.index')
                ->with('error', 'You already have an active session.');
        }

        $session = PosSession::create([
            'user_id' => Auth::id(),
            'opening_amount' => $validated['opening_amount'],
            'opening_note' => $validated['opening_note'],
            'status' => 'active',
            'opened_at' => now(),
        ]);

        return redirect()->route('admin.pos.index')
            ->with('success', 'POS session opened successfully.');
    }

    /**
     * Close POS session
     */
    public function closeSession(Request $request)
    {
        $validated = $request->validate([
            'closing_amount' => 'required|numeric|min:0',
            'closing_note' => 'nullable|string|max:500',
        ]);

        $session = PosSession::active()->byUser(Auth::id())->firstOrFail();

        $session->update([
            'closing_amount' => $validated['closing_amount'],
            'closing_note' => $validated['closing_note'],
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'POS session closed successfully.');
    }

    /**
     * Search products for POS
     */
    public function searchProducts(Request $request)
    {
        $query = Product::query()
            ->with(['variants', 'category', 'mainImage'])
            ->where('is_active', true);

        // If no search and no category, show featured/popular products
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', $search);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        } else if (!$request->filled('search')) {
            // Show recent products when no filters applied
            $query->latest();
        }

        $products = $query->limit(20)->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->sale_price ?? $product->base_price,
                'stock' => $product->stock_quantity,
                'image' => $product->thumbnail_url,
                'has_variants' => $product->has_variants,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->variant_name,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'stock' => $variant->stock_quantity,
                    ];
                }),
            ];
        });

        return response()->json(['success' => true, 'data' => $products]);
    }

    /**
     * Find product by barcode
     */
    public function findByBarcode($barcode)
    {
        // Try product first
        $product = Product::where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->where('is_active', true)
            ->first();

        if ($product) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->sale_price ?? $product->base_price,
                    'stock' => $product->stock_quantity,
                    'has_variants' => $product->has_variants,
                    'type' => 'product',
                ],
            ]);
        }

        // Try variant
        $variant = Variant::where('sku', $barcode)
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->with('product')
            ->first();

        if ($variant) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'name' => $variant->product->name,
                    'variant_name' => $variant->variant_name,
                    'sku' => $variant->sku,
                    'price' => $variant->price,
                    'stock' => $variant->stock_quantity,
                    'has_variants' => false,
                    'type' => 'variant',
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found',
        ], 404);
    }

    /**
     * Hold a cart
     */
    public function holdCart(Request $request)
    {
        $validated = $request->validate([
            'cart_data' => 'required|array',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:255',
        ]);

        $heldCart = PosHeldCart::create([
            'user_id' => Auth::id(),
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'cart_data' => $validated['cart_data'],
            'note' => $validated['note'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart held successfully',
            'data' => ['id' => $heldCart->id],
        ]);
    }

    /**
     * Get held carts
     */
    public function getHeldCarts()
    {
        $carts = PosHeldCart::byUser(Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($cart) {
                return [
                    'id' => $cart->id,
                    'customer_name' => $cart->customer_name,
                    'item_count' => $cart->item_count,
                    'total' => $cart->cart_total,
                    'note' => $cart->note,
                    'created_at' => $cart->created_at->diffForHumans(),
                ];
            });

        return response()->json(['success' => true, 'data' => $carts]);
    }

    /**
     * Retrieve a held cart
     */
    public function retrieveCart($id)
    {
        $cart = PosHeldCart::byUser(Auth::id())->findOrFail($id);
        
        $cartData = $cart->cart_data;
        
        $cart->delete();

        return response()->json([
            'success' => true,
            'data' => $cartData,
        ]);
    }

    /**
     * Delete a held cart
     */
    public function deleteHeldCart($id)
    {
        $cart = PosHeldCart::byUser(Auth::id())->findOrFail($id);
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Held cart deleted',
        ]);
    }

    /**
     * Create a POS order
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,bkash,nagad,other',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string',
            'payments.*.received_amount' => 'nullable|numeric|min:0',
            'payments.*.change_amount' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:users,id',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'note' => 'nullable|string|max:500',
        ]);

        $session = PosSession::active()->byUser(Auth::id())->firstOrFail();

        try {
            DB::beginTransaction();

            // Create order
            $order = PosOrder::create([
                'pos_session_id' => $session->id,
                'user_id' => Auth::id(),
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'subtotal' => $validated['subtotal'],
                'discount_amount' => $validated['discount_amount'] ?? 0,
                'tax_amount' => $validated['tax_amount'] ?? 0,
                'total_amount' => $validated['total_amount'],
                'note' => $validated['note'] ?? null,
                'status' => 'completed',
            ]);

            // Create order items and deduct stock
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product_id']);
                $variant = null;
                $variantInfo = null;

                if (!empty($item['variant_id'])) {
                    $variant = Variant::find($item['variant_id']);
                    $variantInfo = $variant->variant_name ?? null;
                }

                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $product->name,
                    'sku' => $variant ? ($variant->sku ?? 'N/A') : ($product->sku ?? 'N/A'),
                    'variant_info' => $variantInfo,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);

                // Deduct stock
                if ($variant) {
                    $variant->decrement('stock_quantity', $item['quantity']);
                } else {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Create payments
            foreach ($validated['payments'] as $payment) {
                PosPayment::create([
                    'pos_order_id' => $order->id,
                    'payment_method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'reference_number' => $payment['reference'] ?? null,
                    'received_amount' => $payment['received_amount'] ?? null,
                    'change_amount' => $payment['change_amount'] ?? null,
                ]);

                // Update session sales totals
                switch ($payment['method']) {
                    case 'cash':
                        $session->increment('cash_sales', $payment['amount']);
                        break;
                    case 'card':
                        $session->increment('card_sales', $payment['amount']);
                        break;
                    case 'bkash':
                    case 'nagad':
                        $session->increment('mobile_sales', $payment['amount']);
                        break;
                    default:
                        $session->increment('other_sales', $payment['amount']);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show POS order details
     */
    public function showOrder($id)
    {
        $order = PosOrder::with(['items', 'payments', 'user', 'session', 'courier', 'statusHistory.changedBy'])->findOrFail($id);
        
        return view('admin.pos.show', compact('order'));
    }

    /**
     * Get receipt data
     */
    public function getReceipt($id)
    {
        $order = PosOrder::with(['items', 'payments', 'user'])->findOrFail($id);

        return view('admin.pos.receipt', compact('order'));
    }

    /**
     * Get print-friendly receipt
     */
    public function printReceipt($id)
    {
        $order = PosOrder::with(['items', 'payments', 'user'])->findOrFail($id);

        return view('admin.pos.print', compact('order'));
    }

    /**
     * Daily report
     */
    public function dailyReport(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');
        
        $orders = PosOrder::completed()
            ->whereDate('created_at', $date)
            ->with(['payments'])
            ->get();

        $summary = [
            'total_orders' => $orders->count(),
            'total_sales' => $orders->sum('total_amount'),
            'cash_sales' => $orders->sum(fn($o) => $o->payment_method_summary['cash'] ?? 0),
            'card_sales' => $orders->sum(fn($o) => $o->payment_method_summary['card'] ?? 0),
            'mobile_sales' => $orders->sum(fn($o) => ($o->payment_method_summary['bkash'] ?? 0) + ($o->payment_method_summary['nagad'] ?? 0)),
            'total_items' => $orders->sum(fn($o) => $o->items->sum('quantity')),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'date' => $date,
                'summary' => $summary,
                'orders' => $orders->map(fn($o) => [
                    'order_number' => $o->order_number,
                    'total' => $o->total_amount,
                    'items' => $o->items->sum('quantity'),
                    'time' => $o->created_at->format('H:i'),
                ]),
            ],
        ]);
    }

    /**
     * Update delivery status
     */
    public function updateDeliveryStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'delivery_status' => 'required|in:pending,processing,ready,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500',
            'tracking_number' => 'nullable|string|max:100',
            'courier_id' => 'nullable|exists:couriers,id',
            'estimated_delivery_date' => 'nullable|date',
        ]);

        $order = PosOrder::findOrFail($id);

        // Update additional fields if provided
        $updateData = [];
        if ($request->filled('tracking_number')) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }
        if ($request->filled('courier_id')) {
            $updateData['courier_id'] = $validated['courier_id'];
        }
        if ($request->filled('estimated_delivery_date')) {
            $updateData['estimated_delivery_date'] = $validated['estimated_delivery_date'];
        }
        if ($request->filled('delivery_address')) {
            $updateData['delivery_address'] = $request->delivery_address;
        }

        if (!empty($updateData)) {
            $order->update($updateData);
        }

        // Update status and create history
        $order->updateDeliveryStatus(
            $validated['delivery_status'],
            $validated['notes'] ?? null,
            Auth::id()
        );

        return redirect()->back()
            ->with('success', 'Delivery status updated successfully.');
    }

    /**
     * Get tracking timeline
     */
    public function getTrackingTimeline($id)
    {
        $order = PosOrder::with(['statusHistory.changedBy', 'courier'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'delivery_status' => $order->delivery_status,
                'tracking_number' => $order->tracking_number,
                'courier' => $order->courier?->name,
                'estimated_delivery' => $order->estimated_delivery_date?->format('M d, Y'),
                'delivered_at' => $order->delivered_at?->format('M d, Y H:i'),
                'timeline' => $order->statusHistory->map(fn($h) => [
                    'status' => $h->status,
                    'previous_status' => $h->previous_status,
                    'notes' => $h->notes,
                    'changed_by' => $h->changedBy?->name ?? 'System',
                    'date' => $h->created_at->format('M d, Y H:i'),
                ]),
            ],
        ]);
    }
}
