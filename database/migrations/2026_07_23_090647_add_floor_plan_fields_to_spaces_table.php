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
            // Fixed logical canvas coordinates (design-space units, not raw
            // viewport pixels) — nullable until an admin actually arranges
            // the floor plan; null renders via a client-side fallback grid.
            $table->decimal('position_x', 8, 2)->nullable()->after('sort_order');
            $table->decimal('position_y', 8, 2)->nullable()->after('position_x');
            $table->string('shape')->default('rectangle')->after('position_y');
            // When `status` actually changed value (not just re-saved) —
            // powers the floor plan's "occupied since" timer.
            $table->timestamp('status_changed_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'shape', 'status_changed_at']);
        });
    }
};
