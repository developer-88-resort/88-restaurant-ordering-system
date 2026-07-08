<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RestaurantTable;
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
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('orders.created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->select('order_items.item_name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_qty')
            ->first();

        $totalTables = RestaurantTable::count();
        $occupiedTables = RestaurantTable::whereIn('status', [TableStatus::Occupied, TableStatus::InSession])->count();

        $recentOrders = Order::with('table')->latest()->limit(5)->get();

        return view('superadmin.dashboard', [
            'todaysSales' => $todaysSales,
            'activeOrders' => $activeOrders,
            'pendingOrders' => $pendingOrders,
            'unpaidOrders' => $unpaidOrders,
            'popularThisWeek' => $popularThisWeek,
            'totalTables' => $totalTables,
            'occupiedTables' => $occupiedTables,
            'recentOrders' => $recentOrders,
            'adminCount' => User::where('role', UserRole::Admin)->count(),
            'staffCount' => User::where('role', UserRole::Staff)->count(),
        ]);
    }
}
