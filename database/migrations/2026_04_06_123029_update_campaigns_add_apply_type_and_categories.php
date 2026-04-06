<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add apply_type to campaigns table
        Schema::table('campaigns', function (Blueprint $table) {
            if (!Schema::hasColumn('campaigns', 'apply_type')) {
                $table->enum('apply_type', ['all', 'products', 'categories'])
                    ->default('all')
                    ->after('apply_to_all');
            }
        });

        // Create campaign_categories pivot table
        if (!Schema::hasTable('campaign_categories')) {
            Schema::create('campaign_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->timestamps();

                $table->unique(['campaign_id', 'category_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            if (Schema::hasColumn('campaigns', 'apply_type')) {
                $table->dropColumn('apply_type');
            }
        });

        Schema::dropIfExists('campaign_categories');
    }
};
