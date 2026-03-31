<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user', 'items');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
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
}
