<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PosOrder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,delivered,completed,cancelled',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'page' => 'nullable|integer|min:1',
        ]);

        $perPage = 20;
        $page = $validated['page'] ?? 1;

        // Get regular orders (paginated)
        $regularOrdersQuery = Order::with('user', 'items')
            ->orderBy('created_at', 'desc');

        if (!empty($validated['status'])) {
            $regularOrdersQuery->where('status', $validated['status']);
        }
        if (!empty($validated['from_date'])) {
            $regularOrdersQuery->whereDate('created_at', '>=', $validated['from_date']);
        }
        if (!empty($validated['to_date'])) {
            $regularOrdersQuery->whereDate('created_at', '<=', $validated['to_date']);
        }

        $regularOrders = $regularOrdersQuery->paginate($perPage, ['*'], 'page', $page);

        // Get POS orders (paginated)
        $posOrdersQuery = PosOrder::with('user', 'items')
            ->orderBy('created_at', 'desc');

        if (!empty($validated['status'])) {
            $posOrdersQuery->where('status', $validated['status']);
        }
        if (!empty($validated['from_date'])) {
            $posOrdersQuery->whereDate('created_at', '>=', $validated['from_date']);
        }
        if (!empty($validated['to_date'])) {
            $posOrdersQuery->whereDate('created_at', '<=', $validated['to_date']);
        }

        $posOrders = $posOrdersQuery->paginate($perPage, ['*'], 'page', $page);

        // Map to unified structure
        $mappedRegular = $regularOrders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user?->name ?? 'Guest',
                'total' => $order->total_amount,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'delivery_status' => $order->status,
                'tracking_number' => $order->tracking_number,
                'created_at' => $order->created_at,
                'type' => 'online',
                'source' => 'Website',
            ];
        });

        $mappedPos = $posOrders->getCollection()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name ?? $order->user?->name ?? 'Walk-in Customer',
                'total' => $order->total_amount,
                'status' => $order->status,
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'delivery_status' => $order->delivery_status,
                'tracking_number' => $order->tracking_number,
                'created_at' => $order->created_at,
                'type' => 'pos',
                'source' => 'POS',
            ];
        });

        // Merge only current page results, sort, and take perPage
        $merged = $mappedRegular->merge($mappedPos)
            ->sortByDesc('created_at')
            ->values()
            ->take($perPage);

        $total = $regularOrders->total() + $posOrders->total();

        $orders = new LengthAwarePaginator(
            $merged,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'items.variant.product');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $order->status = $validated['status'];
        
        // Auto-update payment status for COD orders when delivered/completed
        if ($order->payment_method === 'cod' && in_array($validated['status'], ['delivered', 'completed'])) {
            $order->payment_status = 'paid';
        }
        
        $order->save();
        
        return redirect()->back()->with('success', 'Order status updated successfully.');
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
        ]);
        
        $order->payment_status = $validated['payment_status'];
        $order->save();
        
        return redirect()->back()->with('success', 'Payment status updated successfully.');
    }
    
    public function invoice(Order $order)
    {
        $order->load('user', 'items.variant.product');
        return view('admin.orders.invoice', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load('user', 'items.variant.product');
        return view('admin.orders.print', compact('order'));
    }
}
