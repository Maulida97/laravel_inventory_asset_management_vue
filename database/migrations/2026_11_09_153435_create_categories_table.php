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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique('idx_categories_code');
            $table->string('name', 100);
            $table->enum('type', ['inventory', 'asset', 'both'])->default('both');
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // Composite index untuk mempercepat filter kombinasi tipe dan status aktif
            $table->index(['type', 'is_active'], 'idx_categories_type_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
