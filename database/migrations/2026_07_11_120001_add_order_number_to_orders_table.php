<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the new "88-MMDD-###" order_number column, backfills every
     * existing order (oldest-first, per calendar day in Asia/Manila) so
     * historical records keep a permanent, correctly-sequenced number
     * instead of being left blank or renumbered later, then seeds
     * order_number_sequences so future numbering picks up where the
     * backfill left off rather than restarting at 001 mid-day.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('id');
        });

        $counters = [];

        DB::table('orders')
            ->orderBy('created_at')
            ->orderBy('id')
            ->select(['id', 'created_at'])
            ->each(function (object $order) use (&$counters) {
                $date = Carbon::parse($order->created_at)->timezone('Asia/Manila');
                $dateKey = $date->format('Y-m-d');

                $counters[$dateKey] = ($counters[$dateKey] ?? 0) + 1;

                DB::table('orders')->where('id', $order->id)->update([
                    'order_number' => sprintf('88-%s-%03d', $date->format('md'), $counters[$dateKey]),
                ]);
            });

        foreach ($counters as $dateKey => $lastNumber) {
            DB::table('order_number_sequences')->updateOrInsert(
                ['sequence_date' => $dateKey],
                ['last_number' => $lastNumber, 'updated_at' => now(), 'created_at' => now()]
            );
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn('order_number');
        });
    }
};
