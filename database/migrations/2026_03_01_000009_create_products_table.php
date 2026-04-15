<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('sku_prefix', 20)->nullable();
            $table->string('barcode', 50)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit', 10)->default('kg');
            $table->tinyInteger('status')->default(1)->comment('0=draft, 1=active, 2=archived');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('has_variants')->default(false);
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->integer('total_stock')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('slug');
            $table->index('category_id');
            $table->index('status');
            $table->index('is_featured');
            $table->index('has_variants');
            $table->index('created_at');
            $table->fullText(['name', 'short_description']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
