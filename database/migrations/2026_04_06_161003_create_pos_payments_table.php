<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained()->onDelete('cascade');
            $table->enum('payment_method', ['cash', 'card', 'bkash', 'nagad', 'other']);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number', 100)->nullable(); // Card last 4, bKash trx ID, etc.
            $table->decimal('received_amount', 12, 2)->nullable(); // For cash (amount given by customer)
            $table->decimal('change_amount', 12, 2)->nullable(); // For cash (change returned)
            $table->timestamps();

            $table->index(['pos_order_id', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_payments');
    }
};
