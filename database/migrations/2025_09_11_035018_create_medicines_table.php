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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('medicine_number')->unique();
            $table->decimal('cost', 10, 2);
            $table->string('image')->nullable();
            $table->foreignId('category_id')->constrained('medicine_categories')->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained('medicine_subcategories')->onDelete('set null');
            $table->json('conditions')->nullable(); // for searchable conditions
            $table->string('manufacturer')->nullable();
            $table->string('strength')->nullable(); // e.g., "500mg", "10ml"
            $table->string('form')->nullable(); // e.g., "tablet", "syrup", "injection"
            $table->integer('quantity_available')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_prescription')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
