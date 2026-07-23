<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpaceCategoryRequest;
use App\Http\Requests\UpdateSpaceCategoryRequest;
use App\Models\Area;
use App\Models\SpaceCategory;
use App\Models\SpaceSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SpaceCategoryController extends Controller
{
    public function create(Area $area): View
    {
        return view('space-categories.create', ['area' => $area]);
    }

    public function store(StoreSpaceCategoryRequest $request): RedirectResponse
    {
        $category = SpaceCategory::create([
            ...$request->validated(),
            'is_free' => $request->boolean('is_free'),
        ]);

        return redirect()->route('spaces.index', ['area' => $category->area_id])
            ->with('status', __('Category created successfully.'));
    }

    public function edit(SpaceCategory $spaceCategory): View
    {
        return view('space-categories.edit', ['category' => $spaceCategory]);
    }

    public function update(UpdateSpaceCategoryRequest $request, SpaceCategory $spaceCategory): RedirectResponse
    {
        $spaceCategory->update([
            ...$request->validated(),
            'is_free' => $request->boolean('is_free'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('spaces.index', ['area' => $spaceCategory->area_id])
            ->with('status', __('Category updated successfully.'));
    }

    public function destroy(SpaceCategory $spaceCategory): RedirectResponse
    {
        if ($spaceCategory->spaces()->exists() || $spaceCategory->activeOccupancyCount() > 0) {
            return redirect()->route('spaces.index', ['area' => $spaceCategory->area_id])
                ->with('error', __('":name" still has spaces or active occupancy and can\'t be deleted.', ['name' => $spaceCategory->name]));
        }

        $areaId = $spaceCategory->area_id;
        $spaceCategory->delete();

        return redirect()->route('spaces.index', ['area' => $areaId])
            ->with('status', __('Category deleted successfully.'));
    }

    public function endSession(SpaceCategory $spaceCategory, SpaceSession $spaceSession): RedirectResponse
    {
        abort_unless($spaceSession->category_id === $spaceCategory->id, 404);

        $spaceSession->update(['status' => 'completed', 'ended_at' => now()]);

        return redirect()->back()->with('status', __('Occupancy released for ":name".', ['name' => $spaceCategory->name]));
    }
}
