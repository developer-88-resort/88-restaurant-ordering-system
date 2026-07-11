<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\OrderNumberGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderNumberTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_sequence_starts_at_001_and_increments(): void
    {
        $date = Carbon::parse('2026-07-11 10:00:00', 'Asia/Manila');

        $this->assertSame('88-0711-001', OrderNumberGenerator::generate($date));
        $this->assertSame('88-0711-002', OrderNumberGenerator::generate($date));
        $this->assertSame('88-0711-003', OrderNumberGenerator::generate($date));
    }

    public function test_sequence_resets_to_001_on_a_new_calendar_day(): void
    {
        $day1 = Carbon::parse('2026-07-11 15:00:00', 'Asia/Manila');
        $day2 = Carbon::parse('2026-07-12 08:00:00', 'Asia/Manila');

        $this->assertSame('88-0711-001', OrderNumberGenerator::generate($day1));
        $this->assertSame('88-0711-002', OrderNumberGenerator::generate($day1));
        $this->assertSame('88-0712-001', OrderNumberGenerator::generate($day2));
        $this->assertSame('88-0712-002', OrderNumberGenerator::generate($day2));
        // Going back to day 1 later must not restart or collide — it continues where it left off.
        $this->assertSame('88-0711-003', OrderNumberGenerator::generate($day1));
    }

    public function test_asia_manila_midnight_boundary_rolls_the_sequence_over(): void
    {
        $justBeforeMidnight = Carbon::parse('2026-07-11 23:59:30', 'Asia/Manila');
        $justAfterMidnight = Carbon::parse('2026-07-12 00:00:30', 'Asia/Manila');

        $this->assertSame('88-0711-001', OrderNumberGenerator::generate($justBeforeMidnight));
        $this->assertSame('88-0712-001', OrderNumberGenerator::generate($justAfterMidnight));
    }

    public function test_a_utc_timestamp_is_converted_to_asia_manila_before_numbering(): void
    {
        // 2026-07-11 16:30 UTC is 2026-07-12 00:30 in Asia/Manila (UTC+8) — a different day.
        $utcTime = Carbon::parse('2026-07-11 16:30:00', 'UTC');

        $this->assertSame('88-0712-001', OrderNumberGenerator::generate($utcTime));
    }

    public function test_numbers_are_unique_and_gapless_under_rapid_sequential_generation(): void
    {
        $date = Carbon::parse('2026-07-11 09:00:00', 'Asia/Manila');

        $numbers = collect(range(1, 50))->map(fn () => OrderNumberGenerator::generate($date));

        $this->assertCount(50, $numbers->unique(), 'Every generated number must be unique — no duplicates under repeated/concurrent-style calls.');
        $this->assertSame('88-0711-001', $numbers->first());
        $this->assertSame('88-0711-050', $numbers->last());
    }

    public function test_order_number_column_enforces_uniqueness_at_the_database_level(): void
    {
        $this->makeOrder('88-0711-999');

        $this->expectException(QueryException::class);

        $this->makeOrder('88-0711-999');
    }

    public function test_deleting_an_order_does_not_free_its_number_for_reuse(): void
    {
        $date = Carbon::parse('2026-07-11 09:00:00', 'Asia/Manila');

        $order = $this->makeOrder(OrderNumberGenerator::generate($date));
        $this->assertSame('88-0711-001', $order->order_number);

        $order->delete();

        $this->assertSame(
            '88-0711-002',
            OrderNumberGenerator::generate($date),
            "The deleted order's number must never be reused."
        );
    }

    public function test_cancelling_an_order_keeps_its_original_number(): void
    {
        $order = $this->makeOrder(OrderNumberGenerator::generate(Carbon::parse('2026-07-11 09:00:00', 'Asia/Manila')));
        $original = $order->order_number;

        $order->update(['status' => OrderStatus::Cancelled]);

        $this->assertSame($original, $order->fresh()->order_number);
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_display_format_adds_hash_prefix_while_the_stored_value_stays_plain(): void
    {
        $order = $this->makeOrder('88-0711-007');

        $this->assertSame('88-0711-007', $order->order_number);
        $this->assertSame('#88-0711-007', $order->orderNumber());
    }

    private function makeOrder(string $orderNumber): Order
    {
        return Order::create([
            'order_number' => $orderNumber,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Unpaid,
        ]);
    }
}
