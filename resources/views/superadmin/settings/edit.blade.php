<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div
        x-data="{
            resort_name: {{ Js::from(old('resort_name', $setting->resort_name)) }},
            address: {{ Js::from(old('address', $setting->address)) }},
            contact_number: {{ Js::from(old('contact_number', $setting->contact_number)) }},
            email: {{ Js::from(old('email', $setting->email)) }},
            opening_time: {{ Js::from(old('opening_time', optional($setting->opening_time)->format('H:i'))) }},
            closing_time: {{ Js::from(old('closing_time', optional($setting->closing_time)->format('H:i'))) }},
        }"
        class="flex flex-col lg:flex-row gap-6 items-start"
    >
        <div class="flex-1 w-full">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
                <h3 class="font-semibold text-gray-900">{{ __('Resort Information') }}</h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('These details may appear on receipts and the customer ordering page.') }}</p>

                <form method="POST" action="{{ route('superadmin.settings.update') }}" class="mt-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="sm:col-span-2">
                            <x-input-label for="resort_name" :value="__('Resort Name')" />
                            <x-text-input id="resort_name" name="resort_name" type="text" class="block mt-1 w-full" x-model="resort_name" required />
                            <x-input-error :messages="$errors->get('resort_name')" class="mt-2" />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="address" :value="__('Address')" />
                            <x-text-input id="address" name="address" type="text" class="block mt-1 w-full" x-model="address" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="contact_number" :value="__('Contact Number')" />
                            <x-text-input id="contact_number" name="contact_number" type="text" class="block mt-1 w-full" x-model="contact_number" />
                            <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" x-model="email" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="opening_time" :value="__('Opening Time')" />
                            <x-text-input id="opening_time" name="opening_time" type="time" class="block mt-1 w-full" x-model="opening_time" />
                            <x-input-error :messages="$errors->get('opening_time')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="closing_time" :value="__('Closing Time')" />
                            <x-text-input id="closing_time" name="closing_time" type="time" class="block mt-1 w-full" x-model="closing_time" />
                            <x-input-error :messages="$errors->get('closing_time')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-[#8A7B9E] mb-4">{{ __('Live Preview') }}</h3>

                <div class="border border-dashed border-[#D9CCBA] rounded-lg p-6 text-center bg-[#FAF6EE]">
                    <p class="font-bold text-gray-900" x-text="resort_name || '{{ __('Resort Name') }}'"></p>
                    <p class="text-xs text-gray-500 mt-2" x-text="address || '{{ __('Resort address') }}'"></p>
                    <p class="text-xs text-gray-500 mt-1" x-text="contact_number || '{{ __('Contact number') }}'"></p>
                    <p class="text-xs text-gray-500" x-text="email || '{{ __('Email address') }}'"></p>
                    <p class="text-xs font-semibold text-[#8A3330] mt-3 pt-3 border-t border-dashed border-[#D9CCBA]"
                       x-text="(opening_time && closing_time) ? ('{{ __('Open') }} ' + opening_time + ' – ' + closing_time) : '{{ __('Business hours') }}'"></p>
                </div>

                <p class="mt-4 text-xs text-gray-400">{{ __('This is how your resort info may look on printed receipts and the customer menu page.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
