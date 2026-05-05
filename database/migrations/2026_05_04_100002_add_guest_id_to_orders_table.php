<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop existing user_id foreign key before modifying column
            $table->dropForeign(['user_id']);

            // Make user_id nullable so guest orders don't require a user record
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Add guest_id for guest orders
            $table->foreignId('guest_id')
                ->nullable()
                ->after('user_id')
                ->constrained('guests')
                ->onDelete('set null');

            $table->index('guest_id', 'idx_guest_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['guest_id']);
            $table->dropIndex('idx_guest_id');
            $table->dropColumn('guest_id');

            // Revert user_id to non-nullable
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
