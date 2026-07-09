<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Area') }} — {{ $area->name }}
        </h2>
    </x-slot>

    <div class="max-w-lg">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
            <form method="POST" action="{{ route('areas.update', $area) }}">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Area Name')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $area->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-5">
                    <x-input-label for="sort_order" :value="__('Sort Order')" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $area->sort_order)" />
                    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                </div>

                <div class="mt-5 flex items-center gap-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $area->is_active)) class="rounded border-gray-300 text-[#8A3330] focus:ring-[#8A3330]">
                    <x-input-label for="is_active" :value="__('Active')" class="!mb-0" />
                </div>

                <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                    <a href="{{ route('areas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
