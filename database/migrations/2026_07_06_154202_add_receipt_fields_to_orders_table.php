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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receipt_number')->nullable()->unique()->after('payment_method');
            $table->decimal('amount_received', 10, 2)->nullable()->after('receipt_number');
            $table->decimal('change_amount', 10, 2)->nullable()->after('amount_received');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['receipt_number', 'amount_received', 'change_amount']);
        });
    }
};
