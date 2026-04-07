<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            // Delivery status: pending, processing, ready, delivered, cancelled
            $table->string('delivery_status', 20)->default('pending')->after('status');
            // Tracking number for courier
            $table->string('tracking_number', 100)->nullable()->after('delivery_status');
            // Delivery address
            $table->text('delivery_address')->nullable()->after('tracking_number');
            // Delivery notes
            $table->text('delivery_notes')->nullable()->after('delivery_address');
            // Estimated delivery date
            $table->date('estimated_delivery_date')->nullable()->after('delivery_notes');
            // Actual delivered at
            $table->timestamp('delivered_at')->nullable()->after('estimated_delivery_date');
            // Assigned courier
            $table->foreignId('courier_id')->nullable()->after('delivered_at')->constrained('couriers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropForeign(['courier_id']);
            $table->dropColumn([
                'delivery_status',
                'tracking_number',
                'delivery_address',
                'delivery_notes',
                'estimated_delivery_date',
                'delivered_at',
                'courier_id',
            ]);
        });
    }
};
