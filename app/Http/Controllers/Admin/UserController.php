<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $users = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();
        
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('role', 'addresses', 'orders');
        return view('admin.users.show', compact('user'));
    }
    
    public function toggleStatus(User $user)
    {
        $user->status = $user->status === 1 ? 0 : 1;
        $user->save();
        
        $status = $user->status === 1 ? 'activated' : 'deactivated';
        
        return redirect()->route('admin.users.index')
            ->with('success', "User {$status} successfully.");
    }
}
