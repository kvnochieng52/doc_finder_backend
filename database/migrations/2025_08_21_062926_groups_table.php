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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->text('group_description');
            $table->string('group_location');
            $table->string('group_tags')->nullable();
            $table->enum('group_privacy', ['public', 'private', 'closed'])->default('public');
            $table->boolean('require_approval')->default(false);
            $table->text('group_image')->nullable();
            $table->text('cover_image')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
