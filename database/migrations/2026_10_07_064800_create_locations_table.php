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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->index('idx_locations_parent_id');
            $table->string('code', 20)->unique('idx_locations_code');
            $table->string('name', 100);
            $table->enum('type', ['building', 'warehouse', 'room', 'area'])->default('room');
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true)->index('idx_locations_is_active');
            $table->timestamps();

            $table->foreign('parent_id', 'fk_locations_parent_id')
                ->references('id')
                ->on('locations')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropForeign('fk_locations_parent_id');
        });

        Schema::dropIfExists('locations');
    }
};
