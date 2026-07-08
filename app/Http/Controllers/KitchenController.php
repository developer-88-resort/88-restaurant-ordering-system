<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(): View
    {
        $orders = Order::with(['table', 'items'])
            ->whereIn('status', [OrderStatus::Pending, OrderStatus::Preparing, OrderStatus::Ready])
            ->oldest()
            ->get()
            ->groupBy(fn (Order $order) => $order->status->value);

        $newCount = $orders->flatten()
            ->filter(fn (Order $order) => $order->created_at->diffInMinutes(now()) < 2)
            ->count();

        return view('kitchen.index', [
            'pending' => $orders->get(OrderStatus::Pending->value, collect()),
            'preparing' => $orders->get(OrderStatus::Preparing->value, collect()),
            'ready' => $orders->get(OrderStatus::Ready->value, collect()),
            'newCount' => $newCount,
        ]);
    }
}
