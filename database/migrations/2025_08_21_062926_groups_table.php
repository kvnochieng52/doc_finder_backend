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
        Schema::table('groups', function (Blueprint $table) {
            // $table->id();
            $table->string('group_name')->after('id');
            $table->text('group_description')->after('group_name');
            $table->string('group_location')->after('group_description');
            $table->string('group_tags')->nullable()->after('group_location');
            $table->enum('group_privacy', ['public', 'private', 'closed'])->default('public')->after('group_tags');
            $table->boolean('require_approval')->default(false)->after('group_privacy');
            $table->text('group_image')->nullable()->after('require_approval');
            $table->text('cover_image')->nullable()->after('group_image');
            $table->bigInteger('created_by')->nullable()->after('cover_image');
            $table->bigInteger('updated_by')->nullable()->after('created_by');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn([
                'group_name',
                'group_description',
                'group_location',
                'group_tags',
                'group_privacy',
                'require_approval',
                'group_image',
                'cover_image',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
