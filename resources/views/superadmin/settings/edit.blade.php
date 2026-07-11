<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ __('These details may appear on receipts and the customer ordering page.') }}</p>
    </x-slot>

    <div
        x-data="{
            resort_name: {{ Js::from(old('resort_name', $setting->resort_name)) }},
            address: {{ Js::from(old('address', $setting->address)) }},
            contact_number: {{ Js::from(old('contact_number', $setting->contact_number)) }},
            email: {{ Js::from(old('email', $setting->email)) }},
            opening_time: {{ Js::from(old('opening_time', optional($setting->opening_time)->format('H:i'))) }},
            closing_time: {{ Js::from(old('closing_time', optional($setting->closing_time)->format('H:i'))) }},
            saving: false,
        }"
        class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start"
    >
        {{-- Settings form — one form, one card, internal section dividers --}}
        <div class="lg:col-span-2 w-full">
            <div class="bg-white border border-[#E5DDD0] rounded-xl">
                <form method="POST" action="{{ route('superadmin.settings.update') }}" @submit="saving = true">
                    @csrf
                    @method('PUT')

                    {{-- Resort Details --}}
                    <div class="p-6 sm:p-8">
                        <div class="flex items-start gap-3 mb-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F3E1DC]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('Resort Details') }}</h3>
                                <p class="mt-0.5 text-sm text-gray-500">{{ __('The name and address customers will see on receipts and the ordering page.') }}</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <x-input-label for="resort_name" :value="__('Resort Name')" />
                                <x-text-input id="resort_name" name="resort_name" type="text" class="block mt-1.5 w-full" x-model="resort_name" required />
                                <x-input-error :messages="$errors->get('resort_name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="address" :value="__('Address')" />
                                <x-text-input id="address" name="address" type="text" class="block mt-1.5 w-full" x-model="address" />
                                <x-input-error :messages="$errors->get('address')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="p-6 sm:p-8 border-t border-[#E5DDD0]">
                        <div class="flex items-start gap-3 mb-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F3E1DC]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.733.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('Contact Information') }}</h3>
                                <p class="mt-0.5 text-sm text-gray-500">{{ __('How customers or staff can reach the resort.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="contact_number" :value="__('Contact Number')" />
                                <x-text-input id="contact_number" name="contact_number" type="text" class="block mt-1.5 w-full" x-model="contact_number" />
                                <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="block mt-1.5 w-full" x-model="email" />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Business Hours --}}
                    <div class="p-6 sm:p-8 border-t border-[#E5DDD0]">
                        <div class="flex items-start gap-3 mb-6">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F3E1DC]">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ __('Business Hours') }}</h3>
                                <p class="mt-0.5 text-sm text-gray-500">{{ __('Shown in the live preview and on the customer menu page.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="opening_time" :value="__('Opening Time')" />
                                <div class="relative mt-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <x-text-input id="opening_time" name="opening_time" type="time" class="block w-full pl-9" x-model="opening_time" />
                                </div>
                                <x-input-error :messages="$errors->get('opening_time')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="closing_time" :value="__('Closing Time')" />
                                <div class="relative mt-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <x-text-input id="closing_time" name="closing_time" type="time" class="block w-full pl-9" x-model="closing_time" />
                                </div>
                                <x-input-error :messages="$errors->get('closing_time')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Actions footer --}}
                    <div class="px-6 sm:px-8 py-4 flex items-center justify-end bg-[#FAF6EE] border-t border-[#E5DDD0] rounded-b-xl">
                        <x-primary-button x-bind:disabled="saving" class="disabled:opacity-60 disabled:cursor-not-allowed">
                            <span x-show="!saving">{{ __('Save Changes') }}</span>
                            <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-3.5 w-3.5 animate-spin">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                {{ __('Saving...') }}
                            </span>
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Live Preview --}}
        <div class="w-full lg:sticky lg:top-20">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F3E1DC]">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Live Preview') }}</h3>
                </div>

                <div class="border border-dashed border-[#D9CCBA] rounded-lg p-6 text-center bg-[#FAF6EE]">
                    <p class="font-bold text-gray-900 break-words" x-text="resort_name || '{{ __('Resort Name') }}'"></p>
                    <p class="text-xs text-gray-500 mt-2 break-words" x-text="address || '{{ __('Resort address') }}'"></p>
                    <p class="text-xs text-gray-500 mt-1" x-text="contact_number || '{{ __('Contact number') }}'"></p>
                    <p class="text-xs text-gray-500 break-words" x-text="email || '{{ __('Email address') }}'"></p>
                    <p class="text-xs font-semibold text-[#8A3330] mt-3 pt-3 border-t border-dashed border-[#D9CCBA]"
                       x-text="(opening_time && closing_time) ? ('{{ __('Open') }} ' + opening_time + ' – ' + closing_time) : '{{ __('Business hours') }}'"></p>
                </div>

                <p class="mt-4 text-xs text-gray-400">{{ __('This is how your resort info may look on printed receipts and the customer menu page.') }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
