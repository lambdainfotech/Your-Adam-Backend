<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function create()
    {
        // Get Customer role first, then other roles
        $roles = Role::where('name', '!=', 'Super Admin')
            ->orderByRaw("CASE WHEN name = 'Customer' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        try {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:0,1',
        ]);

        // Auto-generate password if not provided
        $password = $validated['password'] ?? $this->generateRandomPassword();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($password),
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
            'email_verified_at' => now(),
        ]);

        $message = 'Customer created successfully.';
        if (empty($validated['password'])) {
            $message .= " Auto-generated password: {$password}";
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create user: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function generateRandomPassword(): string
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    public function show(User $user)
    {
        $user->load('role', 'addresses', 'orders');
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::where('name', '!=', 'Super Admin')->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'mobile' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:0,1',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'role_id' => $validated['role_id'],
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
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
