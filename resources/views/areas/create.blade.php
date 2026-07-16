<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Area') }}
        </h2>
    </x-slot>

    <div class="max-w-lg">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
            <form method="POST" action="{{ route('areas.store') }}">
                @csrf

                <div>
                    <x-input-label for="name" :value="__('Area Name')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" placeholder="{{ __('e.g. Cottages') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-5">
                    <x-input-label for="sort_order" :value="__('Sort Order')" />
                    <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $nextSortOrder)" />
                    <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                    <a href="{{ route('areas.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Create Area') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
