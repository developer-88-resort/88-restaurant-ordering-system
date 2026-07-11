<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\SpaceStatus;
use App\Events\CustomerOrderStatusUpdated;
use App\Events\KitchenUpdated;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Area;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Space;
use App\Models\SpaceCategory;
use App\Models\SpaceSession;
use App\Services\OrderCreator;
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
        $orders = Order::with(['area', 'spaceCategory', 'space', 'creator'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('orders.index', [
            'orders' => $orders,
            'activeStatus' => $request->string('status')->toString() ?: null,
            'statusCounts' => $statusCounts,
            'totalOrders' => $statusCounts->sum(),
        ]);
    }

    public function create(): View
    {
        $menuCategories = MenuCategory::where('is_active', true)
            ->with(['menuItems' => fn ($query) => $query->where('is_available', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->menuItems->isNotEmpty())
            ->values();

        $areas = Area::where('is_active', true)
            ->with(['categories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $areas->flatMap(fn (Area $area) => $area->categories)->each(function ($category) {
            if ($category->is_free) {
                $category->setAttribute('occupied_count', $category->activeOccupancyCount());
                $category->setAttribute('capacity_count', $category->capacityCount());
            } else {
                $category->setRelation(
                    'spaces',
                    $category->spaces()->orderBy('sort_order')->orderBy('name')->get()
                );
            }
        });

        return view('orders.create', [
            'areas' => $areas,
            'categories' => $menuCategories,
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $order = DB::transaction(function () use ($request) {
            $isTakeout = $request->string('order_type')->toString() === OrderType::Takeout->value;
            $space = null;
            $spaceId = null;
            $spaceSessionId = null;

            if (! $isTakeout) {
                $category = SpaceCategory::findOrFail($request->integer('space_category_id'));
                $spaceId = $request->input('space_id') ?: null;
                $space = $spaceId ? Space::findOrFail($spaceId) : null;

                if (! $space && $category->is_free && $category->usesSpacePool()) {
                    $space = $category->spaces()
                        ->where('status', SpaceStatus::Available)
                        ->orderBy('sort_order')
                        ->orderBy('name')
                        ->first();
                    $spaceId = $space?->id;
                }

                if (! $space) {
                    $spaceSessionId = SpaceSession::create([
                        'category_id' => $request->integer('space_category_id'),
                    ])->id;
                }
            }

            return OrderCreator::create($request->input('items'), [
                'order_type' => $isTakeout ? OrderType::Takeout : OrderType::DineIn,
                'area_id' => $isTakeout ? null : $request->integer('area_id'),
                'space_category_id' => $isTakeout ? null : $request->integer('space_category_id'),
                'space_id' => $spaceId,
                'space_session_id' => $spaceSessionId,
                'created_by' => auth()->id(),
                'notes' => $request->string('notes')->toString() ?: null,
            ], $space);
        });

        broadcast(new KitchenUpdated());

        return redirect()->route('orders.show', $order)
            ->with('status', __('Order created successfully.'));
    }

    public function show(Order $order): View
    {
        $order->load(['area', 'spaceCategory', 'space', 'creator', 'items']);

        return view('orders.show', ['order' => $order]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        if ($order->status->isFinal()) {
            return redirect()->back()
                ->with('error', __('Order :number is already :status and can no longer be changed.', [
                    'number' => $order->orderNumber(),
                    'status' => $order->status->label(),
                ]));
        }

        $request->validate([
            'status' => ['required', new Enum(OrderStatus::class)],
        ]);

        $order->update(['status' => $request->string('status')->toString()]);

        if ($order->status->isFinal()) {
            $this->releaseOrderLocation($order);
        }

        broadcast(new KitchenUpdated());
        broadcast(new CustomerOrderStatusUpdated($order));

        return redirect()->back()->with('status', __('Order :number is now :status.', [
            'number' => $order->orderNumber(),
            'status' => $order->status->label(),
        ]));
    }

    /**
     * Free up the space (or pooled session) an order was using once the
     * order reaches a final state (Completed/Cancelled), so it's ready for
     * the next customer without staff having to release it by hand.
     */
    protected function releaseOrderLocation(Order $order): void
    {
        if ($order->space && $order->space->status !== SpaceStatus::Available) {
            $order->space->setStatusWithSharedTables(SpaceStatus::Available);

            return;
        }

        if ($order->spaceSession && $order->spaceSession->status === 'active') {
            $order->spaceSession->update(['status' => 'completed', 'ended_at' => now()]);
        }
    }

    public function markAsPaid(Request $request, Order $order): RedirectResponse
    {
        if ($order->payment_status !== PaymentStatus::Unpaid) {
            return redirect()->back()
                ->with('error', __('Order :number is already :status and cannot be marked as paid again.', [
                    'number' => $order->orderNumber(),
                    'status' => $order->payment_status->label(),
                ]));
        }

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

        return redirect()->back()->with('status', __('Order :number marked as paid.', ['number' => $order->orderNumber()]));
    }

    public function voidPayment(Request $request, Order $order): RedirectResponse
    {
        if ($order->payment_status !== PaymentStatus::Paid) {
            return redirect()->back()
                ->with('error', __('Order :number has no paid payment to void.', ['number' => $order->orderNumber()]));
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

        return redirect()->back()->with('status', __('Payment for order :number has been voided.', ['number' => $order->orderNumber()]));
    }

    public function receipt(Order $order): View
    {
        abort_unless($order->receipt_number, 404);

        $order->load(['area', 'spaceCategory', 'space', 'creator', 'items']);

        return view('orders.receipt', ['order' => $order]);
    }

    public function receiptPdf(Order $order): Response
    {
        abort_unless($order->receipt_number, 404);

        $order->load(['area', 'spaceCategory', 'space', 'creator', 'items']);

        $pdf = Pdf::loadView('orders.receipt-pdf', ['order' => $order])->setPaper('a5', 'portrait');

        // Dompdf's bundled fonts (DejaVu Sans, Helvetica, Courier, ...) have no
        // Hangul glyphs, so Korean text would render as missing-glyph boxes.
        // Register a Korean-capable font so it gets embedded in the output.
        $fontMetrics = $pdf->getDomPDF()->getFontMetrics();
        $fontMetrics->registerFont(
            ['family' => 'Nanum Gothic Coding', 'style' => 'normal', 'weight' => 'normal'],
            resource_path('fonts/NanumGothicCoding-Regular.ttf')
        );
        $fontMetrics->registerFont(
            ['family' => 'Nanum Gothic Coding', 'style' => 'normal', 'weight' => 'bold'],
            resource_path('fonts/NanumGothicCoding-Bold.ttf')
        );

        return $pdf->download("{$order->receipt_number}.pdf");
    }
}
