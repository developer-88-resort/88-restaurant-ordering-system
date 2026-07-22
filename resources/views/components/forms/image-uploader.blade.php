@props(['existingImages' => []])

@php
    $defaultPrimaryId = collect($existingImages)->firstWhere('is_primary', true)['id'] ?? ($existingImages[0]['id'] ?? null);
@endphp

<div
    x-data="{
        primaryId: @js($defaultPrimaryId),
        newFiles: [],
        handleFiles(fileList) {
            this.newFiles = Array.from(fileList).slice(0, 6).map((file) => ({
                name: file.name,
                url: URL.createObjectURL(file),
            }));
        },
    }"
>
    @if (count($existingImages) > 0)
        <p class="text-xs font-medium text-gray-500 mb-2">{{ __('Current images') }}</p>
        <div class="flex flex-wrap gap-4 mb-5">
            @foreach ($existingImages as $image)
                <div class="w-24" x-data="{ removed: false }">
                    <img :src="'{{ $image['url'] }}'" alt=""
                         class="h-24 w-24 rounded-lg object-cover border-2 transition"
                         :class="removed ? 'opacity-30 grayscale border-[#E5DDD0]' : (primaryId === {{ $image['id'] }} ? 'border-[#8A3330]' : 'border-[#E5DDD0]')">
                    <div class="mt-1.5 flex flex-col gap-1 text-[11px]">
                        <label class="flex items-center gap-1.5 cursor-pointer text-gray-600" x-show="!removed">
                            <input type="radio" name="primary_image_id" value="{{ $image['id'] }}" x-model.number="primaryId" class="h-3 w-3 text-[#8A3330] focus:ring-[#8A3330]">
                            {{ __('Primary') }}
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-gray-500 hover:text-red-600">
                            <input type="checkbox" name="remove_images[]" value="{{ $image['id'] }}" x-model="removed" class="h-3 w-3 rounded text-red-600 focus:ring-red-500">
                            {{ __('Remove') }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <label class="flex flex-col items-center justify-center gap-1 border-2 border-dashed border-[#D9CCBA] rounded-xl px-4 py-6 cursor-pointer hover:border-[#8A3330] hover:bg-[#FAF6EE] transition text-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-6 w-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l-3.75 3.75M12 9.75l3.75 3.75M3 17.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18v-.75" />
        </svg>
        <span class="text-sm font-medium text-[#8A3330]">{{ __('Add images') }}</span>
        <span class="text-xs text-gray-400">{{ __('Up to 6 photos, 2MB each') }}</span>
        <input type="file" name="images[]" multiple accept="image/*" class="sr-only" @change="handleFiles($event.target.files)">
    </label>

    <div class="mt-3 flex flex-wrap gap-3" x-show="newFiles.length > 0" x-cloak>
        <template x-for="file in newFiles" :key="file.url">
            <img :src="file.url" alt="" class="h-24 w-24 rounded-lg object-cover border border-[#E5DDD0]">
        </template>
    </div>

    <x-input-error :messages="$errors->get('images')" class="mt-2" />
    <x-input-error :messages="$errors->get('images.*')" class="mt-2" />
</div>
