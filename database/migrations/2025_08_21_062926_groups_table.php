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
            $table->string('group_image')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign key constraint for created_by
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['created_by']);
            $table->index(['group_privacy']);
            $table->index(['created_at']);
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
