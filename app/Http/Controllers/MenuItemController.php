<?php

namespace App\Http\Controllers;

use App\Enums\MenuItemAvailability;
use App\Events\MenuItemAvailabilityChanged;
use App\Http\Requests\StoreMenuItemRequest;
use App\Http\Requests\UpdateMenuItemRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\ModifierGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $query = MenuItem::with(['menuCategory', 'images', 'variants']);

        if ($showArchived) {
            $query->onlyTrashed();
        }

        if ($search = trim((string) $request->string('q'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($categoryId = $request->integer('category_id')) {
            $query->where('menu_category_id', $categoryId);
        }

        $availability = $request->string('availability')->toString();
        if (MenuItemAvailability::tryFrom($availability)) {
            $query->where('availability_status', $availability);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        match ($request->string('sort')->toString()) {
            'name_asc' => $query->orderBy('name'),
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'prep_asc' => $query->orderByRaw('prep_time_minutes IS NULL, prep_time_minutes'),
            'prep_desc' => $query->orderByDesc('prep_time_minutes'),
            'newest' => $query->orderByDesc('created_at'),
            default => $query->orderBy('sort_order')->orderBy('name'),
        };

        $items = $query->paginate(24)->withQueryString();

        $categories = MenuCategory::withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('menu-items.index', [
            'items' => $items,
            'categories' => $categories,
            'hasCategories' => $categories->isNotEmpty(),
            'showArchived' => $showArchived,
            'archivedCount' => MenuItem::onlyTrashed()->count(),
            'filters' => $request->only(['q', 'category_id', 'availability', 'featured', 'sort']),
            'availabilityOptions' => MenuItemAvailability::cases(),
        ]);
    }

    public function create(): View
    {
        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('menu-items.create', [
            'categories' => $categories,
            'availabilityOptions' => MenuItemAvailability::cases(),
            // Lets the form auto-fill Sort Order with "next in line" for
            // whichever category gets picked, instead of always showing 0
            // and leaving whoever's creating the item to guess the number.
            'nextSortOrders' => $categories->mapWithKeys(fn ($category) => [
                $category->id => (int) MenuItem::where('menu_category_id', $category->id)->max('sort_order') + 1,
            ]),
            'modifierGroups' => ModifierGroup::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(StoreMenuItemRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'variants', 'default_variant_index', 'modifier_group_ids']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['availability_status'] = $request->input('availability_status', MenuItemAvailability::Available->value);
        // Base price is optional once variants exist (validated in the
        // FormRequest); the column itself stays NOT NULL, so an intentionally
        // blank price just becomes 0 — display already ignores it in favor
        // of the variant price range once variants are present.
        $data['price'] = $request->filled('price') ? $request->input('price') : 0;

        $menuItem = MenuItem::create($data);

        $this->storeUploadedImages($request, $menuItem);
        $this->syncVariants($request, $menuItem);
        $menuItem->modifierGroups()->sync($request->input('modifier_group_ids', []));

        return redirect()->route('menu-items.index')
            ->with('status', __('Menu item created successfully.'));
    }

    public function edit(MenuItem $menuItem): View
    {
        // Active categories plus the item's own current category, even if
        // it has since gone inactive — otherwise the dropdown wouldn't be
        // able to show/keep the item's existing assignment.
        $categories = MenuCategory::where('is_active', true)
            ->orWhere('id', $menuItem->menu_category_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('menu-items.edit', [
            'item' => $menuItem->load(['images', 'variants', 'modifierGroups']),
            'categories' => $categories,
            'availabilityOptions' => MenuItemAvailability::cases(),
            'modifierGroups' => ModifierGroup::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateMenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->safe()->except(['images', 'remove_images', 'primary_image_id', 'variants', 'default_variant_index', 'modifier_group_ids']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['availability_status'] = $request->input('availability_status', $menuItem->availability_status->value);
        $data['price'] = $request->filled('price') ? $request->input('price') : 0;

        $wasAvailability = $menuItem->availability_status;

        $menuItem->update($data);

        foreach ((array) $request->input('remove_images', []) as $imageId) {
            $image = $menuItem->images()->find($imageId);
            if ($image) {
                Storage::disk('public')->delete($image->path);
                $image->delete();
            }
        }

        $this->storeUploadedImages($request, $menuItem);
        $this->syncVariants($request, $menuItem);
        $menuItem->modifierGroups()->sync($request->input('modifier_group_ids', []));

        if ($primaryId = $request->integer('primary_image_id')) {
            $menuItem->images()->update(['is_primary' => false]);
            $menuItem->images()->where('id', $primaryId)->update(['is_primary' => true]);
        } elseif (! $menuItem->images()->where('is_primary', true)->exists()) {
            // Deletions/replacements can leave no image flagged primary —
            // fall back to the first one by display order so the grid/
            // customer views always have something to show.
            $menuItem->images()->orderBy('sort_order')->first()?->update(['is_primary' => true]);
        }

        if ($menuItem->availability_status !== $wasAvailability) {
            broadcast(new MenuItemAvailabilityChanged($menuItem));
        }

        return redirect()->route('menu-items.index')
            ->with('status', __('Menu item updated successfully.'));
    }

    /**
     * Quick one-click "86 it" toggle (Available <-> Out of Stock) and the
     * full Seasonal/Hidden picker both post here. Called via fetch() from
     * the grid, not a form submit, so the page's search/filter query string
     * doesn't get lost — falls back to a classic redirect for non-JS callers.
     */
    public function setAvailability(Request $request, MenuItem $menuItem): RedirectResponse|JsonResponse
    {
        $status = MenuItemAvailability::tryFrom((string) $request->string('status'));

        abort_if($status === null, 422, __('Invalid availability status.'));

        $menuItem->update(['availability_status' => $status]);

        broadcast(new MenuItemAvailabilityChanged($menuItem));

        if ($request->wantsJson()) {
            return response()->json(['availability_status' => $status->value]);
        }

        return redirect()->back()->with('status', __('":name" is now :status.', [
            'name' => $menuItem->name,
            'status' => $status->label(),
        ]));
    }

    /**
     * Archives (soft-deletes) rather than permanently removing — historical
     * order_items keep their frozen item_name/unit_price either way, but a
     * reversible archive avoids irreversibly losing setup work (images,
     * description, etc.) from an accidental click.
     */
    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return redirect()->back()
            ->with('status', __('":name" was archived.', ['name' => $menuItem->name]));
    }

    public function restore(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->restore();

        return redirect()->route('menu-items.index', ['archived' => 1])
            ->with('status', __('":name" was restored.', ['name' => $menuItem->name]));
    }

    /**
     * Diffs the submitted variant rows against what the item already has:
     * rows with a matching `id` are updated, rows with no `id` are created,
     * and any existing variant whose `id` wasn't resubmitted at all (the
     * user removed its row client-side) gets archived — same "the absence
     * of a row means it's gone" approach as the images uploader, just
     * without a file to clean up.
     */
    protected function syncVariants(Request $request, MenuItem $menuItem): void
    {
        $rows = $request->input('variants', []);
        $defaultIndex = $request->input('default_variant_index');
        $keptIds = [];

        foreach ($rows as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $attributes = [
                'menu_item_id' => $menuItem->id,
                'name' => $name,
                'sku' => $row['sku'] ?: null,
                'price' => $row['price'] ?? 0,
                'sort_order' => $index,
                'is_default' => $defaultIndex !== null && (int) $defaultIndex === (int) $index,
            ];

            $variant = ! empty($row['id']) ? $menuItem->variants()->find($row['id']) : null;

            // Most variants (Solo/Medium/Large) look like the base dish and
            // don't need their own photo — only set/replace/remove one when
            // this row actually says to, matching the images uploader's
            // upload-or-remove-or-leave-alone behavior.
            if ($request->boolean("variants.{$index}.remove_image") && $variant?->image_path) {
                Storage::disk('public')->delete($variant->image_path);
                $attributes['image_path'] = null;
            } elseif ($request->hasFile("variants.{$index}.image")) {
                if ($variant?->image_path) {
                    Storage::disk('public')->delete($variant->image_path);
                }
                $attributes['image_path'] = $request->file("variants.{$index}.image")->store('menu-items', 'public');
            }

            if ($variant) {
                $variant->update($attributes);
            } else {
                $variant = $menuItem->variants()->create($attributes);
            }

            $keptIds[] = $variant->id;
        }

        foreach ($menuItem->variants()->whereNotIn('id', $keptIds)->get() as $removedVariant) {
            if ($removedVariant->image_path) {
                Storage::disk('public')->delete($removedVariant->image_path);
            }
        }
        $menuItem->variants()->whereNotIn('id', $keptIds)->delete();

        if ($menuItem->variants()->exists() && ! $menuItem->variants()->where('is_default', true)->exists()) {
            $menuItem->variants()->orderBy('sort_order')->first()?->update(['is_default' => true]);
        }
    }

    protected function storeUploadedImages(Request $request, MenuItem $menuItem): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $nextSortOrder = (int) $menuItem->images()->max('sort_order') + 1;
        $hasPrimary = $menuItem->images()->where('is_primary', true)->exists();

        foreach ($request->file('images') as $index => $file) {
            $menuItem->images()->create([
                'path' => $file->store('menu-items', 'public'),
                'sort_order' => $nextSortOrder + $index,
                'is_primary' => ! $hasPrimary && $index === 0,
            ]);
        }
    }
}
