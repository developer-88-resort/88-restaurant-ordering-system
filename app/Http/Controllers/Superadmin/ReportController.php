<?php

namespace App\Http\Controllers\Superadmin;

use App\Enums\DiscountType;
use App\Enums\InvoiceSnapshotStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderInvoiceSnapshot;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('superadmin.reports.index', $this->buildReportData($request));
    }

    public function pdf(Request $request): Response
    {
        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('superadmin.reports.report-pdf', $data)->setPaper('a4', 'portrait');

        // Same Korean-capable font registration used for order receipts —
        // dompdf's bundled fonts have no Hangul glyphs.
        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $fontMetrics->registerFont(
            ['family' => 'Nanum Gothic Coding', 'style' => 'normal', 'weight' => 'normal'],
            resource_path('fonts/NanumGothicCoding-Regular.ttf')
        );
        $fontMetrics->registerFont(
            ['family' => 'Nanum Gothic Coding', 'style' => 'normal', 'weight' => 'bold'],
            resource_path('fonts/NanumGothicCoding-Bold.ttf')
        );

        $filename = 'Sales-Report-'.str_replace(' ', '-', $data['rangeLabel']).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildReportData(Request $request): array
    {
        $selectedDate = $this->parseSelectedDate($request->string('date')->toString());
        $selectedMonth = $selectedDate ? null : $this->parseSelectedMonth($request->string('month')->toString());
        $range = $request->string('range')->toString() ?: 'month';

        if ($selectedDate) {
            $start = $selectedDate->copy()->startOfDay();
            $end = $selectedDate->copy()->endOfDay();
            $rangeLabel = $selectedDate->format('F j, Y');
        } elseif ($selectedMonth) {
            $start = $selectedMonth->copy()->startOfMonth();
            $end = $selectedMonth->copy()->endOfMonth();
            $rangeLabel = $selectedMonth->format('F Y');
        } else {
            [$start, $end, $rangeLabel] = match ($range) {
                'today' => [now()->startOfDay(), now()->endOfDay(), 'Today'],
                'week' => [now()->startOfWeek(), now()->endOfWeek(), 'This Week'],
                'all' => [Carbon::parse(Order::min('created_at') ?? now()), now()->endOfDay(), 'All Time'],
                default => [now()->startOfMonth(), now()->endOfMonth(), 'This Month'],
            };
        }

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

        $comparison = $this->buildComparison($selectedDate, $selectedMonth, $range, [
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'cancelledOrders' => $cancelledOrders,
        ]);

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

        $areaSales = DB::table('orders')
            ->join('areas', 'areas.id', '=', 'orders.area_id')
            ->where('orders.payment_status', PaymentStatus::Paid->value)
            ->whereBetween('orders.created_at', [$start, $end])
            ->select(
                'areas.name as area_name',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(orders.total_amount) as total_revenue'),
            )
            ->groupBy('areas.name')
            ->orderByDesc('total_revenue')
            ->get();

        $bestSellers = $this->withPercentOfTotal($bestSellers, 'total_revenue', $totalRevenue);
        $categorySales = $this->withPercentOfTotal($categorySales, 'total_revenue', $totalRevenue);
        $areaSales = $this->withPercentOfTotal($areaSales, 'total_revenue', $totalRevenue);

        $taxSummary = $this->buildTaxSummary($start, $end);

        return [
            'range' => $range,
            'selectedMonth' => $selectedMonth?->format('Y-m'),
            'selectedDate' => $selectedDate?->format('Y-m-d'),
            'calendarMonth' => ($selectedDate ?? $selectedMonth ?? now())->format('Y-m'),
            'rangeLabel' => $rangeLabel,
            'totalRevenue' => $totalRevenue,
            'totalOrders' => $totalOrders,
            'averageOrderValue' => $averageOrderValue,
            'cancelledOrders' => $cancelledOrders,
            'comparison' => $comparison,
            'bestSellers' => $bestSellers,
            'categorySales' => $categorySales,
            'dailySales' => $dailySales,
            'areaSales' => $areaSales,
            'taxSummary' => $taxSummary,
        ];
    }

    /**
     * BIR tax/discount aggregates — summed directly from the already-
     * persisted OrderInvoiceSnapshot rows (the InvoiceCalculator's output,
     * frozen at payment time) rather than recomputed here, so this stays
     * consistent with whatever was actually shown on each invoice. Filtered
     * by `computed_at` (when the invoice was issued/paid), not `created_at`
     * (when the order was placed) — a different date basis than the
     * existing cards above, which is a known, pre-existing quirk left
     * as-is rather than changed by this feature.
     *
     * @return array<string, mixed>
     */
    protected function buildTaxSummary(Carbon $start, Carbon $end): array
    {
        $activeSnapshots = OrderInvoiceSnapshot::where('status', InvoiceSnapshotStatus::Active)
            ->whereBetween('computed_at', [$start, $end]);

        $discountTotals = (clone $activeSnapshots)
            ->whereNotNull('discount_type')
            ->selectRaw('discount_type, SUM(discount_amount) as total')
            ->groupBy('discount_type')
            ->pluck('total', 'discount_type');

        return [
            'netAmountCollected' => (clone $activeSnapshots)->sum('total_amount_due'),
            'vatableSales' => (clone $activeSnapshots)->sum('vatable_sales'),
            'vatExemptSales' => (clone $activeSnapshots)->sum('vat_exempt_sales'),
            'zeroRatedSales' => (clone $activeSnapshots)->sum('zero_rated_sales'),
            'vatAmount' => (clone $activeSnapshots)->sum('vat_amount'),
            'seniorDiscounts' => (float) ($discountTotals[DiscountType::SeniorCitizen->value] ?? 0),
            'pwdDiscounts' => (float) ($discountTotals[DiscountType::Pwd->value] ?? 0),
            'promoDiscounts' => (float) ($discountTotals[DiscountType::Promo->value] ?? 0),
            'serviceCharges' => (clone $activeSnapshots)->sum('service_charge_amount'),
            'voidedInvoices' => OrderInvoiceSnapshot::where('status', InvoiceSnapshotStatus::Voided)
                ->whereBetween('computed_at', [$start, $end])
                ->count(),
        ];
    }

    /**
     * Attaches a `percent` (share of $total, 0–100) to each row of a
     * DB::table() result set — used so the breakdown tables can show each
     * line's contribution at a glance, the way a "real" business report
     * would, instead of just a bare revenue figure.
     *
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return \Illuminate\Support\Collection<int, object>
     */
    protected function withPercentOfTotal($rows, string $field, float $total)
    {
        return $rows->map(function ($row) use ($field, $total) {
            $row->percent = $total > 0 ? ($row->{$field} / $total) * 100 : 0;

            return $row;
        });
    }

    /**
     * Compares the current period's key stats against the immediately
     * preceding period of the same kind (yesterday, last week, last
     * month, ...) so the dashboard can show a "+12% vs last month"-style
     * trend the way a normal business report would. "All Time" has no
     * prior period to compare against, so it's skipped entirely.
     *
     * @param  array<string, float|int>  $current
     * @return array<string, array{percent: ?float, label: string}>|null
     */
    protected function buildComparison(?Carbon $selectedDate, ?Carbon $selectedMonth, string $range, array $current): ?array
    {
        [$prevStart, $prevEnd, $label] = match (true) {
            (bool) $selectedDate => [
                $selectedDate->copy()->subDay()->startOfDay(),
                $selectedDate->copy()->subDay()->endOfDay(),
                __('vs previous day'),
            ],
            (bool) $selectedMonth => [
                $selectedMonth->copy()->subMonthNoOverflow()->startOfMonth(),
                $selectedMonth->copy()->subMonthNoOverflow()->endOfMonth(),
                __('vs previous month'),
            ],
            $range === 'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay(), __('vs yesterday')],
            $range === 'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek(), __('vs last week')],
            $range === 'month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth(), __('vs last month')],
            default => [null, null, null],
        };

        if (! $prevStart) {
            return null;
        }

        $prevPaidOrders = Order::where('payment_status', PaymentStatus::Paid)
            ->whereBetween('created_at', [$prevStart, $prevEnd]);

        $prevRevenue = (clone $prevPaidOrders)->sum('total_amount');
        $prevPaidCount = (clone $prevPaidOrders)->count();

        $previous = [
            'totalRevenue' => $prevRevenue,
            'totalOrders' => Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', '!=', 'cancelled')->count(),
            'averageOrderValue' => $prevPaidCount > 0 ? $prevRevenue / $prevPaidCount : 0,
            'cancelledOrders' => Order::whereBetween('created_at', [$prevStart, $prevEnd])->where('status', 'cancelled')->count(),
        ];

        $comparison = [];
        foreach ($current as $key => $value) {
            $comparison[$key] = [
                'percent' => $this->percentChange($value, $previous[$key]),
                'label' => $label,
            ];
        }

        return $comparison;
    }

    /**
     * Null means "no prior data to compare against" (shown as "New" in
     * the UI) rather than a misleading +/-infinity percentage.
     */
    protected function percentChange(float $current, float $previous): ?float
    {
        if ($previous == 0.0) {
            return $current > 0 ? null : 0.0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * The month picker posts a "Y-m" string (e.g. "2026-06"). Anything
     * malformed, or a future month (nothing to report yet), is silently
     * ignored so the page falls back to the quick-range pills instead of
     * erroring.
     */
    protected function parseSelectedMonth(string $month): ?Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        } catch (\Exception) {
            return null;
        }

        return $parsed->greaterThan(now()) ? null : $parsed;
    }

    /**
     * The calendar posts a "Y-m-d" string for a specific day. Same
     * fallback rules as the month: malformed or a future date is ignored
     * rather than erroring.
     */
    protected function parseSelectedDate(string $date): ?Carbon
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        } catch (\Exception) {
            return null;
        }

        return $parsed->greaterThan(now()->endOfDay()) ? null : $parsed;
    }
}
