<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Lets a customer's order-status page and its live-update broadcast
     * channel be keyed off an unguessable token instead of the raw
     * incrementing order id, so a stranger can't page through
     * /order/status/1, /order/status/2, ... and watch other guests'
     * rooms/tables and order status. Mirrors Space::qr_token.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('public_token')->nullable()->after('order_number');
        });

        DB::table('orders')->orderBy('id')->select('id')->each(function (object $order) {
            DB::table('orders')->where('id', $order->id)->update([
                'public_token' => Str::random(32),
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('public_token')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn('public_token');
        });
    }
};
