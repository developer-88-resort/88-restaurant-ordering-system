<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A frozen, immutable copy of everything needed to render an invoice,
     * captured once at payment-finalization time — extends this codebase's
     * existing snapshot-at-creation philosophy (order_items already freeze
     * item_name/unit_price from the live MenuItem) one level up to the
     * whole invoice, so a later Settings/tax-rate/menu-price change never
     * rewrites an already-issued invoice.
     *
     * Deliberately 1:many with `orders` (order_id is NOT unique): once an
     * invoice number is issued it stays on permanent record even if voided
     * — a repay after void gets a brand new invoice number rather than
     * reusing or mutating the voided one. `status` distinguishes the
     * currently-active snapshot from historical voided ones independently
     * of Order's own mutable payment_status.
     */
    public function up(): void
    {
        Schema::create('order_invoice_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('active');
            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();

            // Business/tax settings snapshot
            $table->string('business_name');
            $table->string('trade_name')->nullable();
            $table->string('business_address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('tin')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('tax_registration_type');
            $table->decimal('tax_rate', 5, 2);
            $table->boolean('prices_include_vat');
            $table->string('invoice_title');
            $table->string('bir_permit_number')->nullable();
            $table->string('atp_ocn_number')->nullable();
            $table->date('atp_ocn_date_issued')->nullable();
            $table->string('invoice_serial_from')->nullable();
            $table->string('invoice_serial_to')->nullable();
            $table->text('footer_message')->nullable();

            // Computed sales breakdown
            $table->decimal('gross_sales', 10, 2);
            $table->decimal('vatable_sales', 10, 2)->default(0);
            $table->decimal('vat_exempt_sales', 10, 2)->default(0);
            $table->decimal('zero_rated_sales', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);
            $table->decimal('vat_exemption_amount', 10, 2)->default(0);

            // Service charge snapshot
            $table->boolean('service_charge_enabled')->default(false);
            $table->decimal('service_charge_percent', 5, 2)->nullable();
            $table->decimal('service_charge_amount', 10, 2)->default(0);
            $table->boolean('service_charge_taxable')->default(false);

            // Discount snapshot
            $table->string('discount_type')->nullable();
            $table->string('discount_qualified_name')->nullable();
            $table->string('discount_id_number')->nullable();
            $table->string('discount_eligibility_method')->nullable();
            $table->decimal('discount_eligible_amount', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_promo_percent', 5, 2)->nullable();
            $table->unsignedInteger('discount_qualified_diners')->nullable();
            $table->unsignedInteger('discount_total_diners')->nullable();
            $table->text('discount_notes')->nullable();

            // Optional buyer info (business invoice requests)
            $table->string('buyer_name')->nullable();
            $table->string('buyer_tin')->nullable();
            $table->string('buyer_address')->nullable();

            $table->decimal('rounding_adjustment', 10, 2)->default(0);
            $table->decimal('total_amount_due', 10, 2);

            $table->foreignId('computed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_invoice_snapshots');
    }
};
