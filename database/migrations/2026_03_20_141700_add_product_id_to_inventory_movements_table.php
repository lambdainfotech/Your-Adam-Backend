<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            // Add product_id column if it doesn't exist
            if (!Schema::hasColumn('inventory_movements', 'product_id')) {
                $table->foreignId('product_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('products')
                    ->nullOnDelete();
            }
            
            // Make variant_id nullable since simple products don't have variants
            if (Schema::hasColumn('inventory_movements', 'variant_id')) {
                // We need to drop and recreate the foreign key to make it nullable
                // But first check if foreign key exists
                try {
                    $table->dropForeign(['variant_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, ignore
                }
                
                // Change to nullable
                $table->foreignId('variant_id')
                    ->nullable()
                    ->change();
                
                // Recreate foreign key
                $table->foreign('variant_id')
                    ->references('id')
                    ->on('variants')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_movements', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
        });
    }
};
