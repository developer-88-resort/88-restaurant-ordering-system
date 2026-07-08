<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Category') }} — {{ $category->name }}
        </h2>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        <div class="flex-1 max-w-2xl w-full">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
                <form method="POST" action="{{ route('menu-categories.update', $category) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $category->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="sort_order" :value="__('Sort Order')" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $category->sort_order)" />
                            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-5 flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                               class="rounded border-gray-300 text-[#8A3330] shadow-sm focus:ring-[#8A3330]"
                               @checked(old('is_active', $category->is_active))>
                        <label for="is_active" class="ms-2 text-sm text-gray-600">{{ __('Active') }}</label>
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                        <a href="{{ route('menu-categories.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full lg:w-72 shrink-0">
            <x-category-reference-list :categories="$existingCategories" />
        </div>
    </div>
</x-app-layout>
