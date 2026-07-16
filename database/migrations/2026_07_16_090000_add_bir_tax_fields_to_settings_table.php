<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * All nullable/defaulted — real BIR paperwork (TIN, permit numbers,
     * serial ranges) may not be finalized yet, so the settings page must
     * keep working with any subset of these left blank.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('bir_registered_name')->nullable()->after('resort_name');
            $table->string('website')->nullable()->after('email');
            $table->string('tin')->nullable()->after('website');
            $table->string('branch_code')->nullable()->after('tin');
            $table->string('tax_registration_type')->default('non_vat')->after('branch_code');
            $table->decimal('tax_rate', 5, 2)->default(12.00)->after('tax_registration_type');
            $table->boolean('prices_include_vat')->default(true)->after('tax_rate');
            $table->string('invoice_title')->nullable()->after('prices_include_vat');
            $table->string('bir_permit_number')->nullable()->after('invoice_title');
            $table->string('atp_ocn_number')->nullable()->after('bir_permit_number');
            $table->date('atp_ocn_date_issued')->nullable()->after('atp_ocn_number');
            $table->string('invoice_serial_from')->nullable()->after('atp_ocn_date_issued');
            $table->string('invoice_serial_to')->nullable()->after('invoice_serial_from');
            $table->text('invoice_footer_message')->nullable()->after('invoice_serial_to');
            $table->boolean('service_charge_enabled')->default(false)->after('invoice_footer_message');
            $table->decimal('service_charge_percent', 5, 2)->nullable()->after('service_charge_enabled');
            $table->boolean('service_charge_taxable')->default(false)->after('service_charge_percent');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'bir_registered_name',
                'website',
                'tin',
                'branch_code',
                'tax_registration_type',
                'tax_rate',
                'prices_include_vat',
                'invoice_title',
                'bir_permit_number',
                'atp_ocn_number',
                'atp_ocn_date_issued',
                'invoice_serial_from',
                'invoice_serial_to',
                'invoice_footer_message',
                'service_charge_enabled',
                'service_charge_percent',
                'service_charge_taxable',
            ]);
        });
    }
};
