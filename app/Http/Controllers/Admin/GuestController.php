<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::withCount('orders')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $guests = $query->paginate(20)->withQueryString();

        return view('admin.guests.index', compact('guests'));
    }

    public function show(Guest $guest)
    {
        $guest->load(['orders' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('admin.guests.show', compact('guest'));
    }
}
