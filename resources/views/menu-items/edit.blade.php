<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Item') }} — {{ $item->name }}
        </h2>
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
            <form method="POST" action="{{ route('menu-items.update', $item) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $item->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="menu_category_id" :value="__('Category')" />
                        <select id="menu_category_id" name="menu_category_id" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('menu_category_id', $item->menu_category_id) == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('menu_category_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="price" :value="__('Price')" />
                        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('price', $item->price)" required />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-5" x-data="{ preview: {{ $item->image_path ? "'".asset('storage/'.$item->image_path)."'" : 'null' }} }">
                    <x-input-label for="image" :value="__('Image')" />
                    <div x-show="preview" class="mt-2 mb-2">
                        <img :src="preview" class="h-32 w-32 rounded-lg object-cover border border-[#E5DDD0]">
                    </div>
                    <input id="image" name="image" type="file" accept="image/*"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview"
                           class="block mt-1 w-full text-sm text-gray-600">
                    <p class="text-sm text-gray-500 mt-1">{{ __('Leave blank to keep the current image.') }}</p>
                    <x-input-error :messages="$errors->get('image')" class="mt-2" />
                </div>

                <div class="mt-5 flex items-center">
                    <input id="is_available" name="is_available" type="checkbox" value="1"
                           class="rounded border-gray-300 text-[#8A3330] shadow-sm focus:ring-[#8A3330]"
                           @checked(old('is_available', $item->is_available))>
                    <label for="is_available" class="ms-2 text-sm text-gray-600">{{ __('Available') }}</label>
                </div>

                <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                    <a href="{{ route('menu-items.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
