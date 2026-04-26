<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosHeldCart;
use App\Models\Product;
use App\Models\Variant;
use App\Services\PosOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PosController extends Controller
{
    protected PosOrderService $posOrderService;

    public function __construct(PosOrderService $posOrderService)
    {
        $this->posOrderService = $posOrderService;
    }
    /**
     * POS Main Interface
     */
    public function index()
    {
        // Get categories for filtering
        $categories = \App\Models\Category::active()->select('id', 'name')->get();
        
        // Get held carts count
        $heldCartsCount = PosHeldCart::byUser(Auth::id())->count();

        return view('admin.pos.index', compact('categories', 'heldCartsCount'));
    }

    /**
     * Search products for POS
     */
    public function searchProducts(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
        ]);

        $query = Product::query()
            ->with(['variants', 'category', 'mainImage'])
            ->where('is_active', true);

        // If no search and no category, show featured/popular products
        if (!empty($validated['search'])) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', $search);
            });
        }

        if (!empty($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        } else if (empty($validated['search'])) {
            // Show recent products when no filters applied
            $query->latest();
        }

        $products = $query->limit(20)->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->sale_price ?? $product->base_price,
                'wholesale_price' => $product->effective_wholesale_price ?? $product->wholesale_price,
                'stock' => $product->stock_quantity,
                'image' => $product->thumbnail_url,
                'has_variants' => $product->has_variants,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->variant_name,
                        'sku' => $variant->sku,
                        'price' => $variant->price,
                        'wholesale_price' => $variant->effective_wholesale_price ?? $variant->wholesale_price,
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
                    'wholesale_price' => $product->effective_wholesale_price ?? $product->wholesale_price,
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
                    'wholesale_price' => $variant->effective_wholesale_price ?? $variant->wholesale_price,
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
            'is_wholesale' => 'nullable|boolean',
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

        try {
            $order = $this->posOrderService->createOrder($validated);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (\Exception $e) {
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
        $order = PosOrder::with(['items', 'payments', 'user', 'courier', 'statusHistory.changedBy'])->findOrFail($id);
        
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
     * Search customers for POS
     */
    public function searchCustomers(Request $request)
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        $query = User::query();

        if (!empty($validated['query'])) {
            $search = $validated['query'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $customers = $query->select('id', 'name', 'mobile as phone', 'email')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $customers,
        ]);
    }

    /**
     * Daily report
     */
    public function dailyReport(Request $request)
    {
        $validated = $request->validate([
            'date' => 'nullable|date',
        ]);

        $date = $validated['date'] ?? now()->format('Y-m-d');
        
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
