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
        Schema::table('spaces', function (Blueprint $table) {
            // Canvas units (same 1600x1000 logical space as position_x/position_y,
            // not raw device pixels) — null means "use the shape's default size".
            $table->unsignedSmallInteger('width')->nullable()->after('shape');
            $table->unsignedSmallInteger('height')->nullable()->after('width');
            $table->unsignedSmallInteger('rotation')->default(0)->after('height');
            // Superseded by the general `rotation` field above.
            $table->dropColumn('orientation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['width', 'height', 'rotation']);
            $table->string('orientation')->default('horizontal')->after('shape');
        });
    }
};
