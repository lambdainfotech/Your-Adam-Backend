<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Courier;
use App\Models\CourierAssignment;
use App\Models\TrackingHistory;
use Illuminate\Http\Request;

class OrderFulfillmentController extends Controller
{
    public function assignCourier(Request $request, Order $order)
    {
        $validated = $request->validate([
            'courier_id' => 'required|exists:couriers,id',
            'tracking_number' => 'required|string|max:100',
            'shipping_cost' => 'required|numeric|min:0',
            'estimated_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        
        $courier = Courier::findOrFail($validated['courier_id']);
        
        // Create or update courier assignment
        $assignment = CourierAssignment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'courier_id' => $validated['courier_id'],
                'tracking_number' => $validated['tracking_number'],
                'shipping_cost' => $validated['shipping_cost'],
                'estimated_delivery_date' => $validated['estimated_delivery_date'],
                'status' => 'assigned',
                'notes' => $validated['notes'],
            ]
        );
        
        // Update order status
        $order->update(['status' => 'shipped']);
        
        // Add status history
        $order->addStatusHistory('shipped', 'Courier assigned: ' . $courier->name);
        
        // Add initial tracking history
        TrackingHistory::create([
            'courier_assignment_id' => $assignment->id,
            'status' => 'assigned',
            'description' => 'Order assigned to ' . $courier->name,
            'location' => 'Warehouse',
            'tracked_at' => now(),
        ]);
        
        return redirect()->back()
            ->with('success', 'Courier assigned successfully.');
    }

    public function updateTracking(Request $request, CourierAssignment $assignment)
    {
        $validated = $request->validate([
            'status' => 'required|string|max:50',
            'location' => 'nullable|string|max:255',
            'description' => 'required|string',
        ]);
        
        $validated['tracked_at'] = now();
        
        TrackingHistory::create([
            'courier_assignment_id' => $assignment->id,
            ...$validated,
        ]);
        
        // Update assignment status
        $assignment->update(['status' => $validated['status']]);
        
        // Update order status if delivered
        if ($validated['status'] === 'delivered') {
            $assignment->order->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
            
            // Auto-update payment status for COD orders
            if ($assignment->order->payment_method === 'cod') {
                $assignment->order->payment_status = 'paid';
                $assignment->order->save();
            }
            
            $assignment->update(['delivered_at' => now()]);
            $assignment->order->addStatusHistory('delivered', 'Package delivered');
        }
        
        return redirect()->back()
            ->with('success', 'Tracking updated successfully.');
    }

    public function markShipped(Request $request, Order $order)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        $order->update(['status' => 'shipped']);
        $order->addStatusHistory('shipped', $validated['notes'] ?? 'Order shipped');
        
        return redirect()->back()
            ->with('success', 'Order marked as shipped.');
    }

    public function markDelivered(Request $request, Order $order)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        $order->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
        
        // Auto-update payment status for COD orders
        if ($order->payment_method === 'cod') {
            $order->payment_status = 'paid';
            $order->save();
        }
        
        $order->addStatusHistory('delivered', $validated['notes'] ?? 'Order delivered');
        
        // Update courier assignment if exists
        if ($order->courierAssignment) {
            $order->courierAssignment->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        }
        
        return redirect()->back()
            ->with('success', 'Order marked as delivered.');
    }

    public function getTrackingTimeline(Order $order)
    {
        $order->load(['courierAssignment.trackingHistory', 'courierAssignment.courier', 'statusHistory.changedBy']);
        
        return view('admin.orders.tracking', compact('order'));
    }

    public function couriers()
    {
        $couriers = Courier::active()->ordered()->get();
        
        return response()->json($couriers);
    }
}
