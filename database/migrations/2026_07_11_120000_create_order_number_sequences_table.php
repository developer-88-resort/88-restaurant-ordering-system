<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per business day (Asia/Manila). `last_number` is the last
     * sequence number issued for that day — incremented atomically inside a
     * locked transaction by OrderNumberGenerator, never derived by counting
     * orders, so deleted orders never free up a number for reuse.
     */
    public function up(): void
    {
        Schema::create('order_number_sequences', function (Blueprint $table) {
            $table->date('sequence_date')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_number_sequences');
    }
};
