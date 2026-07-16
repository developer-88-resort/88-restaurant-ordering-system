<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The alphanumeric prefix used on generated invoice numbers, e.g.
     * "88HSR" → "88HSR-000001". Configurable rather than hardcoded so it
     * isn't baked into InvoiceNumberGenerator — changing it only affects
     * invoices issued afterward; already-issued ones keep their original
     * number (frozen on their own OrderInvoiceSnapshot row).
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('invoice_number_prefix')->default('88HSR')->after('invoice_serial_to');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('invoice_number_prefix');
        });
    }
};
