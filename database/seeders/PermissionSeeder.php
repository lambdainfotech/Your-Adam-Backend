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
            ['name' => 'Create Attributes', 'slug' => 'attributes.create', 'module' => 'attributes', 'action' => 'create'],
            ['name' => 'Edit Attributes', 'slug' => 'attributes.edit', 'module' => 'attributes', 'action' => 'edit'],
            ['name' => 'Delete Attributes', 'slug' => 'attributes.delete', 'module' => 'attributes', 'action' => 'delete'],

            // Variants
            ['name' => 'View Variants', 'slug' => 'variants.view', 'module' => 'variants', 'action' => 'view'],
            ['name' => 'Manage Variants', 'slug' => 'variants.manage', 'module' => 'variants', 'action' => 'manage'],

            // Product Images
            ['name' => 'View Product Images', 'slug' => 'product-images.view', 'module' => 'product-images', 'action' => 'view'],
            ['name' => 'Manage Product Images', 'slug' => 'product-images.manage', 'module' => 'product-images', 'action' => 'manage'],

            // Orders
            ['name' => 'View Orders', 'slug' => 'orders.view', 'module' => 'orders', 'action' => 'view'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'module' => 'orders', 'action' => 'create'],
            ['name' => 'Edit Orders', 'slug' => 'orders.edit', 'module' => 'orders', 'action' => 'edit'],
            ['name' => 'Delete Orders', 'slug' => 'orders.delete', 'module' => 'orders', 'action' => 'delete'],
            ['name' => 'Update Order Status', 'slug' => 'orders.status', 'module' => 'orders', 'action' => 'status'],

            // POS
            ['name' => 'View POS', 'slug' => 'pos.view', 'module' => 'pos', 'action' => 'view'],
            ['name' => 'Manage POS', 'slug' => 'pos.manage', 'module' => 'pos', 'action' => 'manage'],

            // Coupons
            ['name' => 'View Coupons', 'slug' => 'coupons.view', 'module' => 'coupons', 'action' => 'view'],
            ['name' => 'Create Coupons', 'slug' => 'coupons.create', 'module' => 'coupons', 'action' => 'create'],
            ['name' => 'Edit Coupons', 'slug' => 'coupons.edit', 'module' => 'coupons', 'action' => 'edit'],
            ['name' => 'Delete Coupons', 'slug' => 'coupons.delete', 'module' => 'coupons', 'action' => 'delete'],

            // Campaigns
            ['name' => 'View Campaigns', 'slug' => 'campaigns.view', 'module' => 'campaigns', 'action' => 'view'],
            ['name' => 'Create Campaigns', 'slug' => 'campaigns.create', 'module' => 'campaigns', 'action' => 'create'],
            ['name' => 'Edit Campaigns', 'slug' => 'campaigns.edit', 'module' => 'campaigns', 'action' => 'edit'],
            ['name' => 'Delete Campaigns', 'slug' => 'campaigns.delete', 'module' => 'campaigns', 'action' => 'delete'],

            // Inventory / Stock In
            ['name' => 'View Inventory', 'slug' => 'inventory.view', 'module' => 'inventory', 'action' => 'view'],
            ['name' => 'Create Inventory', 'slug' => 'inventory.create', 'module' => 'inventory', 'action' => 'create'],
            ['name' => 'Edit Inventory', 'slug' => 'inventory.edit', 'module' => 'inventory', 'action' => 'edit'],
            ['name' => 'Delete Inventory', 'slug' => 'inventory.delete', 'module' => 'inventory', 'action' => 'delete'],

            // Stock In (Bulk)
            ['name' => 'View Stock In', 'slug' => 'stock-in.view', 'module' => 'stock-in', 'action' => 'view'],
            ['name' => 'Manage Stock In', 'slug' => 'stock-in.manage', 'module' => 'stock-in', 'action' => 'manage'],

            // Couriers
            ['name' => 'View Couriers', 'slug' => 'couriers.view', 'module' => 'couriers', 'action' => 'view'],
            ['name' => 'Create Couriers', 'slug' => 'couriers.create', 'module' => 'couriers', 'action' => 'create'],
            ['name' => 'Edit Couriers', 'slug' => 'couriers.edit', 'module' => 'couriers', 'action' => 'edit'],
            ['name' => 'Delete Couriers', 'slug' => 'couriers.delete', 'module' => 'couriers', 'action' => 'delete'],

            // Users (Customers)
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users', 'action' => 'view'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'users', 'action' => 'create'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users', 'action' => 'edit'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'users', 'action' => 'delete'],

            // Guests
            ['name' => 'View Guests', 'slug' => 'guests.view', 'module' => 'guests', 'action' => 'view'],
            ['name' => 'Manage Guests', 'slug' => 'guests.manage', 'module' => 'guests', 'action' => 'manage'],

            // Roles
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'roles', 'action' => 'view'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'module' => 'roles', 'action' => 'create'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'module' => 'roles', 'action' => 'edit'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'module' => 'roles', 'action' => 'delete'],

            // Permissions
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'module' => 'permissions', 'action' => 'view'],
            ['name' => 'Create Permissions', 'slug' => 'permissions.create', 'module' => 'permissions', 'action' => 'create'],
            ['name' => 'Edit Permissions', 'slug' => 'permissions.edit', 'module' => 'permissions', 'action' => 'edit'],
            ['name' => 'Delete Permissions', 'slug' => 'permissions.delete', 'module' => 'permissions', 'action' => 'delete'],

            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'module' => 'reports', 'action' => 'view'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'module' => 'reports', 'action' => 'export'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings', 'action' => 'view'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'module' => 'settings', 'action' => 'edit'],

            // Activity Logs
            ['name' => 'View Activity Logs', 'slug' => 'activity-logs.view', 'module' => 'activity-logs', 'action' => 'view'],
            ['name' => 'Delete Activity Logs', 'slug' => 'activity-logs.delete', 'module' => 'activity-logs', 'action' => 'delete'],

            // Notifications
            ['name' => 'View Notifications', 'slug' => 'notifications.view', 'module' => 'notifications', 'action' => 'view'],
            ['name' => 'Manage Notifications', 'slug' => 'notifications.manage', 'module' => 'notifications', 'action' => 'manage'],

            // Expenses
            ['name' => 'View Expenses', 'slug' => 'expenses.view', 'module' => 'expenses', 'action' => 'view'],
            ['name' => 'Create Expenses', 'slug' => 'expenses.create', 'module' => 'expenses', 'action' => 'create'],
            ['name' => 'Edit Expenses', 'slug' => 'expenses.edit', 'module' => 'expenses', 'action' => 'edit'],
            ['name' => 'Delete Expenses', 'slug' => 'expenses.delete', 'module' => 'expenses', 'action' => 'delete'],

            // Sliders / Banners
            ['name' => 'View Sliders', 'slug' => 'sliders.view', 'module' => 'sliders', 'action' => 'view'],
            ['name' => 'Create Sliders', 'slug' => 'sliders.create', 'module' => 'sliders', 'action' => 'create'],
            ['name' => 'Edit Sliders', 'slug' => 'sliders.edit', 'module' => 'sliders', 'action' => 'edit'],
            ['name' => 'Delete Sliders', 'slug' => 'sliders.delete', 'module' => 'sliders', 'action' => 'delete'],

            // Testimonials
            ['name' => 'View Testimonials', 'slug' => 'testimonials.view', 'module' => 'testimonials', 'action' => 'view'],
            ['name' => 'Create Testimonials', 'slug' => 'testimonials.create', 'module' => 'testimonials', 'action' => 'create'],
            ['name' => 'Edit Testimonials', 'slug' => 'testimonials.edit', 'module' => 'testimonials', 'action' => 'edit'],
            ['name' => 'Delete Testimonials', 'slug' => 'testimonials.delete', 'module' => 'testimonials', 'action' => 'delete'],

            // Brand Values
            ['name' => 'View Brand Values', 'slug' => 'brand-values.view', 'module' => 'brand-values', 'action' => 'view'],
            ['name' => 'Create Brand Values', 'slug' => 'brand-values.create', 'module' => 'brand-values', 'action' => 'create'],
            ['name' => 'Edit Brand Values', 'slug' => 'brand-values.edit', 'module' => 'brand-values', 'action' => 'edit'],
            ['name' => 'Delete Brand Values', 'slug' => 'brand-values.delete', 'module' => 'brand-values', 'action' => 'delete'],

            // Size Charts
            ['name' => 'View Size Charts', 'slug' => 'size-charts.view', 'module' => 'size-charts', 'action' => 'view'],
            ['name' => 'Create Size Charts', 'slug' => 'size-charts.create', 'module' => 'size-charts', 'action' => 'create'],
            ['name' => 'Edit Size Charts', 'slug' => 'size-charts.edit', 'module' => 'size-charts', 'action' => 'edit'],
            ['name' => 'Delete Size Charts', 'slug' => 'size-charts.delete', 'module' => 'size-charts', 'action' => 'delete'],

            // Predefined Descriptions
            ['name' => 'View Predefined Descriptions', 'slug' => 'predefined-descriptions.view', 'module' => 'predefined-descriptions', 'action' => 'view'],
            ['name' => 'Create Predefined Descriptions', 'slug' => 'predefined-descriptions.create', 'module' => 'predefined-descriptions', 'action' => 'create'],
            ['name' => 'Edit Predefined Descriptions', 'slug' => 'predefined-descriptions.edit', 'module' => 'predefined-descriptions', 'action' => 'edit'],
            ['name' => 'Delete Predefined Descriptions', 'slug' => 'predefined-descriptions.delete', 'module' => 'predefined-descriptions', 'action' => 'delete'],

            // Bulk Operations
            ['name' => 'View Bulk Operations', 'slug' => 'bulk-operations.view', 'module' => 'bulk-operations', 'action' => 'view'],
            ['name' => 'Manage Bulk Operations', 'slug' => 'bulk-operations.manage', 'module' => 'bulk-operations', 'action' => 'manage'],
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

        // Assign ALL permissions to admin
        $allPermissions = Permission::pluck('id')->toArray();
        $adminRole->permissions()->sync($allPermissions);

        // Create manager role
        $managerRole = Role::firstOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Can manage most features except settings and permissions',
                'level' => 80,
                'is_system' => true,
            ]
        );

        // Assign permissions to manager (exclude settings, permissions, roles)
        $managerPermissions = Permission::whereNotIn('module', ['settings', 'permissions', 'roles'])
            ->pluck('id')
            ->toArray();
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
}
