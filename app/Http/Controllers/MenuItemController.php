<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Category and search both filter client-side (instant, no
        // reload) — every item ships to the page once and the filtering
        // happens in the browser.
        $items = MenuItem::with('menuCategory')
            ->orderBy('name')
            ->get();

        return view('menu-items.index', [
            'items' => $items,
            'categories' => $categories,
            'hasCategories' => $categories->isNotEmpty(),
        ]);
    }

    public function create(): View
    {
        return view('menu-items.create', [
            'categories' => MenuCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_available'] = $request->boolean('is_available', true);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

        MenuItem::create($data);

        return redirect()->route('menu-items.index')
            ->with('status', __('Menu item created successfully.'));
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('menu-items.edit', [
            'item' => $menuItem,
            'categories' => MenuCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validated();
        $data['is_available'] = $request->boolean('is_available');

        if ($request->hasFile('image')) {
            if ($menuItem->image_path) {
                Storage::disk('public')->delete($menuItem->image_path);
            }

            $data['image_path'] = $request->file('image')->store('menu-items', 'public');
        }

        $menuItem->update($data);

        return redirect()->route('menu-items.index')
            ->with('status', __('Menu item updated successfully.'));
    }

    public function toggleAvailability(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return redirect()->back()->with('status', $menuItem->is_available
            ? __('":name" is now available.', ['name' => $menuItem->name])
            : __('":name" is now unavailable.', ['name' => $menuItem->name]));
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return redirect()->back()
            ->with('status', __('Menu item deleted successfully.'));
    }
}
