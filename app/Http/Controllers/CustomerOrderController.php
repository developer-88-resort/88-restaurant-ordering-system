<?php

namespace App\Http\Controllers;

use App\Enums\OrderType;
use App\Enums\SpaceStatus;
use App\Events\KitchenUpdated;
use App\Http\Requests\StoreCustomerOrderRequest;
use App\Models\MenuCategory;
use App\Models\Order;
use App\Models\Space;
use App\Services\OrderCreator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Public, unauthenticated customer self-service ordering — reached only by
 * scanning a Space's QR code. No auth middleware anywhere in this
 * controller; every action is intentionally reachable by an anonymous
 * guest.
 */
class CustomerOrderController extends Controller
{
    public function show(Space $space): View
    {
        if (! $this->isOrderable($space)) {
            return view('customer.space-unavailable', ['space' => $space]);
        }

        return view('customer.menu', [
            'space' => $space,
            'categories' => $this->activeMenu(),
        ]);
    }

    public function store(StoreCustomerOrderRequest $request, Space $space): RedirectResponse
    {
        if (! $this->isOrderable($space)) {
            return redirect()->route('customer.spaces.show', $space);
        }

        $order = OrderCreator::create($request->input('items'), [
            'order_type' => OrderType::DineIn,
            'area_id' => $space->area_id,
            'space_category_id' => $space->category_id,
            'space_id' => $space->id,
            'space_session_id' => null,
            'created_by' => auth()->id(),
            'notes' => $request->string('notes')->toString() ?: null,
        ], $space);

        broadcast(new KitchenUpdated());

        return redirect()->route('customer.orders.status', $order->public_token);
    }

    public function status(string $token): View
    {
        $order = Order::where('public_token', $token)->with('items')->firstOrFail();

        return view('customer.status', ['order' => $order]);
    }

    /**
     * Occupied is fine — that's usually the customer themselves already
     * seated, placing a second round. Maintenance/Disabled/Reserved, or a
     * whole Area taken offline for service, block ordering.
     */
    protected function isOrderable(Space $space): bool
    {
        if (! $space->area->is_active) {
            return false;
        }

        return ! in_array($space->status, [
            SpaceStatus::Maintenance,
            SpaceStatus::Disabled,
            SpaceStatus::Reserved,
        ], true);
    }

    protected function activeMenu(): Collection
    {
        return MenuCategory::where('is_active', true)
            ->with(['menuItems' => fn ($query) => $query->where('is_available', true)->orderBy('name')])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->filter(fn ($category) => $category->menuItems->isNotEmpty())
            ->values();
    }
}
