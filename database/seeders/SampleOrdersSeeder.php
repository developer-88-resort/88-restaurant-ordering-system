<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\OrderCreator;
use Illuminate\Database\Seeder;

class SampleOrdersSeeder extends Seeder
{
    /**
     * Seed Take-out sample orders across every status, so Order Management
     * and the Kitchen board have something to show without needing any
     * Area/Space sample data. Requires SampleMenuSeeder to have run first.
     */
    public function run(): void
    {
        $menuItemIds = MenuItem::pluck('id', 'name');

        if ($menuItemIds->isEmpty()) {
            $this->command?->warn('No menu items found — run SampleMenuSeeder first.');

            return;
        }

        $staffId = User::where('email', 'dev@88hotspring.com')->value('id');

        $plans = [
            ['status' => OrderStatus::Pending, 'items' => [['Pork Samgyupsal (Marinated)', 2], ['Bottled Water', 2]]],
            ['status' => OrderStatus::Pending, 'items' => [['Bibimbap', 1], ['Iced Tea', 1]]],
            ['status' => OrderStatus::Pending, 'items' => [['Kimchi Jjigae', 1], ['Soft Drinks (Regular)', 2]]],
            ['status' => OrderStatus::Preparing, 'items' => [['Beef Bulgogi', 1], ['Kimchi Fried Rice', 1], ['Soft Drinks (Regular)', 2]]],
            ['status' => OrderStatus::Preparing, 'items' => [['Budae Jjigae (Army Stew)', 1], ['Bottled Water', 3]]],
            ['status' => OrderStatus::Ready, 'items' => [['Chicken Galbi', 1], ['Japchae with Rice', 1]]],
            ['status' => OrderStatus::Ready, 'items' => [['Korean Pancake (Pajeon)', 1], ['Korean Rice Wine (Makgeolli)', 1]]],
            ['status' => OrderStatus::Served, 'items' => [['Sundubu Jjigae', 1], ['Bottled Water', 1]]],
            ['status' => OrderStatus::Completed, 'paid' => true, 'items' => [['Spicy Pork Bulgogi', 1], ['Bulgogi Rice Bowl', 1], ['Soft Drinks (Regular)', 2]]],
            ['status' => OrderStatus::Completed, 'paid' => true, 'items' => [['Samgyetang', 1], ['Hot Barley Tea', 2]]],
            ['status' => OrderStatus::Completed, 'paid' => true, 'items' => [['Mango Bingsu', 2], ['Fried Mandu (Dumplings)', 1]]],
            ['status' => OrderStatus::Cancelled, 'items' => [['Fresh Fruit Platter', 1], ['Iced Tea', 1]]],
        ];

        foreach ($plans as $plan) {
            $items = collect($plan['items'])
                ->filter(fn ($line) => $menuItemIds->has($line[0]))
                ->map(fn ($line) => [
                    'menu_item_id' => $menuItemIds[$line[0]],
                    'quantity' => $line[1],
                ])
                ->all();

            if ($items === []) {
                continue;
            }

            $order = OrderCreator::create($items, [
                'order_type' => OrderType::Takeout,
                'area_id' => null,
                'space_category_id' => null,
                'space_id' => null,
                'space_session_id' => null,
                'created_by' => $staffId,
                'notes' => null,
            ], null);

            $order->status = $plan['status'];

            if ($plan['paid'] ?? false) {
                $order->payment_status = PaymentStatus::Paid;
                $order->payment_method = 'cash';
                $order->amount_received = $order->total_amount;
                $order->change_amount = 0;
                $order->receipt_number = 'RCT-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT);
                $order->paid_at = now();
            }

            $order->save();
        }
    }
}
