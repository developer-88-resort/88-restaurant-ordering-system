<x-app-layout>
    @php
        $canManageSpaces = in_array(Auth::user()->role, [\App\Enums\UserRole::Superadmin, \App\Enums\UserRole::Admin], true);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Spaces') }}
            </h2>
            @if ($canManageSpaces)
                <a href="{{ route('areas.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                    {{ __('Manage Areas') }}
                </a>
            @endif
        </div>
    </x-slot>

    @if ($areas->isEmpty())
        <x-empty-state
            :title="__('No areas yet')"
            :description="__('Add an area (e.g. Cottages, Dining Area, Rooms) to start organizing spaces.')"
            :actionLabel="$canManageSpaces ? __('New Area') : null"
            :actionHref="$canManageSpaces ? route('areas.create') : null"
        />
    @else
        <div
            x-data="{
                activeArea: '{{ $activeAreaId ?? $areas->first()->id }}',
                viewMode: localStorage.getItem('spacesViewMode') ?? 'floor',
                toasts: [],
                statusMeta: {
                    available: { label: @js(__('Available')), accent: 'bg-green-500' },
                    occupied: { label: @js(__('Occupied')), accent: 'bg-red-500' },
                    reserved: { label: @js(__('Reserved')), accent: 'bg-orange-500' },
                    maintenance: { label: @js(__('Maintenance')), accent: 'bg-yellow-500' },
                    disabled: { label: @js(__('Disabled')), accent: 'bg-blue-500' },
                },
                pushToast(message) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, message });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 6000);
                },
                applyStatus(spaceIds, status) {
                    const meta = this.statusMeta[status];
                    if (!meta) return;
                    spaceIds.forEach((id) => {
                        const select = document.getElementById('space-status-select-' + id);
                        const accent = document.getElementById('space-status-accent-' + id);
                        if (select) select.value = status;
                        if (accent) accent.className = accent.className.replace(/bg-\S+-500/, meta.accent);
                    });
                },
            }"
            x-init="
                $watch('viewMode', value => localStorage.setItem('spacesViewMode', value));
                Echo.private('spaces').listen('.SpaceOccupancyChanged', (e) => { pushToast(e.message); applyStatus(e.space_ids, e.status); });
                turboCleanup(() => Echo.leave('spaces'));
            "
        >
            {{-- Live occupancy toasts --}}
            <div class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-4 z-[60] flex flex-col gap-3 sm:w-96">
                <template x-for="toast in toasts" :key="toast.id">
                    <div
                        x-transition:enter="transition ease-out duration-[400ms]"
                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-300"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="flex items-start gap-3 rounded-xl border border-[#E5DDD0] bg-white pl-4 pr-3 py-3.5 shadow-xl"
                    >
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#F3E1DC]">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="#8A3330" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                            </svg>
                        </div>
                        <p class="flex-1 pt-1 text-sm font-medium text-gray-800" x-text="toast.message"></p>
                    </div>
                </template>
            </div>

            {{-- Area filter pills + view toggle --}}
            <div class="flex flex-wrap items-center justify-between gap-3 mb-8">
                <div class="flex flex-wrap gap-2">
                    @foreach ($areas as $area)
                        <button type="button" @click="activeArea = '{{ $area->id }}'"
                                :class="activeArea === '{{ $area->id }}' ? 'bg-[#8A3330] text-white border-[#8A3330]' : 'bg-white text-gray-600 border-[#D9CCBA] hover:border-[#8A3330]'"
                                class="px-5 py-2 text-sm font-semibold rounded-full border transition whitespace-nowrap">
                            {{ $area->name }}
                        </button>
                    @endforeach
                </div>

                <span class="inline-flex rounded-full border border-[#E5DDD0] bg-white p-0.5">
                    <button type="button" @click="viewMode = 'floor'"
                            :class="viewMode === 'floor' ? 'bg-[#8A3330] text-white' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition">
                        {{ __('Floor Plan') }}
                    </button>
                    <button type="button" @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-[#8A3330] text-white' : 'text-gray-500 hover:text-gray-700'"
                            class="px-3 py-1.5 rounded-full text-xs font-semibold transition">
                        {{ __('List') }}
                    </button>
                </span>
            </div>

            {{-- Per-area sections --}}
            @foreach ($areas as $area)
                @php $defaultCategory = $area->categories->first(); @endphp
                <div x-show="activeArea === '{{ $area->id }}'">
                    @php
                        $newSpaceUrl = $defaultCategory
                            ? route('spaces.create', ['category_id' => $defaultCategory->id])
                            : route('spaces.create', ['area_id' => $area->id]);
                    @endphp

                    @if ($canManageSpaces)
                        <div class="flex items-center justify-end mb-5">
                            <a href="{{ $newSpaceUrl }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                                + {{ __('New Space') }}
                            </a>
                        </div>
                    @endif

                    @php $allSpaces = $area->categories->flatMap->spaces; @endphp

                    @if ($allSpaces->isEmpty())
                        <x-empty-state
                            :title="__('No spaces yet')"
                            :description="__('Add individual spaces (e.g. Cottage Table 1) so customers can be assigned to them.')"
                            :actionLabel="$canManageSpaces ? __('New Space') : null"
                            :actionHref="$canManageSpaces ? $newSpaceUrl : null"
                        />
                    @else
                        {{-- Floor Plan view --}}
                        <div x-show="viewMode === 'floor'" x-cloak>
                            @php
                                // Fallback grid position (arranged by sort_order within
                                // category) for any space that hasn't been dragged into
                                // place yet — never persisted, just a starting layout.
                                $cols = 10; $gapX = 150; $gapY = 150; $marginX = 90; $marginY = 80;
                                $fallbackPositions = [];
                                $rowOffset = 0;
                                foreach ($area->categories as $category) {
                                    $categorySpaces = $category->spaces;
                                    foreach ($categorySpaces as $i => $s) {
                                        $fallbackPositions[$s->id] = [
                                            'x' => $marginX + ($i % $cols) * $gapX,
                                            'y' => $marginY + (intdiv($i, $cols) + $rowOffset) * $gapY,
                                        ];
                                    }
                                    $rowOffset += intdiv(max(0, $categorySpaces->count() - 1), $cols) + 1;
                                }
                            @endphp
                            <div
                                x-data="{
                                    canManageSpaces: @js($canManageSpaces),
                                    areaId: {{ $area->id }},
                                    arrangeMode: false,
                                    activeTool: 'select',
                                    selectedId: null,
                                    selectedWallId: null,
                                    selectedObjectId: null,
                                    wallDrawStart: null,
                                    wallPreview: null,
                                    stage: null,
                                    layer: null,
                                    transformer: null,
                                    resizeObserver: null,
                                    hasRendered: false,
                                    nodesById: {},
                                    spaces: @js($allSpaces->map(fn ($s) => [
                                        'id' => $s->id,
                                        'name' => $s->name,
                                        'capacity' => $s->capacity,
                                        'shape' => $s->shape,
                                        'color' => $s->status->hexColor(),
                                        'width' => $s->width ?? $s->defaultSize()['w'],
                                        'height' => $s->height ?? $s->defaultSize()['h'],
                                        'rotation' => $s->rotation,
                                        'x' => $s->position_x !== null ? (float) $s->position_x : $fallbackPositions[$s->id]['x'],
                                        'y' => $s->position_y !== null ? (float) $s->position_y : $fallbackPositions[$s->id]['y'],
                                    ])->values()),
                                    walls: @js($area->floorPlanWalls->map(fn ($w) => [
                                        'id' => $w->id,
                                        'x1' => (float) $w->x1, 'y1' => (float) $w->y1,
                                        'x2' => (float) $w->x2, 'y2' => (float) $w->y2,
                                        'thickness' => $w->thickness,
                                    ])->values()),
                                    objects: @js($area->floorPlanObjects->map(fn ($o) => [
                                        'id' => $o->id,
                                        'type' => $o->object_type,
                                        'x' => (float) $o->x, 'y' => (float) $o->y,
                                        'rotation' => $o->rotation,
                                        'label' => $o->label,
                                    ])->values()),
                                    chairPositions(space) {
                                        const count = space.capacity || 0;
                                        const chairs = [];
                                        if (count <= 0) return chairs;
                                        if (space.shape === 'circle') {
                                            const r = (space.width / 2) + 14;
                                            for (let i = 0; i < count; i++) {
                                                const angle = (i / count) * Math.PI * 2;
                                                chairs.push({
                                                    x: Math.cos(angle) * r,
                                                    y: Math.sin(angle) * r,
                                                    // Face each chair back outward, radially,
                                                    // away from the table centre.
                                                    rotate: (angle * 180 / Math.PI) + 90,
                                                });
                                            }
                                            return chairs;
                                        }
                                        // Rectangle / long_table: split seats across the two long
                                        // (top/bottom) edges — the extra chair on an odd count
                                        // goes to the top edge. Top-edge chairs keep the icon's
                                        // default (back-up) orientation; bottom-edge chairs flip
                                        // 180 degrees so their backs face away from the table too.
                                        const top = Math.ceil(count / 2), bottom = count - top;
                                        const halfW = space.width / 2, halfH = (space.height / 2) + 14;
                                        const spread = (n, y, rotate) => {
                                            for (let i = 0; i < n; i++) {
                                                const x = n === 1 ? 0 : -halfW + ((i + 0.5) * (space.width / n));
                                                chairs.push({ x, y, rotate });
                                            }
                                        };
                                        spread(top, -halfH, 0);
                                        spread(bottom, halfH, 180);
                                        return chairs;
                                    },
                                    snap(value) {
                                        return Math.round(value / 10) * 10;
                                    },
                                    clampSize(value) {
                                        return Math.min(800, Math.max(20, value));
                                    },
                                    saveLayout(space) {
                                        fetch(`{{ url('spaces') }}/${space.id}/layout`, {
                                            method: 'PATCH',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json',
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                position_x: space.x, position_y: space.y,
                                                width: space.width, height: space.height,
                                                rotation: space.rotation,
                                            }),
                                        });
                                    },
                                    apiFetch(url, method, body) {
                                        return fetch(url, {
                                            method,
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                'Accept': 'application/json',
                                                'Content-Type': 'application/json',
                                            },
                                            body: body ? JSON.stringify(body) : undefined,
                                        });
                                    },
                                    createWall(x1, y1, x2, y2) {
                                        this.apiFetch(`{{ url('areas') }}/${this.areaId}/floor-plan-walls`, 'POST', { x1, y1, x2, y2 })
                                            .then((r) => r.json())
                                            .then((data) => {
                                                this.walls.push({ id: data.id, x1, y1, x2, y2, thickness: 14 });
                                                this.renderFloorPlan();
                                            });
                                    },
                                    updateWallPosition(wall) {
                                        this.apiFetch(`{{ url('floor-plan-walls') }}/${wall.id}`, 'PATCH', {
                                            x1: wall.x1, y1: wall.y1, x2: wall.x2, y2: wall.y2,
                                        });
                                    },
                                    deleteSelectedWall() {
                                        if (!this.selectedWallId) return;
                                        const id = this.selectedWallId;
                                        this.apiFetch(`{{ url('floor-plan-walls') }}/${id}`, 'DELETE').then(() => {
                                            this.walls = this.walls.filter((w) => w.id !== id);
                                            this.selectedWallId = null;
                                            this.renderFloorPlan();
                                        });
                                    },
                                    createObject(type, x, y, label = null) {
                                        this.apiFetch(`{{ url('areas') }}/${this.areaId}/floor-plan-objects`, 'POST', {
                                            object_type: type, x, y, rotation: 0, label,
                                        })
                                            .then((r) => r.json())
                                            .then((data) => {
                                                this.objects.push({ id: data.id, type, x, y, rotation: 0, label });
                                                this.renderFloorPlan();
                                            });
                                    },
                                    updateObject(obj) {
                                        this.apiFetch(`{{ url('floor-plan-objects') }}/${obj.id}`, 'PATCH', {
                                            x: obj.x, y: obj.y, rotation: obj.rotation,
                                        });
                                    },
                                    deleteSelectedObject() {
                                        if (!this.selectedObjectId) return;
                                        const id = this.selectedObjectId;
                                        this.apiFetch(`{{ url('floor-plan-objects') }}/${id}`, 'DELETE').then(() => {
                                            this.objects = this.objects.filter((o) => o.id !== id);
                                            this.selectedObjectId = null;
                                            this.renderFloorPlan();
                                        });
                                    },
                                    rotateSelectedObject() {
                                        const obj = this.objects.find((o) => o.id === this.selectedObjectId);
                                        if (!obj) return;
                                        obj.rotation = (obj.rotation + 90) % 360;
                                        this.updateObject(obj);
                                        this.renderFloorPlan();
                                    },
                                    startObjectTool(type) {
                                        if (!type) return;
                                        this.activeTool = 'object:' + type;
                                    },
                                    buildChairNode(chair) {
                                        // Flat wood-tone chair silhouette: seat, a darker
                                        // backrest rail on the outward-facing edge, and 4 small
                                        // corner legs peeking out — still flat colors, no
                                        // shadow/gradient, just a more recognizable chair shape.
                                        const group = new Konva.Group({ x: chair.x, y: chair.y, rotation: chair.rotate });
                                        [[-8, -8], [8, -8], [-8, 8], [8, 8]].forEach(([lx, ly]) => {
                                            group.add(new Konva.Rect({ x: lx - 1.5, y: ly - 1.5, width: 3, height: 3, fill: '#5C3F26' }));
                                        });
                                        group.add(new Konva.Rect({ x: -7, y: -7, width: 14, height: 14, cornerRadius: 3, fill: '#A9784E' }));
                                        group.add(new Konva.Rect({ x: -7, y: -7, width: 14, height: 5, cornerRadius: 2, fill: '#7A5636' }));
                                        return group;
                                    },
                                    buildWallNode(wall) {
                                        const isSelected = this.selectedWallId === wall.id;
                                        const line = new Konva.Line({
                                            points: [wall.x1, wall.y1, wall.x2, wall.y2],
                                            stroke: isSelected ? '#8A3330' : '#3D3226',
                                            strokeWidth: wall.thickness,
                                            lineCap: 'square',
                                            draggable: this.arrangeMode,
                                        });

                                        if (this.arrangeMode) {
                                            line.on('click tap', (e) => {
                                                if (this.activeTool !== 'select') return;
                                                e.cancelBubble = true;
                                                this.selectedId = null;
                                                this.selectedObjectId = null;
                                                this.selectedWallId = wall.id;
                                                this.renderFloorPlan();
                                            });
                                            // Dragging a Konva.Line moves its x/y offset, not the
                                            // points themselves — fold that offset into the actual
                                            // endpoint coordinates, then reset x/y back to 0.
                                            line.on('dragend', () => {
                                                const dx = line.x(), dy = line.y();
                                                wall.x1 = Math.round(wall.x1 + dx);
                                                wall.y1 = Math.round(wall.y1 + dy);
                                                wall.x2 = Math.round(wall.x2 + dx);
                                                wall.y2 = Math.round(wall.y2 + dy);
                                                line.position({ x: 0, y: 0 });
                                                line.points([wall.x1, wall.y1, wall.x2, wall.y2]);
                                                this.updateWallPosition(wall);
                                            });
                                        }

                                        return line;
                                    },
                                    // Small, flat, hand-drawn room-decoration icons — kept to
                                    // simple shape primitives (rects/circles/lines) in flat
                                    // colors, consistent with the chairs, not photorealistic.
                                    buildObjectNode(obj) {
                                        const isSelected = this.selectedObjectId === obj.id;
                                        const group = new Konva.Group({
                                            x: obj.x, y: obj.y, rotation: obj.rotation,
                                            draggable: this.arrangeMode,
                                        });
                                        const label = (text, dy) => group.add(new Konva.Text({
                                            text, x: -60, y: dy, width: 120, align: 'center',
                                            fontSize: 12, fontStyle: '700', fill: '#3F2D1C',
                                        }));

                                        if (isSelected) {
                                            group.add(new Konva.Rect({
                                                x: -80, y: -50, width: 160, height: 100, cornerRadius: 8,
                                                stroke: '#8A3330', strokeWidth: 2, dash: [6, 4], fill: 'transparent',
                                            }));
                                        }

                                        if (obj.type === 'entrance') {
                                            group.add(new Konva.Line({ points: [-22, -30, -2, 0], stroke: '#3D3226', strokeWidth: 3 }));
                                            group.add(new Konva.Line({ points: [22, -30, 2, 0], stroke: '#3D3226', strokeWidth: 3 }));
                                            group.add(new Konva.Line({ points: [0, 6, -8, 20, 8, 20], closed: true, fill: '#3D3226' }));
                                            label(obj.label || 'ENTRANCE', 28);
                                        } else if (obj.type === 'kitchen_counter') {
                                            group.add(new Konva.Rect({ x: -70, y: -30, width: 140, height: 60, cornerRadius: 4, fill: '#8A8580', stroke: '#5C5852', strokeWidth: 2 }));
                                            group.add(new Konva.Circle({ x: -30, y: 0, radius: 12, fill: '#3D3226' }));
                                            group.add(new Konva.Circle({ x: 30, y: 0, radius: 12, fill: '#3D3226' }));
                                            label(obj.label || 'KITCHEN', 42);
                                        } else if (obj.type === 'sink') {
                                            group.add(new Konva.Rect({ x: -30, y: -18, width: 60, height: 36, cornerRadius: 6, fill: '#C9D3D6', stroke: '#8A9599', strokeWidth: 2 }));
                                            group.add(new Konva.Circle({ x: -15, y: 0, radius: 9, fill: '#8A9599' }));
                                            group.add(new Konva.Circle({ x: 15, y: 0, radius: 9, fill: '#8A9599' }));
                                        } else if (obj.type === 'sofa') {
                                            group.add(new Konva.Rect({ x: -60, y: -22, width: 120, height: 44, cornerRadius: 10, fill: '#8FA083' }));
                                            group.add(new Konva.Rect({ x: -60, y: -22, width: 120, height: 14, cornerRadius: 8, fill: '#6F8062' }));
                                            group.add(new Konva.Line({ points: [0, -22, 0, 22], stroke: '#6F8062', strokeWidth: 2 }));
                                        } else if (obj.type === 'plant') {
                                            group.add(new Konva.Rect({ x: -10, y: 4, width: 20, height: 16, cornerRadius: 3, fill: '#8A6D42' }));
                                            group.add(new Konva.Circle({ x: 0, y: -6, radius: 16, fill: '#5C8352' }));
                                            group.add(new Konva.Circle({ x: -10, y: 2, radius: 11, fill: '#6F9A62' }));
                                            group.add(new Konva.Circle({ x: 10, y: 2, radius: 11, fill: '#6F9A62' }));
                                        } else if (obj.type === 'rug') {
                                            group.add(new Konva.Rect({ x: -40, y: -90, width: 80, height: 180, cornerRadius: 24, fill: '#7C8F6E', opacity: 0.55, stroke: '#5E6F52', strokeWidth: 2 }));
                                        } else if (obj.type === 'cashier_desk') {
                                            group.add(new Konva.Rect({ x: -55, y: -20, width: 110, height: 40, cornerRadius: 4, fill: '#8A6D42' }));
                                            group.add(new Konva.Rect({ x: -30, y: -30, width: 22, height: 14, cornerRadius: 2, fill: '#3D3226' }));
                                            group.add(new Konva.Rect({ x: 8, y: -30, width: 22, height: 14, cornerRadius: 2, fill: '#3D3226' }));
                                            label(obj.label || 'CASHIER', 34);
                                        } else if (obj.type === 'restroom') {
                                            group.add(new Konva.Circle({ x: 0, y: 0, radius: 16, fill: '#C9D3D6', stroke: '#8A9599', strokeWidth: 2 }));
                                            group.add(new Konva.Rect({ x: -10, y: -28, width: 20, height: 10, cornerRadius: 2, fill: '#C9D3D6', stroke: '#8A9599', strokeWidth: 2 }));
                                            label(obj.label || 'RESTROOM', 34);
                                        } else if (obj.type === 'text') {
                                            label(obj.label || '', 0);
                                        }

                                        if (this.arrangeMode) {
                                            group.on('click tap', (e) => {
                                                if (this.activeTool !== 'select') return;
                                                e.cancelBubble = true;
                                                this.selectedId = null;
                                                this.selectedWallId = null;
                                                this.selectedObjectId = obj.id;
                                                this.renderFloorPlan();
                                            });
                                            group.on('dragend', () => {
                                                obj.x = Math.round(group.x());
                                                obj.y = Math.round(group.y());
                                                this.updateObject(obj);
                                            });
                                        }

                                        return group;
                                    },
                                    buildGroup(space) {
                                        const isSelected = this.selectedId === space.id;
                                        const group = new Konva.Group({
                                            x: space.x,
                                            y: space.y,
                                            rotation: space.rotation,
                                            draggable: this.arrangeMode,
                                        });

                                        this.chairPositions(space).forEach((chair) => group.add(this.buildChairNode(chair)));

                                        const shapeProps = {
                                            fill: '#D9CCBA',
                                            stroke: isSelected ? '#8A3330' : space.color,
                                            strokeWidth: isSelected ? 4 : 3,
                                        };
                                        const shapeNode = space.shape === 'circle'
                                            ? new Konva.Circle({ ...shapeProps, radius: space.width / 2 })
                                            : new Konva.Rect({ ...shapeProps, x: -space.width / 2, y: -space.height / 2, width: space.width, height: space.height, cornerRadius: 8 });
                                        group.add(shapeNode);

                                        group.add(new Konva.Text({
                                            text: space.name, x: -space.width / 2, y: -18, width: space.width,
                                            align: 'center', fontSize: 18, fontStyle: '600', fill: '#3F2D1C',
                                        }));

                                        if (space.capacity) {
                                            group.add(new Konva.Text({
                                                text: '🪑 ' + space.capacity, x: -space.width / 2, y: 6, width: space.width,
                                                align: 'center', fontSize: 13, fill: '#3F2D1C', opacity: 0.8,
                                            }));
                                        }

                                        if (this.arrangeMode) {
                                            group.on('click tap', (e) => {
                                                e.cancelBubble = true;
                                                this.selectedId = space.id;
                                                this.renderFloorPlan();
                                            });
                                            group.on('dragend', () => {
                                                space.x = this.snap(group.x());
                                                space.y = this.snap(group.y());
                                                group.position({ x: space.x, y: space.y });
                                                this.saveLayout(space);
                                            });
                                            // Konva.Transformer scales the node (scaleX/scaleY)
                                            // rather than changing width/height directly — commit
                                            // the scale into real width/height here, then reset
                                            // scale to 1 and rebuild so chairs/text re-lay-out
                                            // correctly against the new size.
                                            group.on('transformend', () => {
                                                const scaleX = group.scaleX(), scaleY = group.scaleY();
                                                if (scaleX !== 1 || scaleY !== 1) {
                                                    space.width = this.clampSize(Math.round(space.width * scaleX));
                                                    space.height = this.clampSize(Math.round(space.height * scaleY));
                                                    group.scale({ x: 1, y: 1 });
                                                }
                                                space.rotation = Math.round(((group.rotation() % 360) + 360) % 360);
                                                space.x = Math.round(group.x());
                                                space.y = Math.round(group.y());
                                                this.saveLayout(space);
                                                this.renderFloorPlan();
                                            });
                                        }

                                        this.nodesById[space.id] = group;
                                        return group;
                                    },
                                    renderFloorPlan() {
                                        this.layer.destroyChildren();
                                        this.nodesById = {};

                                        // Rugs/objects/walls first so tables and chairs always
                                        // render on top of them, matching normal floor-plan
                                        // stacking (floor coverings and room fixtures behind
                                        // furniture that's placed on top of them).
                                        this.objects.filter((o) => o.type === 'rug').forEach((o) => this.layer.add(this.buildObjectNode(o)));
                                        this.walls.forEach((w) => this.layer.add(this.buildWallNode(w)));
                                        this.objects.filter((o) => o.type !== 'rug').forEach((o) => this.layer.add(this.buildObjectNode(o)));

                                        this.spaces.forEach((space) => {
                                            this.layer.add(this.buildGroup(space));
                                        });

                                        if (this.arrangeMode && this.selectedId && this.nodesById[this.selectedId]) {
                                            const space = this.spaces.find((s) => s.id === this.selectedId);
                                            this.transformer = new Konva.Transformer({
                                                nodes: [this.nodesById[this.selectedId]],
                                                enabledAnchors: ['bottom-right'],
                                                rotateAnchorOffset: 28,
                                                centeredScaling: true,
                                                keepRatio: space.shape === 'circle',
                                                anchorFill: '#8A3330',
                                                anchorStroke: '#8A3330',
                                                borderStroke: '#8A3330',
                                                boundBoxFunc: (oldBox, newBox) => {
                                                    if (newBox.width < 20 || newBox.height < 20 || newBox.width > 800 || newBox.height > 800) {
                                                        return oldBox;
                                                    }
                                                    return newBox;
                                                },
                                            });
                                            this.layer.add(this.transformer);
                                        }

                                        this.layer.batchDraw();
                                    },
                                    // Konva's Stage has no SVG-viewBox equivalent — it deals in
                                    // real pixels, so the fixed 1600x1000 logical canvas has to be
                                    // manually scaled to fit whatever the container's actual pixel
                                    // size is, and re-fit whenever that size changes (including a
                                    // hidden area tab going from 0x0 to its real size when it
                                    // becomes active — ResizeObserver fires for that transition
                                    // too, so no extra tab-switch handling is needed here). A
                                    // hidden (inactive) area tab's container is 0x0 at page load,
                                    // and some Konva shapes (Line, used by walls) throw if drawn
                                    // into a zero-sized canvas — so skip sizing/rendering entirely
                                    // until the container actually has real dimensions, which
                                    // happens once its tab becomes active.
                                    fitStage() {
                                        const el = this.$refs.canvasContainer;
                                        if (el.clientWidth <= 0) return;
                                        const scale = el.clientWidth / 1600;
                                        this.stage.width(el.clientWidth);
                                        this.stage.height(el.clientWidth * (1000 / 1600));
                                        this.stage.scale({ x: scale, y: scale });
                                        this.stage.batchDraw();
                                        if (!this.hasRendered) {
                                            this.hasRendered = true;
                                            this.renderFloorPlan();
                                        }
                                    },
                                    initStage() {
                                        this.stage = new Konva.Stage({ container: this.$refs.canvasContainer });
                                        this.layer = new Konva.Layer();
                                        this.stage.add(this.layer);

                                        this.stage.on('click tap', (e) => {
                                            if (this.activeTool && this.activeTool.startsWith('object:')) {
                                                const pos = this.stage.getRelativePointerPosition();
                                                const type = this.activeTool.slice('object:'.length);
                                                if (type === 'text') {
                                                    window.Swal.fire({
                                                        input: 'text',
                                                        inputLabel: '{{ __('Label') }}',
                                                        showCancelButton: true,
                                                        confirmButtonColor: '#8A3330',
                                                    }).then((result) => {
                                                        if (result.isConfirmed && result.value) {
                                                            this.createObject('text', Math.round(pos.x), Math.round(pos.y), result.value);
                                                        }
                                                        this.activeTool = 'select';
                                                    });
                                                } else {
                                                    this.createObject(type, Math.round(pos.x), Math.round(pos.y));
                                                    this.activeTool = 'select';
                                                }
                                                return;
                                            }
                                            // Checking the class name (not `e.target === this.stage`)
                                            // matters here: `this.stage` read inside an Alpine method
                                            // is Alpine's reactivity Proxy wrapping the real Konva
                                            // Stage, while Konva's own event system hands back the
                                            // raw, unwrapped instance — those are never `===` to each
                                            // other even when they refer to the same stage.
                                            if (e.target.getClassName() === 'Stage') {
                                                this.selectedId = null;
                                                this.selectedWallId = null;
                                                this.selectedObjectId = null;
                                                this.renderFloorPlan();
                                            }
                                        });

                                        // Wall tool: mousedown starts a segment on empty canvas,
                                        // mousemove previews it, mouseup commits it (unless the
                                        // drag was too short to be a deliberate wall).
                                        this.stage.on('mousedown touchstart', (e) => {
                                            if (this.activeTool !== 'wall' || e.target.getClassName() !== 'Stage') return;
                                            this.wallDrawStart = this.stage.getRelativePointerPosition();
                                            this.wallPreview = new Konva.Line({
                                                points: [this.wallDrawStart.x, this.wallDrawStart.y, this.wallDrawStart.x, this.wallDrawStart.y],
                                                stroke: '#8A3330', strokeWidth: 14, dash: [10, 6],
                                            });
                                            this.layer.add(this.wallPreview);
                                        });
                                        this.stage.on('mousemove touchmove', () => {
                                            if (!this.wallDrawStart) return;
                                            const pos = this.stage.getRelativePointerPosition();
                                            this.wallPreview.points([this.wallDrawStart.x, this.wallDrawStart.y, pos.x, pos.y]);
                                            this.layer.batchDraw();
                                        });
                                        this.stage.on('mouseup touchend', () => {
                                            if (!this.wallDrawStart) return;
                                            const pos = this.stage.getRelativePointerPosition();
                                            const start = this.wallDrawStart;
                                            this.wallDrawStart = null;
                                            this.wallPreview.destroy();
                                            this.wallPreview = null;
                                            if (Math.hypot(pos.x - start.x, pos.y - start.y) > 10) {
                                                this.createWall(Math.round(start.x), Math.round(start.y), Math.round(pos.x), Math.round(pos.y));
                                            } else {
                                                this.layer.batchDraw();
                                            }
                                            this.activeTool = 'select';
                                        });

                                        this.resizeObserver = new ResizeObserver(() => this.fitStage());
                                        this.resizeObserver.observe(this.$refs.canvasContainer);
                                        turboCleanup(() => this.resizeObserver.disconnect());

                                        this.fitStage();
                                    },
                                }"
                                x-init="initStage(); $watch('arrangeMode', () => { selectedId = null; selectedWallId = null; selectedObjectId = null; activeTool = 'select'; renderFloorPlan(); })"
                            >
                                @if ($canManageSpaces)
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                        <div class="flex flex-wrap items-center gap-2" x-show="arrangeMode" x-cloak>
                                            <button type="button" @click="activeTool = activeTool === 'wall' ? 'select' : 'wall'"
                                                    :class="activeTool === 'wall' ? 'bg-[#8A3330] text-white border-[#8A3330]' : 'bg-white text-gray-600 border-[#D9CCBA] hover:border-[#8A3330]'"
                                                    class="px-3 py-1.5 text-xs font-semibold rounded-full border transition">
                                                {{ __('+ Wall') }}
                                            </button>
                                            <select @change="startObjectTool($event.target.value); $event.target.value = ''"
                                                    class="text-xs font-semibold rounded-full border border-[#D9CCBA] px-3 py-1.5 bg-white text-gray-600 hover:border-[#8A3330] focus:ring-[#8A3330] focus:border-[#8A3330]">
                                                <option value="">{{ __('+ Object…') }}</option>
                                                <option value="kitchen_counter">{{ __('Kitchen Counter') }}</option>
                                                <option value="sink">{{ __('Sink') }}</option>
                                                <option value="sofa">{{ __('Sofa') }}</option>
                                                <option value="plant">{{ __('Plant') }}</option>
                                                <option value="rug">{{ __('Rug') }}</option>
                                                <option value="cashier_desk">{{ __('Cashier Desk') }}</option>
                                                <option value="restroom">{{ __('Restroom') }}</option>
                                                <option value="entrance">{{ __('Entrance') }}</option>
                                                <option value="text">{{ __('Text Label') }}</option>
                                            </select>
                                            <button type="button" x-show="selectedWallId" @click="deleteSelectedWall()"
                                                    class="px-3 py-1.5 text-xs font-semibold rounded-full border border-red-300 text-red-600 hover:bg-red-50 transition">
                                                {{ __('Delete Wall') }}
                                            </button>
                                            <template x-if="selectedObjectId">
                                                <span class="flex items-center gap-2">
                                                    <button type="button" @click="rotateSelectedObject()"
                                                            class="px-3 py-1.5 text-xs font-semibold rounded-full border border-[#D9CCBA] text-gray-600 hover:border-[#8A3330] transition">
                                                        {{ __('Rotate 90°') }}
                                                    </button>
                                                    <button type="button" @click="deleteSelectedObject()"
                                                            class="px-3 py-1.5 text-xs font-semibold rounded-full border border-red-300 text-red-600 hover:bg-red-50 transition">
                                                        {{ __('Delete Object') }}
                                                    </button>
                                                </span>
                                            </template>
                                        </div>
                                        <button type="button" @click="arrangeMode = !arrangeMode"
                                                :class="arrangeMode ? 'bg-[#8A3330] text-white border-[#8A3330]' : 'bg-white text-gray-600 border-[#D9CCBA] hover:border-[#8A3330]'"
                                                class="px-4 py-1.5 text-xs font-semibold rounded-full border transition ml-auto">
                                            <span x-text="arrangeMode ? '{{ __('Done Arranging') }}' : '{{ __('Arrange Floor Plan') }}'"></span>
                                        </button>
                                    </div>
                                @endif
                                <div class="relative border border-[#E5DDD0] rounded-xl bg-[#FAF6EE] overflow-hidden w-full" style="aspect-ratio: 1600 / 1000;">
                                    <div x-ref="canvasContainer"
                                         :class="(activeTool === 'wall' || (activeTool && activeTool.startsWith('object:'))) ? 'cursor-crosshair' : ''"
                                         class="absolute inset-0 w-full h-full select-none"></div>
                                </div>
                                @if ($canManageSpaces)
                                    <p class="mt-2 text-xs text-gray-400" x-show="arrangeMode" x-cloak>{{ __('Drag a table, wall, or object to reposition it (tables snap to a 10-unit grid). Click a table to resize/rotate it, or use "+ Wall"/"+ Object…" to add new room elements.') }}</p>
                                    <p class="mt-2 text-xs text-gray-400" x-show="!arrangeMode">{{ __('Click "Arrange Floor Plan" to reposition, resize, or rotate tables and room elements.') }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- List view --}}
                        <div x-show="viewMode === 'list'" x-cloak class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach ($allSpaces as $space)
                                <div class="border border-[#E5DDD0] rounded-xl p-4 bg-white hover:border-[#8A3330] transition">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-semibold text-gray-900">{{ $space->name }}</span>
                                        @if ($canManageSpaces)
                                            <a href="{{ route('spaces.print', $space) }}" target="_blank" class="text-xs text-[#8A3330] hover:underline shrink-0">{{ __('QR') }}</a>
                                        @endif
                                    </div>
                                    <form action="{{ route('spaces.update-status', $space) }}" method="POST" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="relative w-full">
                                            <div id="space-status-accent-{{ $space->id }}" class="absolute inset-y-0 left-0 w-full -translate-x-2 rounded-full {{ $space->status->capsuleAccentClass() }}"></div>
                                            <select id="space-status-select-{{ $space->id }}" name="status" onchange="this.form.submit()"
                                                    class="relative w-full text-xs font-bold text-black text-center rounded-full px-3 py-1.5 bg-white border-2 border-gray-900 focus:ring-2 focus:ring-[#8A3330] focus:outline-none">
                                                @foreach (\App\Enums\SpaceStatus::cases() as $status)
                                                    <option value="{{ $status->value }}" @selected($space->status === $status)>{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </form>
                                    @if ($canManageSpaces)
                                        <div class="mt-3 pt-3 border-t border-[#E5DDD0] flex items-center justify-between text-xs">
                                            <a href="{{ route('spaces.edit', $space) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                                            <x-confirm-form
                                                :action="route('spaces.destroy', $space)"
                                                method="DELETE"
                                                :title="__('Delete this space?')"
                                                :message="__('This will permanently remove :name.', ['name' => $space->name])"
                                                :confirm-label="__('Delete')"
                                            >
                                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </x-confirm-form>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
