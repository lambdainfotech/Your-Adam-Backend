<?php

declare(strict_types=1);

use App\Modules\Sales\Enums\OrderStatus;
use App\Modules\Sales\Enums\PaymentMethod;
use App\Modules\Sales\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status', 20)->default(OrderStatus::PENDING->value);
            $table->string('payment_status', 20)->default(PaymentStatus::PENDING->value);
            $table->string('payment_method', 20)->default(PaymentMethod::COD->value);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('coupon_discount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->json('delivery_address');
            $table->json('billing_address');
            $table->timestamp('estimated_delivery_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index('order_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
