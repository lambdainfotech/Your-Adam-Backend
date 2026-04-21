<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Rent', 'icon' => 'fa-building', 'color' => '#EF4444', 'sort_order' => 1],
            ['name' => 'Salary', 'icon' => 'fa-users', 'color' => '#3B82F6', 'sort_order' => 2],
            ['name' => 'Marketing', 'icon' => 'fa-bullhorn', 'color' => '#F59E0B', 'sort_order' => 3],
            ['name' => 'Utilities', 'icon' => 'fa-bolt', 'color' => '#FCD34D', 'sort_order' => 4],
            ['name' => 'Courier/Shipping', 'icon' => 'fa-truck', 'color' => '#10B981', 'sort_order' => 5],
            ['name' => 'Packaging', 'icon' => 'fa-box', 'color' => '#8B5CF6', 'sort_order' => 6],
            ['name' => 'Equipment', 'icon' => 'fa-tools', 'color' => '#6366F1', 'sort_order' => 7],
            ['name' => 'Maintenance', 'icon' => 'fa-wrench', 'color' => '#EC4899', 'sort_order' => 8],
            ['name' => 'Office Supplies', 'icon' => 'fa-pencil-alt', 'color' => '#14B8A6', 'sort_order' => 9],
            ['name' => 'Others', 'icon' => 'fa-ellipsis-h', 'color' => '#6B7280', 'sort_order' => 10],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
