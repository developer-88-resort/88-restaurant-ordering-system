<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_invoice_snapshots', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('total_amount_due');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->decimal('amount_received', 10, 2)->nullable()->after('payment_reference');
            $table->decimal('change_amount', 10, 2)->nullable()->after('amount_received');
        });
    }

    public function down(): void
    {
        Schema::table('order_invoice_snapshots', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_reference', 'amount_received', 'change_amount']);
        });
    }
};
