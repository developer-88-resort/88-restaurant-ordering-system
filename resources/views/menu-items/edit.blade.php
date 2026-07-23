<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Item') }} — {{ $item->name }}
        </h2>
    </x-slot>

    <div>
        <form method="POST" action="{{ route('menu-items.update', $item) }}" enctype="multipart/form-data"
              data-draft-key="menu-item-edit-{{ $item->id }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                <div class="lg:col-span-2 space-y-6">
                    <x-forms.section :title="__('Basic Information')">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $item->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="menu_category_id" :value="__('Category')" />
                                <select id="menu_category_id" name="menu_category_id" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('menu_category_id', $item->menu_category_id) == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('menu_category_id')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-5">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="2"
                                      class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">{{ old('description', $item->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                    </x-forms.section>

                    <x-forms.section :title="__('Pricing & Prep')">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <x-input-label for="price" :value="__('Price')" />
                                <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="block mt-1 w-full" :value="old('price', $item->price)" />
                                <p class="mt-1 text-xs text-gray-400">{{ __('Required unless you add variants below.') }}</p>
                                <x-input-error :messages="$errors->get('price')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="sku" :value="__('SKU (optional)')" />
                                <x-text-input id="sku" name="sku" type="text" class="block mt-1 w-full" :value="old('sku', $item->sku)" />
                                <x-input-error :messages="$errors->get('sku')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="prep_time_minutes" :value="__('Prep Time (min)')" />
                                <x-text-input id="prep_time_minutes" name="prep_time_minutes" type="number" min="0" class="block mt-1 w-full" :value="old('prep_time_minutes', $item->prep_time_minutes)" />
                                <x-input-error :messages="$errors->get('prep_time_minutes')" class="mt-2" />
                            </div>
                        </div>
                    </x-forms.section>

                    <x-forms.section :title="__('Variants')" :description="__('Optional — use instead of creating duplicate items for things like Solo/Medium/Large, Spicy/Oriental, or Half/Whole. Leave empty if this item is just sold at the Price above.')">
                        @php
                            $existingVariants = $item->variants->values();
                            $defaultVariantIndex = $existingVariants->search(fn ($variant) => $variant->is_default);
                        @endphp
                        <div x-data="{
                                variants: @js($existingVariants->map(fn ($variant) => [
                                    'id' => $variant->id,
                                    'name' => $variant->name,
                                    'sku' => $variant->sku,
                                    'price' => (float) $variant->price,
                                    'existingImageUrl' => $variant->imageUrl(),
                                    'imagePreview' => null,
                                    'removeImage' => false,
                                ])),
                                defaultIndex: {{ $defaultVariantIndex !== false ? $defaultVariantIndex : 'null' }},
                                addVariant() {
                                    this.variants.push({ id: null, name: '', sku: '', price: '', existingImageUrl: null, imagePreview: null, removeImage: false });
                                    if (this.defaultIndex === null) this.defaultIndex = this.variants.length - 1;
                                },
                                removeVariant(index) {
                                    this.variants.splice(index, 1);
                                    if (this.defaultIndex === index) this.defaultIndex = this.variants.length ? 0 : null;
                                    else if (this.defaultIndex > index) this.defaultIndex--;
                                },
                            }"
                             x-persist="{ key: 'menu-item-edit-{{ $item->id }}-variants', paths: ['variants', 'defaultIndex'] }"
                             data-draft-ignore
                        >
                            <template x-for="(variant, index) in variants" :key="index">
                                <div class="flex gap-3 border border-[#E5DDD0] rounded-lg p-3 mb-3">
                                    <input type="hidden" :name="'variants[' + index + '][id]'" :value="variant.id">
                                    <input type="hidden" :name="'variants[' + index + '][remove_image]'" :value="variant.removeImage ? 1 : 0">

                                    <div class="shrink-0">
                                        <label class="relative h-16 w-16 rounded-md bg-[#FAF6EE] border border-dashed border-[#D9CCBA] overflow-hidden cursor-pointer flex items-center justify-center hover:border-[#8A3330]">
                                            <img x-show="variant.imagePreview || (variant.existingImageUrl && !variant.removeImage)" :src="variant.imagePreview || variant.existingImageUrl" class="absolute inset-0 h-full w-full object-cover">
                                            <span x-show="!variant.imagePreview && (!variant.existingImageUrl || variant.removeImage)" class="text-[9px] text-gray-400 text-center px-1 leading-tight">{{ __('Photo (optional)') }}</span>
                                            <input type="file" accept="image/*" :name="'variants[' + index + '][image]'" class="sr-only"
                                                   @change="if ($event.target.files[0]) { variant.imagePreview = URL.createObjectURL($event.target.files[0]); variant.removeImage = false; }">
                                        </label>
                                        <button type="button"
                                                x-show="(variant.imagePreview || variant.existingImageUrl) && !variant.removeImage"
                                                @click="variant.removeImage = true; variant.imagePreview = null"
                                                class="mt-1 text-[9px] text-gray-400 hover:text-red-600 underline block mx-auto">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <div class="grid grid-cols-12 gap-2">
                                            <div class="col-span-1 flex flex-col items-center gap-0.5">
                                                <input type="radio" name="default_variant_index" :value="index" x-model.number="defaultIndex" class="mt-1">
                                                <span class="text-[8px] text-gray-400 uppercase tracking-wide">{{ __('Default') }}</span>
                                            </div>
                                            <div class="col-span-4">
                                                <label class="block text-[9px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Variant Name') }}</label>
                                                <input type="text" x-model="variant.name" :name="'variants[' + index + '][name]'" placeholder="{{ __('e.g. Spicy, Solo, Large') }}"
                                                       class="block w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">
                                            </div>
                                            <div class="col-span-3">
                                                <label class="block text-[9px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">{{ __('SKU') }}</label>
                                                <input type="text" x-model="variant.sku" :name="'variants[' + index + '][sku]'" placeholder="{{ __('optional') }}"
                                                       class="block w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">
                                            </div>
                                            <div class="col-span-3">
                                                <label class="block text-[9px] font-semibold text-gray-400 uppercase tracking-wide mb-0.5">{{ __('Price') }}</label>
                                                <input type="number" step="0.01" min="0" x-model="variant.price" :name="'variants[' + index + '][price]'" placeholder="0.00"
                                                       class="block w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">
                                            </div>
                                            <div class="col-span-1 flex justify-center pt-4">
                                                <button type="button" @click="removeVariant(index)" title="{{ __('Remove this variant') }}" class="text-gray-400 hover:text-red-600">✕</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <p class="text-xs text-gray-400 mb-2" x-show="variants.length > 0" x-cloak>{{ __('"Default" is pre-selected when this item is ordered. Only add a photo if this variant looks different from the item photo above (e.g. a different sauce/color) — otherwise leave it blank.') }}</p>
                            <button type="button" @click="addVariant()" class="text-sm font-medium text-[#8A3330] hover:underline">
                                + {{ __('Add Variant') }}
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('variants')" class="mt-2" />
                    </x-forms.section>

                </div>

                <div class="space-y-6">
                    <x-forms.section :title="__('Images')" :description="__('First image (or whichever is Primary) shows on the grid, receipts, and the customer menu.')">
                        <x-forms.image-uploader :existing-images="$item->images->map(fn ($image) => [
                            'id' => $image->id,
                            'url' => $image->url,
                            'is_primary' => $image->is_primary,
                        ])->all()" />
                    </x-forms.section>

                    <x-forms.section :title="__('Availability & Flags')">
                        <div>
                            <x-input-label for="availability_status" :value="__('Availability')" />
                            <select id="availability_status" name="availability_status" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm">
                                @foreach ($availabilityOptions as $option)
                                    <option value="{{ $option->value }}" @selected(old('availability_status', $item->availability_status->value) === $option->value)>{{ $option->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('availability_status')" class="mt-2" />
                        </div>

                        <div class="mt-5">
                            <x-input-label for="sort_order" :value="__('Sort Order')" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', $item->sort_order)" />
                            <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                        </div>

                        <div class="mt-5 flex flex-col gap-3">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input id="is_featured" name="is_featured" type="checkbox" value="1"
                                       class="rounded border-gray-300 text-[#8A3330] shadow-sm focus:ring-[#8A3330]" @checked(old('is_featured', $item->is_featured))>
                                <span class="text-sm text-gray-600">{{ __('Featured') }}</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input id="is_best_seller" name="is_best_seller" type="checkbox" value="1"
                                       class="rounded border-gray-300 text-[#8A3330] shadow-sm focus:ring-[#8A3330]" @checked(old('is_best_seller', $item->is_best_seller))>
                                <span class="text-sm text-gray-600">{{ __('Best Seller') }}</span>
                            </label>
                        </div>
                    </x-forms.section>
                </div>
            </div>

            <div class="sticky bottom-0 z-10 -mx-4 sm:-mx-6 mt-6 border-t border-[#E5DDD0] bg-[#F7F0E3]/95 backdrop-blur px-4 sm:px-6 py-4 flex items-center justify-end space-x-3">
                <a href="{{ route('menu-items.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
