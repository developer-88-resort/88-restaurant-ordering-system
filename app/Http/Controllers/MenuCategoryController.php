<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('menu-categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('menu-categories.create', [
            'existingCategories' => MenuCategory::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMenuCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        MenuCategory::create($data);

        return redirect()->route('menu-categories.index')
            ->with('status', __('Menu category created successfully.'));
    }

    public function edit(MenuCategory $menuCategory): View
    {
        return view('menu-categories.edit', [
            'category' => $menuCategory,
            'existingCategories' => MenuCategory::where('id', '!=', $menuCategory->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateMenuCategoryRequest $request, MenuCategory $menuCategory): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $menuCategory->update($data);

        return redirect()->route('menu-categories.index')
            ->with('status', __('Menu category updated successfully.'));
    }

    public function toggleStatus(MenuCategory $menuCategory): RedirectResponse
    {
        $menuCategory->update(['is_active' => ! $menuCategory->is_active]);

        return redirect()->back()->with('status', $menuCategory->is_active
            ? __('":name" is now active.', ['name' => $menuCategory->name])
            : __('":name" is now inactive.', ['name' => $menuCategory->name]));
    }

    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        if ($menuCategory->menuItems()->exists()) {
            return redirect()->route('menu-categories.index')
                ->with('error', __('Cannot delete a category that still has menu items. Move or delete its items first.'));
        }

        $menuCategory->delete();

        return redirect()->route('menu-categories.index')
            ->with('status', __('Menu category deleted successfully.'));
    }
}
