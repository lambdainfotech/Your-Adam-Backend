<?php

namespace Database\Seeders;

use App\Modules\Auth\src\Models\Permission;
use App\Modules\Auth\src\Models\Role;
use App\Modules\Auth\src\Models\User;
use App\Modules\Auth\src\Enums\UserStatus;
use App\Modules\User\src\Models\Profile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            CategorySeeder::class,
        ]);
    }
}

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'level' => 100, 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'level' => 90, 'is_system' => true],
            ['name' => 'Accounts', 'slug' => 'accounts', 'level' => 80, 'is_system' => true],
            ['name' => 'Customer', 'slug' => 'customer', 'level' => 10, 'is_system' => true],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        // Create permissions
        $modules = ['user', 'product', 'category', 'order', 'coupon', 'campaign', 'report', 'setting'];
        $actions = ['view', 'create', 'update', 'delete', 'manage'];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(
                    ['slug' => "{$module}.{$action}"],
                    [
                        'name' => ucfirst($action) . ' ' . ucfirst($module),
                        'module' => $module,
                        'action' => $action,
                    ]
                );
            }
        }

        // Assign all permissions to super admin
        $superAdmin = Role::where('slug', 'super-admin')->first();
        $superAdmin->permissions()->sync(Permission::all()->pluck('id'));

        // Assign view permissions to customer
        $customer = Role::where('slug', 'customer')->first();
        $viewPermissions = Permission::where('action', 'view')->pluck('id');
        $customer->permissions()->sync($viewPermissions);
    }
}

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        $user = User::firstOrCreate(
            ['mobile' => '+8801700000000'],
            [
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'status' => UserStatus::ACTIVE,
                'mobile_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Super Administrator',
                'email' => 'admin@example.com',
            ]
        );
    }
}

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => "Men's Fashion",
                'slug' => 'mens-fashion',
                'children' => [
                    ['name' => 'T-Shirts', 'slug' => 'mens-t-shirts'],
                    ['name' => 'Shirts', 'slug' => 'mens-shirts'],
                    ['name' => 'Pants', 'slug' => 'mens-pants'],
                    ['name' => 'Shoes', 'slug' => 'mens-shoes'],
                ],
            ],
            [
                'name' => "Women's Fashion",
                'slug' => 'womens-fashion',
                'children' => [
                    ['name' => 'Dresses', 'slug' => 'womens-dresses'],
                    ['name' => 'Tops', 'slug' => 'womens-tops'],
                    ['name' => 'Pants', 'slug' => 'womens-pants'],
                    ['name' => 'Shoes', 'slug' => 'womens-shoes'],
                ],
            ],
            [
                'name' => 'Kids Fashion',
                'slug' => 'kids-fashion',
                'children' => [
                    ['name' => 'Boys', 'slug' => 'boys-fashion'],
                    ['name' => 'Girls', 'slug' => 'girls-fashion'],
                ],
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'children' => [
                    ['name' => 'Watches', 'slug' => 'watches'],
                    ['name' => 'Bags', 'slug' => 'bags'],
                    ['name' => 'Jewelry', 'slug' => 'jewelry'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = \App\Modules\Catalog\src\Models\Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData + ['is_active' => true]
            );

            foreach ($children as $child) {
                \App\Modules\Catalog\src\Models\Category::firstOrCreate(
                    ['slug' => $child['slug']],
                    $child + ['parent_id' => $parent->id, 'is_active' => true]
                );
            }
        }
    }
}
