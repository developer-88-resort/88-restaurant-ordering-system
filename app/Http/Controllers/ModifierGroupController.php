<?php

namespace App\Http\Controllers;

use App\Enums\ModifierSelectionType;
use App\Http\Requests\StoreModifierGroupRequest;
use App\Http\Requests\UpdateModifierGroupRequest;
use App\Models\ModifierGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModifierGroupController extends Controller
{
    public function index(Request $request): View
    {
        $showArchived = $request->boolean('archived');

        $groups = ModifierGroup::withCount('options')
            ->when($showArchived, fn ($query) => $query->onlyTrashed())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('modifier-groups.index', [
            'groups' => $groups,
            'showArchived' => $showArchived,
            'archivedCount' => ModifierGroup::onlyTrashed()->count(),
        ]);
    }

    public function create(): View
    {
        return view('modifier-groups.create', [
            'selectionTypes' => ModifierSelectionType::cases(),
        ]);
    }

    public function store(StoreModifierGroupRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['options']);
        $data['is_required'] = $request->boolean('is_required');
        [$data['min_select'], $data['max_select']] = $this->selectLimits($data['selection_type'], $data['is_required']);

        $group = ModifierGroup::create($data);

        $this->syncOptions($request, $group);

        return redirect()->route('modifier-groups.index')
            ->with('status', __('Modifier group created successfully.'));
    }

    public function edit(ModifierGroup $modifierGroup): View
    {
        return view('modifier-groups.edit', [
            'group' => $modifierGroup->load('options'),
            'selectionTypes' => ModifierSelectionType::cases(),
        ]);
    }

    public function update(UpdateModifierGroupRequest $request, ModifierGroup $modifierGroup): RedirectResponse
    {
        $data = $request->safe()->except(['options']);
        $data['is_required'] = $request->boolean('is_required');
        [$data['min_select'], $data['max_select']] = $this->selectLimits($data['selection_type'], $data['is_required']);

        $modifierGroup->update($data);
        $this->syncOptions($request, $modifierGroup);

        return redirect()->route('modifier-groups.index')
            ->with('status', __('Modifier group updated successfully.'));
    }

    /**
     * Archives (soft-deletes) rather than permanently removing, same
     * reasoning as Menu Items/Categories — reversible, no accidental
     * permanent loss of a reusable group other items still reference.
     */
    public function destroy(ModifierGroup $modifierGroup): RedirectResponse
    {
        $modifierGroup->delete();

        return redirect()->route('modifier-groups.index')
            ->with('status', __('":name" was archived.', ['name' => $modifierGroup->name]));
    }

    public function restore(ModifierGroup $modifierGroup): RedirectResponse
    {
        $modifierGroup->restore();

        return redirect()->route('modifier-groups.index', ['archived' => 1])
            ->with('status', __('":name" was restored.', ['name' => $modifierGroup->name]));
    }

    /**
     * min/max aren't exposed as their own form fields — they're derived
     * from selection type + required, which is all the create/edit form
     * asks for and covers every real case (Rice Options: required single;
     * Add-ons: optional multiple, etc.).
     */
    protected function selectLimits(string $selectionType, bool $isRequired): array
    {
        if ($selectionType === ModifierSelectionType::Single->value) {
            return [$isRequired ? 1 : 0, 1];
        }

        return [$isRequired ? 1 : 0, null];
    }

    /**
     * Same row-diffing approach as MenuItemController::syncVariants(): rows
     * with a matching id are updated, new rows are created, and any
     * existing option not resubmitted gets removed for real — options are
     * a detail of the group, not independently manageable, so unlike
     * variants/categories/items they don't need their own archive/restore.
     */
    protected function syncOptions(Request $request, ModifierGroup $group): void
    {
        $rows = $request->input('options', []);
        $keptIds = [];

        foreach ($rows as $index => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $attributes = [
                'modifier_group_id' => $group->id,
                'name' => $name,
                'price_delta' => $row['price_delta'] ?? 0,
                'sku' => $row['sku'] ?: null,
                'sort_order' => $index,
            ];

            $option = ! empty($row['id']) ? $group->options()->find($row['id']) : null;

            if ($option) {
                $option->update($attributes);
            } else {
                $option = $group->options()->create($attributes);
            }

            $keptIds[] = $option->id;
        }

        $group->options()->whereNotIn('id', $keptIds)->delete();
    }
}
