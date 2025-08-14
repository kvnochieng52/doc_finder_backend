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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('facility_name')->nullable();
            $table->text('facility_profile')->nullable();
            $table->text('facility_cover_image')->nullable();
            $table->text('facility_logo')->nullable();
            $table->string('facility_phone')->nullable();
            $table->string('facility_email')->nullable();
            $table->text('facility_location')->nullable();
            $table->integer('is_active')->nullable();
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
        Schema::dropIfExists('facilities');
    }
};
