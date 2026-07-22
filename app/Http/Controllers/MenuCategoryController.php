<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuCategoryRequest;
use App\Http\Requests\UpdateMenuCategoryRequest;
use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $categories = MenuCategory::withCount('menuItems')
            ->when($showArchived, fn ($query) => $query->onlyTrashed())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('menu-categories.index', [
            'categories' => $categories,
            'showArchived' => $showArchived,
            'archivedCount' => MenuCategory::onlyTrashed()->count(),
        ]);
    }

    public function create(): View
    {
        return view('menu-categories.create', [
            'existingCategories' => MenuCategory::orderBy('sort_order')->orderBy('name')->get(),
            'nextSortOrder' => MenuCategory::max('sort_order') + 1,
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

    /**
     * Archives (soft-deletes) rather than permanently removing — reversible
     * via restore(), unlike the old hard delete.
     */
    public function destroy(MenuCategory $menuCategory): RedirectResponse
    {
        if ($menuCategory->menuItems()->exists()) {
            return redirect()->route('menu-categories.index')
                ->with('error', __('Cannot archive a category that still has menu items. Move or archive its items first.'));
        }

        $menuCategory->delete();

        return redirect()->route('menu-categories.index')
            ->with('status', __('":name" was archived.', ['name' => $menuCategory->name]));
    }

    public function restore(MenuCategory $menuCategory): RedirectResponse
    {
        $menuCategory->restore();

        return redirect()->route('menu-categories.index', ['archived' => 1])
            ->with('status', __('":name" was restored.', ['name' => $menuCategory->name]));
    }
}
