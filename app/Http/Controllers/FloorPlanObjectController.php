<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\FloorPlanObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FloorPlanObjectController extends Controller
{
    private const TYPES = [
        'entrance', 'kitchen_counter', 'sink', 'sofa', 'plant', 'rug', 'cashier_desk', 'restroom', 'text',
    ];

    public function store(Request $request, Area $area): JsonResponse
    {
        $data = $request->validate([
            'object_type' => ['required', 'in:'.implode(',', self::TYPES)],
            'x' => ['required', 'numeric'],
            'y' => ['required', 'numeric'],
            'rotation' => ['nullable', 'integer', 'min:0', 'max:359'],
            'label' => ['nullable', 'string', 'max:40'],
        ]);

        $object = $area->floorPlanObjects()->create($data);

        return response()->json(['id' => $object->id]);
    }

    public function update(Request $request, FloorPlanObject $floorPlanObject): JsonResponse
    {
        $data = $request->validate([
            'x' => ['required', 'numeric'],
            'y' => ['required', 'numeric'],
            'rotation' => ['required', 'integer', 'min:0', 'max:359'],
        ]);

        $floorPlanObject->update($data);

        return response()->json(['status' => 'ok']);
    }

    public function destroy(FloorPlanObject $floorPlanObject): JsonResponse
    {
        $floorPlanObject->delete();

        return response()->json(['status' => 'ok']);
    }
}
