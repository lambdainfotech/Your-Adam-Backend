<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Seed the application's database with dummy data.
     */
    public function run(): void
    {
        $this->seedRolesAndPermissions();
        $admin = $this->seedAdminUser();
        $categories = $this->seedCategories();
        $this->seedProducts($categories, $admin);
    }

    private function seedRolesAndPermissions(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'level' => 100, 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'level' => 90, 'is_system' => true],
            ['name' => 'Customer', 'slug' => 'customer', 'level' => 10, 'is_system' => true],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $modules = ['user', 'product', 'category', 'order', 'setting'];
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

        $superAdmin = Role::where('slug', 'super-admin')->first();
        if ($superAdmin) {
            $superAdmin->permissions()->sync(Permission::all()->pluck('id'));
        }
    }

    private function seedAdminUser(): User
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'mobile' => '+8801700000000',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole?->id,
                'status' => true,
                'email_verified_at' => now(),
                'mobile_verified_at' => now(),
            ]
        );

        Profile::firstOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => 'Super Administrator',
                'email' => 'admin@example.com',
            ]
        );

        return $user;
    }

    /**
     * @return array<int, Category>
     */
    private function seedCategories(): array
    {
        $categoriesData = [
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
                'name' => 'Electronics',
                'slug' => 'electronics',
                'children' => [
                    ['name' => 'Mobile Phones', 'slug' => 'mobile-phones'],
                    ['name' => 'Laptops', 'slug' => 'laptops'],
                    ['name' => 'Accessories', 'slug' => 'electronics-accessories'],
                ],
            ],
            [
                'name' => 'Home & Living',
                'slug' => 'home-living',
                'children' => [
                    ['name' => 'Furniture', 'slug' => 'furniture'],
                    ['name' => 'Decor', 'slug' => 'decor'],
                    ['name' => 'Kitchen', 'slug' => 'kitchen'],
                ],
            ],
        ];

        $allCategories = [];

        foreach ($categoriesData as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = Category::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData + ['is_active' => true, 'sort_order' => 0]
            );

            $allCategories[] = $parent;

            foreach ($children as $child) {
                $childCategory = Category::firstOrCreate(
                    ['slug' => $child['slug']],
                    $child + ['parent_id' => $parent->id, 'is_active' => true, 'sort_order' => 0]
                );
                $allCategories[] = $childCategory;
            }
        }

        return $allCategories;
    }

    /**
     * @param array<int, Category> $categories
     */
    private function seedProducts(array $categories, User $admin): void
    {
        $leafCategories = array_values(array_filter($categories, fn ($c) => $c->parent_id !== null));

        $products = [
            [
                'name' => 'Classic Cotton T-Shirt',
                'slug' => 'classic-cotton-t-shirt',
                'base_price' => 450.00,
                'stock_quantity' => 120,
                'category_slug' => 'mens-t-shirts',
            ],
            [
                'name' => 'Slim Fit Denim Jeans',
                'slug' => 'slim-fit-denim-jeans',
                'base_price' => 1200.00,
                'stock_quantity' => 80,
                'category_slug' => 'mens-pants',
            ],
            [
                'name' => 'Running Sneakers',
                'slug' => 'running-sneakers',
                'base_price' => 2500.00,
                'stock_quantity' => 45,
                'category_slug' => 'mens-shoes',
            ],
            [
                'name' => 'Floral Summer Dress',
                'slug' => 'floral-summer-dress',
                'base_price' => 1800.00,
                'stock_quantity' => 60,
                'category_slug' => 'womens-dresses',
            ],
            [
                'name' => 'Casual Blouse Top',
                'slug' => 'casual-blouse-top',
                'base_price' => 650.00,
                'stock_quantity' => 95,
                'category_slug' => 'womens-tops',
            ],
            [
                'name' => 'High Heel Pumps',
                'slug' => 'high-heel-pumps',
                'base_price' => 2200.00,
                'stock_quantity' => 35,
                'category_slug' => 'womens-shoes',
            ],
            [
                'name' => 'Smartphone X Pro',
                'slug' => 'smartphone-x-pro',
                'base_price' => 45000.00,
                'stock_quantity' => 25,
                'category_slug' => 'mobile-phones',
            ],
            [
                'name' => 'Wireless Earbuds',
                'slug' => 'wireless-earbuds',
                'base_price' => 3200.00,
                'stock_quantity' => 150,
                'category_slug' => 'electronics-accessories',
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'slug' => 'ergonomic-office-chair',
                'base_price' => 8500.00,
                'stock_quantity' => 20,
                'category_slug' => 'furniture',
            ],
            [
                'name' => 'Ceramic Dinner Set',
                'slug' => 'ceramic-dinner-set',
                'base_price' => 2800.00,
                'stock_quantity' => 40,
                'category_slug' => 'kitchen',
            ],
        ];

        foreach ($products as $index => $productData) {
            $categorySlug = $productData['category_slug'];
            unset($productData['category_slug']);

            $category = collect($leafCategories)->firstWhere('slug', $categorySlug);

            if (! $category) {
                continue;
            }

            Product::firstOrCreate(
                ['slug' => $productData['slug']],
                [
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'short_description' => 'High quality ' . strtolower($productData['name']) . ' at the best price.',
                    'description' => '<p>This is a detailed description for ' . $productData['name'] . '.</p>',
                    'base_price' => $productData['base_price'],
                    'compare_price' => round($productData['base_price'] * 1.2, 2),
                    'cost_price' => round($productData['base_price'] * 0.6, 2),
                    'status' => 1,
                    'is_active' => true,
                    'is_featured' => $index < 3,
                    'has_variants' => false,
                    'product_type' => 'simple',
                    'sku_prefix' => strtoupper(substr(str_replace(' ', '', $productData['name']), 0, 3)),
                    'barcode' => 'BAR' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'weight' => 0.50,
                    'weight_unit' => 'kg',
                    'tax_rate' => 5.00,
                    'manage_stock' => true,
                    'stock_quantity' => $productData['stock_quantity'],
                    'stock_status' => $productData['stock_quantity'] > 0 ? 'in_stock' : 'out_of_stock',
                    'low_stock_threshold' => 10,
                    'total_stock' => $productData['stock_quantity'],
                    'seo_title' => $productData['name'] . ' | Buy Online',
                    'seo_description' => 'Shop ' . $productData['name'] . ' online. Best price guaranteed.',
                    'created_by' => $admin->id,
                    'updated_by' => $admin->id,
                ]
            );
        }
    }
}
