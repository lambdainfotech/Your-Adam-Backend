<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('size_chart_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('size_chart_id')->constrained('size_charts')->cascadeOnDelete();
            $table->string('size', 20)->comment('S, M, L, etc.');
            $table->json('measurements')->comment('{chest: 40, waist: 32, ...}');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('size_chart_id', 'idx_chart_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('size_chart_rows');
    }
};
