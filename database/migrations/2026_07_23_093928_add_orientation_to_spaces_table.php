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
            // Only meaningful for the 'long_table' shape today, but kept
            // generic (not long-table-specific column name) in case another
            // non-symmetric shape ever needs orientation too.
            $table->string('orientation')->default('horizontal')->after('shape');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spaces', function (Blueprint $table) {
            $table->dropColumn('orientation');
        });
    }
};
