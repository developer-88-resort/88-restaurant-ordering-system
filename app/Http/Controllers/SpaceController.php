<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\SpaceStatus;
use App\Http\Requests\StoreBulkSpacesRequest;
use App\Http\Requests\StoreSpaceRequest;
use App\Http\Requests\UpdateSpaceRequest;
use App\Models\Area;
use App\Models\Space;
use App\Models\SpaceCategory;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class SpaceController extends Controller
{
    public function index(Request $request): View
    {
        $areas = Area::with([
            'categories' => fn ($query) => $query->orderBy('sort_order')->orderBy('name'),
            'categories.spaces' => fn ($query) => $query->orderBy('sort_order')->orderBy('name'),
        ])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $activeAreaId = $request->integer('area') ?: $areas->first()?->id;

        return view('spaces.index', ['areas' => $areas, 'activeAreaId' => $activeAreaId]);
    }

    public function create(Request $request): View
    {
        $category = SpaceCategory::with('area')->findOrFail($request->integer('category_id'));

        return view('spaces.create', ['category' => $category]);
    }

    public function store(StoreSpaceRequest $request): RedirectResponse
    {
        $category = SpaceCategory::findOrFail($request->integer('category_id'));
        $name = $request->string('name')->toString();

        Space::create([
            ...$request->validated(),
            'area_id' => $category->area_id,
            'sort_order' => preg_match('/(\d+)$/', $name, $m) ? (int) $m[1] : 0,
        ]);

        return redirect()->route('spaces.index', ['area' => $category->area_id])->with('status', 'Space created successfully.');
    }

    public function storeBulk(StoreBulkSpacesRequest $request): RedirectResponse
    {
        $category = SpaceCategory::findOrFail($request->integer('category_id'));
        $prefix = $request->string('prefix')->trim()->toString();
        $start = $request->integer('start');
        $count = $request->integer('count');

        $created = 0;
        $skipped = 0;

        for ($n = $start; $n < $start + $count; $n++) {
            $name = "{$prefix} {$n}";

            $space = Space::firstOrCreate(
                ['category_id' => $category->id, 'name' => $name],
                ['area_id' => $category->area_id, 'sort_order' => $n]
            );

            $space->wasRecentlyCreated ? $created++ : $skipped++;
        }

        $message = "{$created} space(s) created.";
        if ($skipped > 0) {
            $message .= " {$skipped} already existed and were skipped.";
        }

        return redirect()->route('spaces.index', ['area' => $category->area_id])->with('status', $message);
    }

    public function edit(Space $space): View
    {
        $space->load(['category', 'sharedTables']);

        $availableSpaces = Space::where('area_id', $space->area_id)
            ->where('id', '!=', $space->id)
            ->where(function ($query) use ($space) {
                $query->where('status', SpaceStatus::Available)
                    ->orWhereIn('id', $space->sharedTables->pluck('id'));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('spaces.edit', ['space' => $space, 'availableSpaces' => $availableSpaces]);
    }

    public function update(UpdateSpaceRequest $request, Space $space): RedirectResponse
    {
        $space->update($request->safe()->except(['shared_space_ids', 'status']));
        $space->syncSharedTables($request->input('shared_space_ids', []));
        $space->setStatusWithSharedTables(SpaceStatus::from($request->string('status')->toString()));

        return redirect()->route('spaces.index', ['area' => $space->area_id])->with('status', 'Space updated successfully.');
    }

    public function updateStatus(Request $request, Space $space): RedirectResponse
    {
        $request->validate([
            'status' => ['required', new Enum(SpaceStatus::class)],
        ]);

        $space->setStatusWithSharedTables(SpaceStatus::from($request->string('status')->toString()));

        return redirect()->back()
            ->with('status', "\"{$space->name}\" is now {$space->status->label()}.");
    }

    public function destroy(Space $space): RedirectResponse
    {
        $hasActiveOrder = $space->orders()
            ->whereNotIn('status', [OrderStatus::Completed, OrderStatus::Cancelled])
            ->exists();

        if ($hasActiveOrder) {
            return redirect()->route('spaces.index', ['area' => $space->area_id])
                ->with('error', "\"{$space->name}\" has an active order and can't be deleted.");
        }

        $areaId = $space->area_id;
        $space->delete();

        return redirect()->route('spaces.index', ['area' => $areaId])->with('status', 'Space deleted successfully.');
    }

    public function print(Space $space): View
    {
        return view('spaces.print', ['space' => $space]);
    }

    public function qrCode(Request $request, Space $space): Response
    {
        $result = (new Builder(
            writer: new SvgWriter(),
            data: route('customer.spaces.show', $space->qr_token),
            size: 300,
            margin: 10,
        ))->build();

        $headers = ['Content-Type' => $result->getMimeType()];

        if ($request->boolean('download')) {
            $headers['Content-Disposition'] = 'attachment; filename="space-'.$space->code.'-qr.svg"';
        }

        return response($result->getString(), 200, $headers);
    }
}
