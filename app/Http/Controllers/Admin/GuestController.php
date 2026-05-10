<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Subquery: aggregate guest data grouped by email
        $guestQuery = DB::table('guests')
            ->select(
                DB::raw('MAX(guests.id) as latest_guest_id'),
                DB::raw('MAX(guests.name) as name'),
                'guests.email',
                DB::raw('MAX(guests.phone) as phone'),
                DB::raw('MAX(guests.created_at) as last_order_date'),
                DB::raw('COUNT(DISTINCT guests.id) as guest_records'),
                DB::raw('COALESCE(SUM(order_counts.order_count), 0) as total_orders'),
                DB::raw('COALESCE(SUM(order_counts.total_spent), 0) as total_spent')
            )
            ->leftJoin(
                DB::raw('(SELECT guest_id, COUNT(*) as order_count, SUM(total_amount) as total_spent FROM orders WHERE guest_id IS NOT NULL GROUP BY guest_id) as order_counts'),
                'guests.id',
                '=',
                'order_counts.guest_id'
            )
            ->groupBy('guests.email')
            ->orderByDesc('last_order_date');

        if ($search) {
            $guestQuery->havingRaw(
                'MAX(guests.name) LIKE ? OR guests.email LIKE ? OR MAX(guests.phone) LIKE ?',
                ["%{$search}%", "%{$search}%", "%{$search}%"]
            );
        }

        $guests = $guestQuery->paginate(20)->withQueryString();

        return view('admin.guests.index', compact('guests', 'search'));
    }

    public function show(Request $request, string $email)
    {
        // Find all guest records with this email
        $guestRecords = Guest::where('email', $email)
            ->orWhere(function ($q) use ($email) {
                // Also include guests with same phone but different email
                $phone = Guest::where('email', $email)->value('phone');
                if ($phone) {
                    $q->where('phone', $phone);
                }
            })
            ->get();

        $guestIds = $guestRecords->pluck('id');

        // Get the most recent guest record for display
        $primaryGuest = $guestRecords->sortByDesc('created_at')->first();

        // Fetch all orders from all guest records
        $orders = Order::whereIn('guest_id', $guestIds)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSpent = $orders->sum('total_amount');
        $totalOrders = $orders->count();

        return view('admin.guests.show', compact(
            'primaryGuest',
            'guestRecords',
            'orders',
            'totalSpent',
            'totalOrders',
            'email'
        ));
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            // Delete all guest records with this email
            $deleted = Guest::where('email', $validated['email'])->delete();

            return redirect()->route('admin.guests.index')
                ->with('success', "Guest and {$deleted} associated records deleted successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete guest: ' . $e->getMessage());
        }
    }
}
