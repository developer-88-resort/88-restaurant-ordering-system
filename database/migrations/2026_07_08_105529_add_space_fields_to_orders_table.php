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
        // `orders` is currently empty, so table_id can be freely dropped and
        // re-added as nullable (no data to preserve) since new orders will
        // reference a space instead of the old table_id going forward.
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('table_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_id')->nullable()->after('id')->constrained('tables')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->after('table_id')->constrained()->nullOnDelete();
            $table->foreignId('space_category_id')->nullable()->after('area_id')->constrained('space_categories')->nullOnDelete();
            $table->foreignId('space_id')->nullable()->after('space_category_id')->constrained('spaces')->nullOnDelete();
            $table->foreignId('space_session_id')->nullable()->after('space_id')->constrained('space_sessions')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('area_id');
            $table->dropConstrainedForeignId('space_category_id');
            $table->dropConstrainedForeignId('space_id');
            $table->dropConstrainedForeignId('space_session_id');
            $table->dropConstrainedForeignId('table_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('table_id')->after('id')->constrained('tables')->restrictOnDelete();
        });
    }
};
