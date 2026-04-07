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
        // Get regular orders
        $regularOrdersQuery = Order::with('user', 'items');
        
        if ($request->filled('status')) {
            $regularOrdersQuery->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $regularOrdersQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $regularOrdersQuery->whereDate('created_at', '<=', $request->to_date);
        }
        
        $regularOrders = collect($regularOrdersQuery->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user?->name ?? 'Guest',
                'total' => $order->total_amount,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'type' => 'online',
                'source' => 'Website',
            ];
        }));

        // Get POS orders
        $posOrdersQuery = PosOrder::with('user', 'items');
        
        if ($request->filled('status')) {
            $posOrdersQuery->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $posOrdersQuery->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $posOrdersQuery->whereDate('created_at', '<=', $request->to_date);
        }
        
        $posOrders = collect($posOrdersQuery->get()->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name ?? $order->user?->name ?? 'Walk-in Customer',
                'total' => $order->total_amount,
                'status' => $order->status,
                'delivery_status' => $order->delivery_status,
                'tracking_number' => $order->tracking_number,
                'created_at' => $order->created_at,
                'type' => 'pos',
                'source' => 'POS',
            ];
        }));

        // Merge and sort orders
        $allOrders = $regularOrders->merge($posOrders)
            ->sortByDesc('created_at')
            ->values();

        // Paginate manually
        $perPage = 20;
        $page = $request->get('page', 1);
        $total = $allOrders->count();
        
        $orders = new LengthAwarePaginator(
            $allOrders->forPage($page, $perPage),
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
        $order->load('user', 'items.variant.product', 'address');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,completed,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $order->status = $validated['status'];
        $order->save();
        
        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
    
    public function invoice(Order $order)
    {
        $order->load('user', 'items.variant.product', 'address');
        return view('admin.orders.invoice', compact('order'));
    }

    public function print(Order $order)
    {
        $order->load('user', 'items.variant.product', 'address');
        return view('admin.orders.print', compact('order'));
    }
}
