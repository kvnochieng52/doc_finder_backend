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
        Schema::create('group_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('group_categories')->onDelete('cascade');

            // Indexes
            $table->index(['group_id']);
            $table->index(['category_id']);

            // Unique constraint to prevent duplicate mappings
            $table->unique(['group_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_category_mappings');
    }
};
