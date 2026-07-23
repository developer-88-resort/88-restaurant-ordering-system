<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->unsignedInteger('covers_count')->nullable()->after('notes');
            $table->foreignId('current_invoice_snapshot_id')->nullable()->after('receipt_number')
                ->constrained('order_invoice_snapshots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_invoice_snapshot_id');
            $table->dropColumn(['payment_reference', 'covers_count']);
        });
    }
};
