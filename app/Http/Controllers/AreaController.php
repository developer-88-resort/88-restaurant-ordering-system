<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $areas = Area::withCount(['categories', 'spaces'])->orderBy('sort_order')->orderBy('name')->get();

        return view('areas.index', ['areas' => $areas]);
    }

    public function create(): View
    {
        return view('areas.create', ['nextSortOrder' => Area::max('sort_order') + 1]);
    }

    public function store(StoreAreaRequest $request): RedirectResponse
    {
        Area::create($request->validated());

        return redirect()->route('areas.index')->with('status', __('Area created successfully.'));
    }

    public function edit(Area $area): View
    {
        return view('areas.edit', ['area' => $area]);
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $area->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('areas.index')->with('status', __('Area updated successfully.'));
    }

    public function destroy(Area $area): RedirectResponse
    {
        if ($area->categories()->exists() || $area->spaces()->exists()) {
            return redirect()->route('areas.index')
                ->with('error', __('":name" still has categories or spaces and can\'t be deleted.', ['name' => $area->name]));
        }

        $area->delete();

        return redirect()->route('areas.index')->with('status', __('Area deleted successfully.'));
    }
}
