<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $query = Permission::withCount('roles');
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->filled('module')) {
            $query->byModule($request->module);
        }
        
        $permissions = $query->orderBy('module')->orderBy('name')->paginate(50)->withQueryString();
        $modules = Permission::select('module')->distinct()->pluck('module');
        
        return view('admin.permissions.index', compact('permissions', 'modules'));
    }

    public function create()
    {
        $modules = Permission::select('module')->distinct()->pluck('module');
        $actions = ['view', 'create', 'edit', 'delete', 'manage', 'export', 'import'];
        
        return view('admin.permissions.create', compact('modules', 'actions'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized action.');
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions',
            'module' => 'required|string|max:50',
            'action' => 'required|string|max:20',
            'description' => 'nullable|string|max:255',
        ]);
        
        $validated['slug'] = Str::slug($validated['module'] . '-' . $validated['action']);
        
        Permission::create($validated);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission)
    {
        $modules = Permission::select('module')->distinct()->pluck('module');
        $actions = ['view', 'create', 'edit', 'delete', 'manage', 'export', 'import'];
        
        return view('admin.permissions.edit', compact('permission', 'modules', 'actions'));
    }

    public function update(Request $request, Permission $permission)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized action.');
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
            'module' => 'required|string|max:50',
            'action' => 'required|string|max:20',
            'description' => 'nullable|string|max:255',
        ]);
        
        $validated['slug'] = Str::slug($validated['module'] . '-' . $validated['action']);
        
        $permission->update($validated);
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Unauthorized action.');
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete permission assigned to roles.');
        }
        
        $permission->delete();
        
        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
