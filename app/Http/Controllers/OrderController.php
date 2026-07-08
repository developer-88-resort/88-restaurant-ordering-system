<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TableStatus;
use App\Events\KitchenUpdated;
use App\Http\Requests\StoreOrderRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::with(['table', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'activeStatus' => $request->string('status')->toString() ?: null,
        ]);
    }

    public function create(): View
    {
        $categories = MenuCategory::where('is_active', true)
            ->with(['menuItems' => fn ($query) => $query->where('is_available', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->menuItems->isNotEmpty())
            ->values();

        return view('orders.create', [
            'tables' => RestaurantTable::orderBy('table_number')->get(),
            'categories' => $categories,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = DB::transaction(function () use ($request) {
            $lines = collect($request->input('items'))->map(function ($line) {
                $menuItem = MenuItem::findOrFail($line['menu_item_id']);
                $unitPrice = (float) $menuItem->price;
                $quantity = (int) $line['quantity'];

                return [
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $unitPrice * $quantity,
                    'notes' => $line['notes'] ?? null,
                ];
            });

            $order = Order::create([
                'table_id' => $request->integer('table_id'),
                'created_by' => auth()->id(),
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'total_amount' => $lines->sum('subtotal'),
                'notes' => $request->string('notes')->toString() ?: null,
            ]);

            $order->items()->createMany($lines->all());

            if ($order->table->status === TableStatus::Available) {
                $order->table->update(['status' => TableStatus::Occupied]);
            }

            return $order;
        });

        broadcast(new KitchenUpdated());

        return redirect()->route('orders.show', $order)
            ->with('status', 'Order created successfully.');
    }

    public function show(Order $order): View
    {
        $order->load(['table', 'creator', 'items']);

        return view('orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        if ($order->status->isFinal()) {
            return redirect()->back()
                ->with('error', "Order {$order->orderNumber()} is already {$order->status->label()} and can no longer be changed.");
        }

        $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        $order->update(['status' => $request->string('status')->toString()]);

        broadcast(new KitchenUpdated());

        return redirect()->back()->with('status', "Order {$order->orderNumber()} is now {$order->status->label()}.");
    }

    public function markAsPaid(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'amount_received' => ['required', 'numeric', 'min:'.$order->total_amount],
        ]);

        $amountReceived = (float) $request->input('amount_received');

        $order->update([
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => 'cash',
            'amount_received' => $amountReceived,
            'change_amount' => $amountReceived - (float) $order->total_amount,
            'receipt_number' => 'RCT-'.now()->format('Ymd').'-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('status', "Order {$order->orderNumber()} marked as paid.");
    }

    public function voidPayment(Request $request, Order $order): RedirectResponse
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            return redirect()->back()
                ->with('error', "Order {$order->orderNumber()} has no paid payment to void.");
        }

        $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        $order->update([
            'payment_status' => PaymentStatus::Voided,
            'voided_by' => auth()->id(),
            'voided_at' => now(),
            'void_reason' => $request->string('void_reason')->toString(),
        ]);

        return redirect()->back()->with('status', "Payment for order {$order->orderNumber()} has been voided.");
    }

    public function receipt(Order $order): View
    {
        abort_unless($order->receipt_number, 404);

        $order->load(['table', 'creator', 'items']);

        return view('orders.receipt', ['order' => $order]);
    }

    public function receiptPdf(Order $order): Response
    {
        abort_unless($order->receipt_number, 404);

        $order->load(['table', 'creator', 'items']);

        $pdf = Pdf::loadView('orders.receipt-pdf', ['order' => $order])->setPaper('a5', 'portrait');

        return $pdf->download("{$order->receipt_number}.pdf");
    }
}
