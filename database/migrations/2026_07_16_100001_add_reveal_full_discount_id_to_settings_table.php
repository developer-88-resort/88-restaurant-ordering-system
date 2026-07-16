<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether the full, unmasked Senior/PWD ID number is printed on the
     * downloadable staff PDF (BIR generally requires the real ID on the
     * official copy for substantiation, so this defaults on) — the
     * on-screen views always mask it regardless of this setting.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('reveal_full_discount_id_on_pdf')->default(true)->after('service_charge_taxable');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('reveal_full_discount_id_on_pdf');
        });
    }
};
