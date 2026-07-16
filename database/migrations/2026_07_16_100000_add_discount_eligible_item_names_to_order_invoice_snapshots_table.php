<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A frozen list of item names that were eligible for the discount on
     * THIS specific invoice — needed because `order_items.is_discount_
     * eligible` is a mutable, current-state-only flag: if an order is
     * voided and repaid with a different item selection, that flag would
     * silently repaint an OLDER (voided) invoice's item assignment when
     * rendered. Storing the eligible names directly on the snapshot keeps
     * every historical invoice's item breakdown permanently correct,
     * regardless of what happens to the order afterward.
     */
    public function up(): void
    {
        Schema::table('order_invoice_snapshots', function (Blueprint $table) {
            $table->json('discount_eligible_item_names')->nullable()->after('discount_eligibility_method');
        });
    }

    public function down(): void
    {
        Schema::table('order_invoice_snapshots', function (Blueprint $table) {
            $table->dropColumn('discount_eligible_item_names');
        });
    }
};
