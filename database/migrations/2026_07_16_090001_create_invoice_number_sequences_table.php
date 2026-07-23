<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A single global counter (`sequence_key` is always the literal
     * 'default') rather than the date-keyed pattern order numbers use — a
     * real BIR OR/Invoice number is one continuous run matching the
     * pre-approved serial range printed on the ATP, not reset daily.
     * Incremented the same concurrency-safe way as order_number_sequences:
     * insertOrIgnore + lockForUpdate() inside the caller's transaction.
     */
    public function up(): void
    {
        Schema::create('invoice_number_sequences', function (Blueprint $table) {
            $table->string('sequence_key')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_number_sequences');
    }
};
