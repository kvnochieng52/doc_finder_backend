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
        Schema::create('group_subcategory_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('subcategory_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('group_sub_categories')->onDelete('cascade');

            // Indexes
            $table->index(['group_id']);
            $table->index(['subcategory_id']);

            // Unique constraint to prevent duplicate mappings
            $table->unique(['group_id', 'subcategory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_subcategory_mappings');
    }
};
