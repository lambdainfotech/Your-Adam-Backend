<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained('variants')->onDelete('set null');
            $table->string('product_name', 255);
            $table->string('sku', 100);
            $table->string('variant_info', 255)->nullable(); // e.g., "Red - Large"
            $table->integer('quantity')->unsigned();
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();

            $table->index(['pos_order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
    }
};
