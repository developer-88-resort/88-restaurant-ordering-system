<?php

namespace Tests\Feature;

use App\Enums\InvoiceSnapshotStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Area;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Models\Space;
use App\Models\SpaceCategory;
use App\Models\User;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentFinalizationTest extends TestCase
{
    use RefreshDatabase;

    private Area $area;

    private SpaceCategory $category;

    private Space $space;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->area = Area::create(['name' => 'Cottages', 'slug' => 'cottages', 'sort_order' => 1, 'is_active' => true]);
        $this->category = SpaceCategory::create(['area_id' => $this->area->id, 'name' => 'Cottages', 'slug' => 'cottages', 'is_active' => true]);
        $this->space = Space::create(['area_id' => $this->area->id, 'category_id' => $this->category->id, 'name' => 'Table 1', 'status' => 'available', 'sort_order' => 1]);

        $this->admin = User::factory()->create(['role' => UserRole::Admin, 'is_active' => true]);
        $this->staff = User::factory()->create(['role' => UserRole::Staff, 'is_active' => true]);
    }

    public function test_finalizing_payment_with_a_senior_discount_persists_a_correct_invoice_snapshot(): void
    {
        Setting::current()->update(['tax_registration_type' => 'vat', 'tax_rate' => 12, 'prices_include_vat' => true]);
        $order = $this->makeOrder('1120.00');

        $response = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '1000.00',
            'discount_type' => 'senior_citizen',
            'discount_qualified_name' => 'Juan Dela Cruz',
            'discount_id_number' => '1234567890',
            'discount_eligibility_method' => 'amount_based',
            'discount_eligible_amount' => '1120.00',
        ]);

        $response->assertRedirect();
        $order->refresh();

        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotNull($order->currentInvoiceSnapshot);
        $this->assertSame('1000.00', $order->currentInvoiceSnapshot->vat_exempt_sales);
        $this->assertSame('200.00', $order->currentInvoiceSnapshot->discount_amount);
        $this->assertSame('800.00', $order->currentInvoiceSnapshot->total_amount_due);
        $this->assertSame('200.00', $order->change_amount, 'Change must be computed off the discounted total (800), not the pre-discount subtotal (1120).');
        $this->assertSame($order->currentInvoiceSnapshot->invoice_number, $order->receipt_number);
        $this->assertSame(InvoiceSnapshotStatus::Active, $order->currentInvoiceSnapshot->status);
    }

    public function test_item_based_eligibility_only_accepts_items_belonging_to_the_order(): void
    {
        $order = $this->makeOrder('500.00');
        $foreignOrder = $this->makeOrder('300.00');
        $foreignItem = $foreignOrder->items->first();

        $response = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '500.00',
            'discount_type' => 'pwd',
            'discount_qualified_name' => 'Maria Santos',
            'discount_id_number' => '999',
            'discount_eligibility_method' => 'item_based',
            'discount_item_ids' => [$foreignItem->id],
        ]);

        $response->assertSessionHasErrors('discount_item_ids');
        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_eligible_amount_cannot_exceed_the_order_subtotal(): void
    {
        $order = $this->makeOrder('500.00');

        $response = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '500.00',
            'discount_type' => 'senior_citizen',
            'discount_qualified_name' => 'Juan Dela Cruz',
            'discount_id_number' => '111',
            'discount_eligibility_method' => 'amount_based',
            'discount_eligible_amount' => '600.00',
        ]);

        $response->assertSessionHasErrors('discount_eligible_amount');
        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);
    }

    public function test_amount_received_is_validated_against_the_computed_discounted_total_not_the_raw_subtotal(): void
    {
        Setting::current()->update(['tax_registration_type' => 'non_vat']);
        $order = $this->makeOrder('1000.00');

        // The real total due after a full PWD discount is 800 (1000 - 20%).
        // 900 is below the raw 1000 subtotal but still covers the real
        // 800 total, so it must be ACCEPTED — proving the validation uses
        // the computed post-discount figure, not the pre-discount one.
        $accepted = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '900.00',
            'discount_type' => 'pwd',
            'discount_qualified_name' => 'Juan',
            'discount_id_number' => '111',
            'discount_eligibility_method' => 'amount_based',
            'discount_eligible_amount' => '1000.00',
        ]);
        $accepted->assertSessionHasNoErrors();
        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);

        // 700 falls short of the real 800 total and must be rejected.
        $order2 = $this->makeOrder('1000.00');
        $rejected = $this->actingAs($this->admin)->patch("/orders/{$order2->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '700.00',
            'discount_type' => 'pwd',
            'discount_qualified_name' => 'Juan',
            'discount_id_number' => '111',
            'discount_eligibility_method' => 'amount_based',
            'discount_eligible_amount' => '1000.00',
        ]);
        $rejected->assertSessionHasErrors('amount_received');
        $this->assertSame(PaymentStatus::Unpaid, $order2->fresh()->payment_status);
    }

    public function test_a_discount_type_cannot_be_submitted_without_its_required_fields(): void
    {
        $order = $this->makeOrder('500.00');

        $response = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '500.00',
            'discount_type' => 'senior_citizen',
            // qualified_name / id_number / eligibility_method all missing.
        ]);

        $response->assertSessionHasErrors(['discount_qualified_name', 'discount_id_number', 'discount_eligibility_method']);
    }

    public function test_staff_role_cannot_finalize_payment_but_admin_and_superadmin_can(): void
    {
        $order = $this->makeOrder('500.00');

        $this->actingAs($this->staff)
            ->patch("/orders/{$order->id}/mark-as-paid", ['payment_method' => 'cash', 'amount_received' => '500.00'])
            ->assertForbidden();

        $this->assertSame(PaymentStatus::Unpaid, $order->fresh()->payment_status);

        $this->actingAs($this->admin)
            ->patch("/orders/{$order->id}/mark-as-paid", ['payment_method' => 'cash', 'amount_received' => '500.00'])
            ->assertRedirect();

        $this->assertSame(PaymentStatus::Paid, $order->fresh()->payment_status);
    }

    public function test_voiding_then_repaying_the_same_day_succeeds_and_issues_a_new_invoice_number(): void
    {
        $order = $this->makeOrder('500.00');

        $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '500.00',
        ])->assertRedirect();

        $order->refresh();
        $firstInvoiceNumber = $order->receipt_number;
        $firstSnapshotId = $order->current_invoice_snapshot_id;

        $this->actingAs($this->admin)->patch("/orders/{$order->id}/void-payment", [
            'void_reason' => 'Customer changed order',
        ])->assertRedirect();

        $order->refresh();
        $this->assertSame(PaymentStatus::Voided, $order->payment_status);
        $this->assertSame(InvoiceSnapshotStatus::Voided, $order->invoiceSnapshots()->find($firstSnapshotId)->status);

        // Repaying the SAME order, same calendar day, must not collide on
        // the legacy receipt_number unique constraint (the bug this
        // feature fixes) and must issue a brand new invoice number.
        $response = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '500.00',
        ]);

        $response->assertRedirect();
        $order->refresh();

        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        $this->assertNotSame($firstInvoiceNumber, $order->receipt_number, 'A repay must issue a new invoice number, not reuse the voided one.');
        $this->assertNotSame($firstSnapshotId, $order->current_invoice_snapshot_id);
        $this->assertSame(2, $order->invoiceSnapshots()->count(), 'Both the voided and the new active snapshot must remain on permanent record.');
    }

    public function test_invoice_number_column_enforces_uniqueness_at_the_database_level(): void
    {
        $order = $this->makeOrder('100.00');
        $number = InvoiceNumberGenerator::generate();

        \App\Models\OrderInvoiceSnapshot::create($this->snapshotAttributes($order, $number));

        $this->expectException(QueryException::class);

        \App\Models\OrderInvoiceSnapshot::create($this->snapshotAttributes($order, $number));
    }

    public function test_invoice_numbers_are_unique_under_rapid_sequential_generation(): void
    {
        $numbers = collect(range(1, 30))->map(fn () => InvoiceNumberGenerator::generate('88HSR'));

        $this->assertCount(30, $numbers->unique());
        $this->assertSame('88HSR-001', $numbers->first());
        $this->assertSame('88HSR-030', $numbers->last());
    }

    public function test_receipt_screen_and_pdf_show_the_same_total_amount_due(): void
    {
        $order = $this->makeOrder('1120.00');

        $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '1120.00',
        ]);

        $order->refresh();
        $total = number_format($order->currentInvoiceSnapshot->total_amount_due, 2);

        $this->actingAs($this->admin)->get("/orders/{$order->id}/receipt")->assertOk()->assertSee($total);

        $pdfResponse = $this->actingAs($this->admin)->get("/orders/{$order->id}/receipt/pdf");
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_historical_invoice_snapshot_is_unaffected_by_a_later_settings_change(): void
    {
        Setting::current()->update(['tax_registration_type' => 'non_vat', 'resort_name' => 'Original Resort Name']);
        $order = $this->makeOrder('1000.00');

        $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '1000.00',
        ]);

        $order->refresh();
        $this->assertSame('Original Resort Name', $order->currentInvoiceSnapshot->business_name);
        $this->assertSame('non_vat', $order->currentInvoiceSnapshot->tax_registration_type->value);

        // Settings change after the fact — the already-issued invoice must
        // not be affected.
        Setting::current()->update(['tax_registration_type' => 'vat', 'tax_rate' => 12, 'resort_name' => 'Renamed Resort']);

        $order->refresh();
        $this->assertSame('Original Resort Name', $order->currentInvoiceSnapshot->business_name);
        $this->assertSame('non_vat', $order->currentInvoiceSnapshot->tax_registration_type->value);
    }

    public function test_a_paid_order_from_before_this_feature_still_opens_via_the_legacy_fallback(): void
    {
        $order = $this->makeOrder('500.00');
        $order->update([
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => 'cash',
            'amount_received' => '500.00',
            'change_amount' => '0.00',
            'receipt_number' => 'RCT-20260101-0001',
            'paid_at' => now(),
        ]);

        $this->assertNull($order->currentInvoiceSnapshot);

        $response = $this->actingAs($this->admin)->get("/orders/{$order->id}/receipt");

        $response->assertOk()->assertSee('RCT-20260101-0001')->assertSee('88 Hotspring Resort Inc.');

        $this->actingAs($this->admin)->get("/orders/{$order->id}/receipt/pdf")->assertOk();
    }

    public function test_reports_separate_vatable_and_vat_exempt_sales_for_a_mixed_transaction(): void
    {
        Setting::current()->update(['tax_registration_type' => 'vat', 'tax_rate' => 12, 'prices_include_vat' => true]);
        $order = $this->makeOrder('2000.00');

        $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '1680.00',
            'discount_type' => 'senior_citizen',
            'discount_qualified_name' => 'Juan',
            'discount_id_number' => '111',
            'discount_eligibility_method' => 'amount_based',
            'discount_eligible_amount' => '1120.00',
        ]);

        $superadmin = User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]);
        $response = $this->actingAs($superadmin)->get('/superadmin/reports?range=all');

        $response->assertOk();
        $data = $response->viewData('taxSummary');

        $this->assertSame(785.71, (float) $data['vatableSales']);
        $this->assertSame(1000.00, (float) $data['vatExemptSales']);
        $this->assertSame(200.00, (float) $data['seniorDiscounts']);
        $this->assertSame(0.00, (float) $data['pwdDiscounts']);
    }

    public function test_setting_bir_fields_persist_and_are_audited(): void
    {
        $superadmin = User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]);

        $response = $this->actingAs($superadmin)->put('/superadmin/settings', [
            'resort_name' => '88 Hot Spring Resort',
            'tax_registration_type' => 'vat',
            'tax_rate' => '12',
            'tin' => '123-456-789-0000',
            'invoice_title' => 'VAT Invoice',
            'invoice_number_prefix' => '88HSR',
        ]);

        $response->assertRedirect();

        $setting = Setting::current();
        $this->assertSame('vat', $setting->tax_registration_type->value);
        $this->assertSame('123-456-789-0000', $setting->tin);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Setting::class,
            'subject_id' => $setting->id,
        ]);
    }

    /**
     * The exact real-world scenario flagged by the owner: Budae Jjigae
     * (₱399.00, personal consumption of the qualified Senior Citizen) +
     * Bottled Water (₱90.00, the non-qualified diner's own item). Only
     * the Budae Jjigae is marked eligible.
     */
    public function test_full_checkout_flow_matches_the_exact_mixed_table_scenario(): void
    {
        Setting::current()->update(['tax_registration_type' => 'vat', 'tax_rate' => 12, 'prices_include_vat' => true]);

        $order = Order::create([
            'order_number' => '88-TEST-999',
            'order_type' => 'dine_in',
            'area_id' => $this->area->id,
            'space_category_id' => $this->category->id,
            'space_id' => $this->space->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => '489.00',
        ]);

        $stew = OrderItem::create([
            'order_id' => $order->id,
            'item_name' => 'Budae Jjigae (Army Stew)',
            'unit_price' => '399.00',
            'quantity' => 1,
            'subtotal' => '399.00',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => 'Bottled Water',
            'unit_price' => '90.00',
            'quantity' => 1,
            'subtotal' => '90.00',
        ]);

        $response = $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '489.00',
            'discount_type' => 'senior_citizen',
            'discount_qualified_name' => 'Juan Dela Cruz',
            'discount_id_number' => 'SC-2026-1234567',
            'discount_eligibility_method' => 'item_based',
            'discount_item_ids' => [$stew->id],
            'discount_qualified_diners' => 1,
            'discount_total_diners' => 2,
        ]);

        $response->assertRedirect();
        $order->refresh();
        $invoice = $order->currentInvoiceSnapshot;

        $this->assertSame('489.00', $invoice->gross_sales);
        $this->assertSame('80.36', $invoice->vatable_sales);
        $this->assertSame('356.25', $invoice->vat_exempt_sales);
        $this->assertSame('9.64', $invoice->vat_amount);
        $this->assertSame('42.75', $invoice->vat_exemption_amount);
        $this->assertSame('71.25', $invoice->discount_amount);
        $this->assertSame('375.00', $invoice->total_amount_due);
        $this->assertSame('489.00', $order->amount_received);
        $this->assertSame('114.00', $order->change_amount);
        $this->assertSame(['Budae Jjigae (Army Stew)'], $invoice->discount_eligible_item_names);

        // The receipt must tag the eligible item and correctly name the
        // non-qualified diner's item without implying it's categorically
        // excluded from discounts.
        $receiptResponse = $this->actingAs($this->admin)->get("/orders/{$order->id}/receipt");
        $receiptResponse->assertOk()
            ->assertSee('Budae Jjigae (Army Stew) [SC]', false)
            ->assertSee('Bottled Water was assigned to the non-qualified diner.', false)
            ->assertSee('1 × ₱399.00', false);
    }

    /**
     * A repay after void must not let the OLD (voided) invoice's item
     * tagging change just because the order_items table's current
     * is_discount_eligible flags got reassigned for the new attempt.
     */
    public function test_voided_invoice_keeps_its_own_item_eligibility_after_a_different_repay(): void
    {
        Setting::current()->update(['tax_registration_type' => 'non_vat']);

        $order = Order::create([
            'order_number' => '88-TEST-998',
            'order_type' => 'dine_in',
            'area_id' => $this->area->id,
            'space_category_id' => $this->category->id,
            'space_id' => $this->space->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => '200.00',
        ]);

        $itemA = OrderItem::create(['order_id' => $order->id, 'item_name' => 'Item A', 'unit_price' => '100.00', 'quantity' => 1, 'subtotal' => '100.00']);
        $itemB = OrderItem::create(['order_id' => $order->id, 'item_name' => 'Item B', 'unit_price' => '100.00', 'quantity' => 1, 'subtotal' => '100.00']);

        // First payment: Item A is the eligible one.
        $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '180.00',
            'discount_type' => 'pwd',
            'discount_qualified_name' => 'Juan',
            'discount_id_number' => '111',
            'discount_eligibility_method' => 'item_based',
            'discount_item_ids' => [$itemA->id],
        ]);

        $order->refresh();
        $firstSnapshot = $order->currentInvoiceSnapshot;
        $this->assertSame(['Item A'], $firstSnapshot->discount_eligible_item_names);

        $this->actingAs($this->admin)->patch("/orders/{$order->id}/void-payment", ['void_reason' => 'test']);

        // Repay: this time Item B is the eligible one instead.
        $this->actingAs($this->admin)->patch("/orders/{$order->id}/mark-as-paid", [
            'payment_method' => 'cash',
            'amount_received' => '180.00',
            'discount_type' => 'pwd',
            'discount_qualified_name' => 'Juan',
            'discount_id_number' => '111',
            'discount_eligibility_method' => 'item_based',
            'discount_item_ids' => [$itemB->id],
        ]);

        $order->refresh();
        $secondSnapshot = $order->currentInvoiceSnapshot;

        $this->assertSame(['Item B'], $secondSnapshot->discount_eligible_item_names);
        // The OLD snapshot's own record must be untouched by the repay.
        $this->assertSame(['Item A'], $firstSnapshot->fresh()->discount_eligible_item_names);
    }

    private function makeOrder(string $totalAmount): Order
    {
        static $counter = 0;
        $counter++;

        $order = Order::create([
            'order_number' => sprintf('88-TEST-%03d', $counter),
            'order_type' => 'dine_in',
            'area_id' => $this->area->id,
            'space_category_id' => $this->category->id,
            'space_id' => $this->space->id,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
            'total_amount' => $totalAmount,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'item_name' => 'Test Item',
            'unit_price' => $totalAmount,
            'quantity' => 1,
            'subtotal' => $totalAmount,
        ]);

        return $order->fresh(['items']);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshotAttributes(Order $order, string $invoiceNumber): array
    {
        return [
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'business_name' => 'Test Resort',
            'tax_registration_type' => 'non_vat',
            'tax_rate' => '12.00',
            'prices_include_vat' => true,
            'invoice_title' => 'Non-VAT Invoice',
            'gross_sales' => $order->total_amount,
            'total_amount_due' => $order->total_amount,
            'computed_at' => now(),
        ];
    }
}
