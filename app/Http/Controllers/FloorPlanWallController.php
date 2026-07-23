<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\FloorPlanWall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FloorPlanWallController extends Controller
{
    public function store(Request $request, Area $area): JsonResponse
    {
        $data = $request->validate([
            'x1' => ['required', 'numeric'],
            'y1' => ['required', 'numeric'],
            'x2' => ['required', 'numeric'],
            'y2' => ['required', 'numeric'],
            'thickness' => ['nullable', 'integer', 'min:4', 'max:40'],
        ]);

        $wall = $area->floorPlanWalls()->create($data);

        return response()->json(['id' => $wall->id]);
    }

    public function update(Request $request, FloorPlanWall $floorPlanWall): JsonResponse
    {
        $data = $request->validate([
            'x1' => ['required', 'numeric'],
            'y1' => ['required', 'numeric'],
            'x2' => ['required', 'numeric'],
            'y2' => ['required', 'numeric'],
        ]);

        $floorPlanWall->update($data);

        return response()->json(['status' => 'ok']);
    }

    public function destroy(FloorPlanWall $floorPlanWall): JsonResponse
    {
        $floorPlanWall->delete();

        return response()->json(['status' => 'ok']);
    }
}
