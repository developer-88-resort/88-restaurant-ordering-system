<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->string('range')->toString() ?: 'month';

        [$start, $end, $rangeLabel] = match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay(), 'Today'],
            'week' => [now()->startOfWeek(), now()->endOfWeek(), 'This Week'],
            'all' => [Carbon::parse(Order::min('created_at') ?? now()), now()->endOfDay(), 'All Time'],
            default => [now()->startOfMonth(), now()->endOfMonth(), 'This Month'],
        };

        $paidOrders = Order::where('payment_status', PaymentStatus::Paid)
            ->whereBetween('created_at', [$start, $end]);

        $totalRevenue = (clone $paidOrders)->sum('total_amount');
        $paidOrderCount = (clone $paidOrders)->count();
        $averageOrderValue = $paidOrderCount > 0 ? $totalRevenue / $paidOrderCount : 0;

        $totalOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->count();

        $cancelledOrders = Order::whereBetween('created_at', [$start, $end])
            ->where('status', 'cancelled')
            ->count();

        $bestSellers = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'order_items.item_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
            )
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        $categorySales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('menu_items', 'menu_items.id', '=', 'order_items.menu_item_id')
            ->leftJoin('menu_categories', 'menu_categories.id', '=', 'menu_items.menu_category_id')
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                DB::raw("COALESCE(menu_categories.name, 'Uncategorized') as category_name"),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
            )
            ->groupBy('category_name')
            ->orderByDesc('total_revenue')
            ->get();

        $dailySales = DB::table('orders')
            ->where('payment_status', PaymentStatus::Paid->value)
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total_amount) as revenue'),
            )
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        return view('superadmin.reports.index', [
            'range' => $range,
            'rangeLabel' => $rangeLabel,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'cancelledOrders' => $cancelledOrders,
            'bestSellers' => $bestSellers,
            'categorySales' => $categorySales,
            'dailySales' => $dailySales,
        ]);
    }
}
