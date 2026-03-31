<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'action' => 'view'],
            
            // Products
            ['name' => 'View Products', 'slug' => 'products.view', 'module' => 'products', 'action' => 'view'],
            ['name' => 'Create Products', 'slug' => 'products.create', 'module' => 'products', 'action' => 'create'],
            ['name' => 'Edit Products', 'slug' => 'products.edit', 'module' => 'products', 'action' => 'edit'],
            ['name' => 'Delete Products', 'slug' => 'products.delete', 'module' => 'products', 'action' => 'delete'],
            
            // Categories
            ['name' => 'View Categories', 'slug' => 'categories.view', 'module' => 'categories', 'action' => 'view'],
            ['name' => 'Create Categories', 'slug' => 'categories.create', 'module' => 'categories', 'action' => 'create'],
            ['name' => 'Edit Categories', 'slug' => 'categories.edit', 'module' => 'categories', 'action' => 'edit'],
            ['name' => 'Delete Categories', 'slug' => 'categories.delete', 'module' => 'categories', 'action' => 'delete'],
            
            // Attributes
            ['name' => 'View Attributes', 'slug' => 'attributes.view', 'module' => 'attributes', 'action' => 'view'],
            ['name' => 'Manage Attributes', 'slug' => 'attributes.manage', 'module' => 'attributes', 'action' => 'manage'],
            
            // Orders
            ['name' => 'View Orders', 'slug' => 'orders.view', 'module' => 'orders', 'action' => 'view'],
            ['name' => 'Manage Orders', 'slug' => 'orders.manage', 'module' => 'orders', 'action' => 'manage'],
            ['name' => 'Update Order Status', 'slug' => 'orders.status', 'module' => 'orders', 'action' => 'edit'],
            
            // Coupons
            ['name' => 'View Coupons', 'slug' => 'coupons.view', 'module' => 'coupons', 'action' => 'view'],
            ['name' => 'Manage Coupons', 'slug' => 'coupons.manage', 'module' => 'coupons', 'action' => 'manage'],
            
            // Campaigns
            ['name' => 'View Campaigns', 'slug' => 'campaigns.view', 'module' => 'campaigns', 'action' => 'view'],
            ['name' => 'Manage Campaigns', 'slug' => 'campaigns.manage', 'module' => 'campaigns', 'action' => 'manage'],
            
            // Inventory
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory', 'action' => 'view'],
            ['name' => 'Manage Inventory', 'slug' => 'inventory.manage', 'module' => 'inventory', 'action' => 'manage'],
            
            // Couriers
            ['name' => 'View Couriers', 'slug' => 'couriers.view', 'module' => 'couriers', 'action' => 'view'],
            ['name' => 'Manage Couriers', 'slug' => 'couriers.manage', 'module' => 'couriers', 'action' => 'manage'],
            
            // Users
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'module' => 'users', 'action' => 'manage'],
            
            // Roles & Permissions
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'module' => 'roles', 'action' => 'manage'],
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'module' => 'permissions', 'action' => 'view'],
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'module' => 'permissions', 'action' => 'manage'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports', 'action' => 'view'],
            
            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings', 'action' => 'view'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'module' => 'settings', 'action' => 'manage'],
            
            // Activity Logs
            ['name' => 'View Activity Logs', 'slug' => 'activity-logs.view', 'module' => 'activity-logs', 'action' => 'view'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        // Create admin role if not exists
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full system access',
                'level' => 100,
                'is_system' => true,
            ]
        );

        // Assign all permissions to admin
        $allPermissions = Permission::pluck('id')->toArray();
        $adminRole->permissions()->sync($allPermissions);

        // Create manager role
        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage most features except settings',
                'level' => 80,
                'is_system' => true,
            ]
        );

        // Assign limited permissions to manager
        $managerPermissions = Permission::whereNotIn('module', ['settings', 'permissions'])->pluck('id')->toArray();
        $managerRole->permissions()->sync($managerPermissions);

        // Create staff role
        $staffRole = Role::firstOrCreate(
            ['slug' => 'staff'],
            [
                'name' => 'Staff',
                'description' => 'Can view and manage orders, products, and inventory',
                'level' => 50,
                'is_system' => true,
            ]
        );

        // Assign view permissions to staff
        $staffPermissions = Permission::whereIn('action', ['view', 'edit'])
            ->whereIn('module', ['products', 'orders', 'inventory', 'dashboard'])
            ->pluck('id')
            ->toArray();
        $staffRole->permissions()->sync($staffPermissions);
    }
}
