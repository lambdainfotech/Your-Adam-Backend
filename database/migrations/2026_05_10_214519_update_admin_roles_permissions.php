<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migration.
     */
    public function up(): void
    {
        // Create super-admin role if not exists (full access)
        $superAdminRole = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full system access',
                'level' => 100,
                'is_system' => true,
            ]
        );

        // Create admin role if not exists (full access)
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full system access',
                'level' => 90,
                'is_system' => true,
            ]
        );

        // Create manager role if not exists
        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage most features except settings and permissions',
                'level' => 80,
                'is_system' => true,
            ]
        );

        // Create staff role if not exists
        $staffRole = Role::firstOrCreate(
            ['slug' => 'staff'],
            [
                'name' => 'Staff',
                'description' => 'Can view and manage orders, products, and inventory',
                'level' => 50,
                'is_system' => true,
            ]
        );

        // Assign ALL permissions to super-admin and admin
        $allPermissions = Permission::pluck('id')->toArray();
        $superAdminRole->permissions()->sync($allPermissions);
        $adminRole->permissions()->sync($allPermissions);

        // Assign permissions to manager (exclude settings, permissions, roles)
        $managerPermissions = Permission::whereNotIn('module', ['settings', 'permissions', 'roles'])
            ->pluck('id')
            ->toArray();
        $managerRole->permissions()->sync($managerPermissions);

        // Assign view + manage permissions to staff for core modules
        $staffPermissions = Permission::whereIn('action', ['view', 'manage', 'edit', 'status'])
            ->whereIn('module', [
                'products', 'categories', 'attributes', 'variants',
                'orders', 'inventory', 'stock-in', 'couriers',
                'dashboard', 'pos', 'coupons', 'campaigns',
                'guests', 'users', 'reports'
            ])
            ->pluck('id')
            ->toArray();
        $staffRole->permissions()->sync($staffPermissions);
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        // No reverse needed - roles and permissions stay
    }
};
