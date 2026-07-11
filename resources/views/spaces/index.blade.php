<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Spaces') }}
            </h2>
            <a href="{{ route('areas.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                {{ __('Manage Areas') }}
            </a>
        </div>
    </x-slot>

    @if ($areas->isEmpty())
        <x-empty-state
            :title="__('No areas yet')"
            :description="__('Add an area (e.g. Cottages, Dining Area, Rooms) to start organizing spaces.')"
            :actionLabel="__('New Area')"
            :actionHref="route('areas.create')"
        />
    @else
        <div
            x-data="{
                activeArea: '{{ $activeAreaId ?? $areas->first()->id }}',
                toasts: [],
                reloadScheduled: false,
                pushToast(message) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, message });
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 6000);

                    if (!this.reloadScheduled) {
                        this.reloadScheduled = true;
                        setTimeout(() => window.location.reload(), 6500);
                    }
                },
            }"
            x-init="Echo.private('spaces').listen('.SpaceOccupancyChanged', (e) => pushToast(e.message))"
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

            {{-- Area filter pills --}}
            <div class="flex flex-wrap gap-2 mb-8">
                @foreach ($areas as $area)
                    <button type="button" @click="activeArea = '{{ $area->id }}'"
                            :class="activeArea === '{{ $area->id }}' ? 'bg-[#8A3330] text-white border-[#8A3330]' : 'bg-white text-gray-600 border-[#D9CCBA] hover:border-[#8A3330]'"
                            class="px-5 py-2 text-sm font-semibold rounded-full border transition whitespace-nowrap">
                        {{ $area->name }}
                    </button>
                @endforeach
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

                    <div class="flex items-center justify-end mb-5">
                        <a href="{{ $newSpaceUrl }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                            + {{ __('New Space') }}
                        </a>
                    </div>

                    @php $allSpaces = $area->categories->flatMap->spaces; @endphp

                    @if ($allSpaces->isEmpty())
                        <x-empty-state
                            :title="__('No spaces yet')"
                            :description="__('Add individual spaces (e.g. Cottage Table 1) so customers can be assigned to them.')"
                            :actionLabel="__('New Space')"
                            :actionHref="$newSpaceUrl"
                        />
                    @else
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach ($allSpaces as $space)
                                <div class="border border-[#E5DDD0] rounded-xl p-4 bg-white hover:border-[#8A3330] transition">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-semibold text-gray-900">{{ $space->name }}</span>
                                        <a href="{{ route('spaces.print', $space) }}" target="_blank" class="text-xs text-[#8A3330] hover:underline shrink-0">{{ __('QR') }}</a>
                                    </div>
                                    <form action="{{ route('spaces.update-status', $space) }}" method="POST" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="relative w-full">
                                            <div class="absolute inset-y-0 left-0 w-full -translate-x-2 rounded-full {{ $space->status->capsuleAccentClass() }}"></div>
                                            <select name="status" onchange="this.form.submit()"
                                                    class="relative w-full text-xs font-bold text-black text-center rounded-full px-3 py-1.5 bg-white border-2 border-gray-900 focus:ring-2 focus:ring-[#8A3330] focus:outline-none">
                                                @foreach (\App\Enums\SpaceStatus::cases() as $status)
                                                    <option value="{{ $status->value }}" @selected($space->status === $status)>{{ $status->label() }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </form>
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
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
