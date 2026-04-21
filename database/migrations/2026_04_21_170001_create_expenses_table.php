<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('payment_method', 50)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->string('receipt_image', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
