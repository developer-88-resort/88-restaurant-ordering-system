<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Modifier Group') }}
        </h2>
    </x-slot>

    <div class="max-w-3xl">
        <form method="POST" action="{{ route('modifier-groups.store') }}" class="space-y-6"
              data-draft-key="modifier-group-create"
              x-data="{
                  options: [{ id: null, name: '', price_delta: '', sku: '' }],
                  addOption() { this.options.push({ id: null, name: '', price_delta: '', sku: '' }); },
                  removeOption(index) { this.options.splice(index, 1); },
              }"
              x-persist="{ key: 'modifier-group-create-options', paths: ['options'] }"
        >
            @csrf

            <x-forms.section :title="__('Group Details')">
                <div>
                    <x-input-label for="name" :value="__('Name')" />
                    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" required autofocus placeholder="{{ __('e.g. Rice Options, Spice Level') }}" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="selection_type" :value="__('Selection Type')" />
                        <select id="selection_type" name="selection_type" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm">
                            @foreach ($selectionTypes as $type)
                                <option value="{{ $type->value }}" @selected(old('selection_type', 'single') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('selection_type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="sort_order" :value="__('Sort Order')" />
                        <x-text-input id="sort_order" name="sort_order" type="number" min="0" class="block mt-1 w-full" :value="old('sort_order', 0)" />
                        <x-input-error :messages="$errors->get('sort_order')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-5 flex items-center">
                    <input id="is_required" name="is_required" type="checkbox" value="1"
                           class="rounded border-gray-300 text-[#8A3330] shadow-sm focus:ring-[#8A3330]" @checked(old('is_required'))>
                    <label for="is_required" class="ms-2 text-sm text-gray-600">{{ __('Customer must choose (required)') }}</label>
                </div>
            </x-forms.section>

            <x-forms.section :title="__('Options')" :description="__('E.g. for Spice Level: Mild, Medium, Spicy. Price is an add-on amount — leave it 0 if it doesn\'t change the price.')" data-draft-ignore>
                <template x-for="(option, index) in options" :key="index">
                    <div class="grid grid-cols-12 gap-2 items-start mb-3">
                        <input type="hidden" :name="'options[' + index + '][id]'" :value="option.id">
                        <div class="col-span-5">
                            <input type="text" x-model="option.name" :name="'options[' + index + '][name]'" placeholder="{{ __('Option name') }}"
                                   class="block w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">
                        </div>
                        <div class="col-span-3">
                            <input type="number" step="0.01" x-model="option.price_delta" :name="'options[' + index + '][price_delta]'" placeholder="{{ __('+ Price') }}"
                                   class="block w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">
                        </div>
                        <div class="col-span-3">
                            <input type="text" x-model="option.sku" :name="'options[' + index + '][sku]'" placeholder="{{ __('SKU (optional)') }}"
                                   class="block w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm text-sm">
                        </div>
                        <div class="col-span-1 flex justify-center pt-2">
                            <button type="button" @click="removeOption(index)" class="text-gray-400 hover:text-red-600">✕</button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addOption()" class="text-sm font-medium text-[#8A3330] hover:underline">
                    + {{ __('Add Option') }}
                </button>
                <x-input-error :messages="$errors->get('options')" class="mt-2" />
            </x-forms.section>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('modifier-groups.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                <x-primary-button>{{ __('Create Modifier Group') }}</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
