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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('verification_code')->nullable();
            $table->integer('is_active')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();

            $table->string('telephone')->nullable();
            $table->string('id_number')->nullable();
            $table->integer('account_type')->nullable(); // 1 for user, 2 for Sp/dcotor
            $table->string('address')->nullable();


            $table->text('licence_number')->nullable();
            $table->text('professional_bio')->nullable();
            $table->integer('sp_approved')->default(0)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
