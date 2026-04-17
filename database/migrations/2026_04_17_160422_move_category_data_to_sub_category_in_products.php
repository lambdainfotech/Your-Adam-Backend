<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migration: Move sub-category from category_id to sub_category_id,
     * and update category_id to the parent (main) category.
     */
    public function up(): void
    {
        $products = DB::table('products')
            ->whereNotNull('category_id')
            ->select('id', 'category_id')
            ->get();

        foreach ($products as $product) {
            // Get the current category (which is actually a sub-category)
            $subCategory = DB::table('categories')
                ->where('id', $product->category_id)
                ->first();

            if ($subCategory && $subCategory->parent_id !== null) {
                // This is a sub-category — move it to sub_category_id
                // and set category_id to the parent category
                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'sub_category_id' => $product->category_id,
                        'category_id'     => $subCategory->parent_id,
                    ]);
            }
            // If it's already a parent category (no parent_id), leave it as-is
        }
    }

    /**
     * Reverse the migration: Move sub_category_id back to category_id.
     */
    public function down(): void
    {
        $products = DB::table('products')
            ->whereNotNull('sub_category_id')
            ->select('id', 'sub_category_id')
            ->get();

        foreach ($products as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'category_id'     => $product->sub_category_id,
                    'sub_category_id' => null,
                ]);
        }
    }
};
