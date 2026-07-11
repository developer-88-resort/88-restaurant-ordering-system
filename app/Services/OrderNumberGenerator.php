<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Generates the customer/staff-facing order number: 88-MMDD-### (brand
 * prefix, Asia/Manila month+day, 3-digit daily sequence starting at 001).
 *
 * The sequence is tracked in a dedicated `order_number_sequences` table
 * (one row per calendar day) rather than derived by counting or MAX()-ing
 * existing orders, so deleted orders never free up a number for reuse and
 * cancelled orders simply keep whatever number they were given at creation.
 *
 * Safe under concurrent order creation: `insertOrIgnore` guarantees the
 * day's row exists, then `lockForUpdate()` inside the caller's transaction
 * serializes the increment via a real row lock (InnoDB) — this works
 * identically on the MariaDB connection used in production and on the
 * SQLite connection used in tests.
 */
class OrderNumberGenerator
{
    public static function generate(?Carbon $for = null): string
    {
        $date = ($for ?? now())->clone()->timezone('Asia/Manila');
        $dateKey = $date->format('Y-m-d');

        DB::table('order_number_sequences')->insertOrIgnore([
            'sequence_date' => $dateKey,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('order_number_sequences')
            ->where('sequence_date', $dateKey)
            ->lockForUpdate()
            ->first();

        $next = $row->last_number + 1;

        DB::table('order_number_sequences')
            ->where('sequence_date', $dateKey)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return sprintf('88-%s-%03d', $date->format('md'), $next);
    }
}
