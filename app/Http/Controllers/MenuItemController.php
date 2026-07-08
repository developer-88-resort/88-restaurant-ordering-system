<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $categories = MenuCategory::withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $items = MenuItem::with('menuCategory')
            ->when($request->filled('category'), fn ($query) => $query->where('menu_category_id', $request->integer('category')))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->get();

        return view('menu-items.index', [
            'items' => $items,
            'categories' => $categories,
            'activeCategoryId' => $request->integer('category') ?: null,
            'search' => $request->string('search')->toString(),
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
            ->with('status', 'Menu item created successfully.');
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
            ->with('status', 'Menu item updated successfully.');
    }

    public function toggleAvailability(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return redirect()->back()
            ->with('status', "\"{$menuItem->name}\" is now ".($menuItem->is_available ? 'available' : 'unavailable').'.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        if ($menuItem->image_path) {
            Storage::disk('public')->delete($menuItem->image_path);
        }

        $menuItem->delete();

        return redirect()->back()
            ->with('status', 'Menu item deleted successfully.');
    }
}
