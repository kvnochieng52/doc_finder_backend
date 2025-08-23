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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('pharmacy_name');
            $table->text('pharmacy_description');
            $table->string('pharmacy_location');
            $table->string('pharmacy_tags')->nullable();
            $table->enum('pharmacy_privacy', ['public', 'private', 'closed'])->default('public');
            $table->boolean('require_approval')->default(false);
            $table->text('pharmacy_image')->nullable();
            $table->text('cover_image')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
