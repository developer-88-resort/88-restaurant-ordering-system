<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\SpaceStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Space;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todaysSales = Order::where('payment_status', PaymentStatus::Paid)
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->sum('total_amount');

        $activeOrders = Order::whereIn('status', [
            OrderStatus::Pending,
            OrderStatus::Preparing,
            OrderStatus::Ready,
            OrderStatus::Served,
        ])->count();

        $pendingOrders = Order::where('status', OrderStatus::Pending)->count();

        $unpaidOrders = Order::where('payment_status', PaymentStatus::Unpaid)->count();

        $popularThisWeek = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->leftJoin('menu_categories', 'menu_categories.id', '=', 'menu_items.menu_category_id')
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('orders.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->select(
                'order_items.item_name',
                'menu_categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_qty')
            )
            ->groupBy('order_items.item_name', 'menu_categories.name')
            ->orderByDesc('total_qty')
            ->first();

        $totalSpaces = Space::count();
        $occupiedSpaces = Space::where('status', SpaceStatus::Occupied)->count();

        $recentOrders = Order::with(['area', 'spaceCategory', 'space'])->latest()->limit(5)->get();

        return view('superadmin.dashboard', [
            'todaysSales' => $todaysSales,
            'activeOrders' => $activeOrders,
            'pendingOrders' => $pendingOrders,
            'unpaidOrders' => $unpaidOrders,
            'popularThisWeek' => $popularThisWeek,
            'totalSpaces' => $totalSpaces,
            'occupiedSpaces' => $occupiedSpaces,
            'recentOrders' => $recentOrders,
            'adminCount' => User::where('role', UserRole::Admin)->count(),
            'staffCount' => User::where('role', UserRole::Staff)->count(),
        ]);
    }
}
