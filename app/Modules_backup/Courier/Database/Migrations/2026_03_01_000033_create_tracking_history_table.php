<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tracking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('status', 50)->notNull();
            $table->string('location', 255)->nullable();
            $table->text('description')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('tracked_at')->notNull();
            $table->timestamps();

            $table->index(['order_id'], 'idx_order_id');
            $table->index(['tracked_at'], 'idx_tracked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_history');
    }
};
