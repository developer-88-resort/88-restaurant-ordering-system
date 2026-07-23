<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Generates the official, BIR-facing sequential invoice number — a single
 * continuous run (e.g. "88HSR-001"), never reset by date, matching how
 * a real pre-approved BIR serial range (from the Authority to Print)
 * works. This is deliberately NOT the same pattern as OrderNumberGenerator
 * (which resets daily) — an invoice/OR sequence must never have gaps
 * other than voided transactions, and must never restart.
 *
 * The prefix (e.g. "88HSR") is configurable via Settings rather than
 * hardcoded here — changing it only affects invoices issued afterward;
 * already-issued ones keep their original number frozen on their own
 * OrderInvoiceSnapshot row, so a later prefix change never rewrites
 * history.
 *
 * Concurrency-safe the same way OrderNumberGenerator is: insertOrIgnore
 * guarantees the single counter row exists, then lockForUpdate() inside
 * the caller's transaction serializes the increment via a real row lock.
 */
class InvoiceNumberGenerator
{
    protected const SEQUENCE_KEY = 'default';

    public static function generate(string $prefix = '88HSR'): string
    {
        DB::table('invoice_number_sequences')->insertOrIgnore([
            'sequence_key' => self::SEQUENCE_KEY,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('invoice_number_sequences')
            ->where('sequence_key', self::SEQUENCE_KEY)
            ->lockForUpdate()
            ->first();

        $next = $row->last_number + 1;

        DB::table('invoice_number_sequences')
            ->where('sequence_key', self::SEQUENCE_KEY)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        // %03d only pads UP TO 3 digits — once the count passes 999 it
        // naturally widens to "1000", "1001", etc. rather than truncating,
        // so there's no cap here, just less padding while the count is low.
        return sprintf('%s-%03d', $prefix, $next);
    }
}
