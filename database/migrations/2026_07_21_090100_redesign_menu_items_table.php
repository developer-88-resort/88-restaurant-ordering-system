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
        Schema::table('menu_items', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('sku')->nullable()->unique()->after('price');
            $table->unsignedSmallInteger('prep_time_minutes')->nullable()->after('sku');
            $table->boolean('is_featured')->default(false)->after('prep_time_minutes');
            $table->boolean('is_best_seller')->default(false)->after('is_featured');
            $table->unsignedInteger('sort_order')->default(0)->after('is_best_seller');
            $table->string('availability_status')->default('available')->after('sort_order');
            $table->softDeletes();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn(['is_available', 'image_path']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->boolean('is_available')->default(true);
            $table->string('image_path')->nullable();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'description',
                'sku',
                'prep_time_minutes',
                'is_featured',
                'is_best_seller',
                'sort_order',
                'availability_status',
            ]);
        });
    }
};
