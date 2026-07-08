<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('New Table') }}
        </h2>
    </x-slot>

    <div
        x-data="{
            mode: 'single',
            prefix: 'Table',
            start: 1,
            count: 5,
            get preview() {
                const names = [];
                const total = Math.max(0, Math.min(this.count || 0, 9999));
                for (let i = 0; i < Math.min(total, 8); i++) {
                    names.push((this.prefix || '').trim() + ' ' + (Number(this.start || 1) + i));
                }
                return { names, remaining: Math.max(0, total - names.length) };
            }
        }"
        class="flex flex-col lg:flex-row gap-6 items-start"
    >
        <div class="flex-1 w-full">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">

                {{-- Tabs --}}
                <div class="flex gap-2 mb-6 border-b border-[#E5DDD0]">
                    <button type="button" @click="mode = 'single'"
                            :class="mode === 'single' ? 'border-[#8A3330] text-[#8A3330]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition">
                        {{ __('Single Table') }}
                    </button>
                    <button type="button" @click="mode = 'bulk'"
                            :class="mode === 'bulk' ? 'border-[#8A3330] text-[#8A3330]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="px-4 py-2 text-sm font-medium border-b-2 -mb-px transition">
                        {{ __('Multiple Tables') }}
                    </button>
                </div>

                {{-- Single table form --}}
                <form x-show="mode === 'single'" method="POST" action="{{ route('tables.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="table_number" :value="__('Table Number')" />
                        <x-text-input id="table_number" name="table_number" type="text" class="block mt-1 w-full" :value="old('table_number')" placeholder="e.g. Table 1" required autofocus />
                        <x-input-error :messages="$errors->get('table_number')" class="mt-2" />
                    </div>

                    <p class="mt-3 text-sm text-gray-500">{{ __('New tables start as Available. A QR code is generated automatically.') }}</p>

                    <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                        <a href="{{ route('tables.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Create Table') }}</x-primary-button>
                    </div>
                </form>

                {{-- Bulk table form --}}
                <form x-show="mode === 'bulk'" method="POST" action="{{ route('tables.store-bulk') }}">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div class="sm:col-span-1">
                            <x-input-label for="prefix" :value="__('Prefix')" />
                            <x-text-input id="prefix" name="prefix" type="text" class="block mt-1 w-full" x-model="prefix" placeholder="Table" required />
                            <x-input-error :messages="$errors->get('prefix')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="start" :value="__('Starting Number')" />
                            <x-text-input id="start" name="start" type="number" min="1" max="9999" class="block mt-1 w-full" x-model="start" required />
                            <x-input-error :messages="$errors->get('start')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="count" :value="__('How Many')" />
                            <x-text-input id="count" name="count" type="number" min="1" max="50" class="block mt-1 w-full" x-model="count" required />
                            <x-input-error :messages="$errors->get('count')" class="mt-2" />
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-gray-500">{{ __('Existing table numbers are automatically skipped, so it\'s safe to add more later.') }}</p>

                    <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                        <a href="{{ route('tables.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Create Tables') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Side panel --}}
        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
                <template x-if="mode === 'bulk'">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-[#8A7B9E] mb-4">{{ __('Preview') }}</h3>
                        <ul class="space-y-2">
                            <template x-for="name in preview.names" :key="name">
                                <li class="text-sm text-gray-700 border border-dashed border-[#D9CCBA] rounded-lg px-3 py-2 bg-[#FAF6EE]" x-text="name"></li>
                            </template>
                        </ul>
                        <p x-show="preview.remaining > 0" class="mt-2 text-xs text-gray-400" x-text="'+ ' + preview.remaining + ' {{ __('more') }}'"></p>
                        <p x-show="preview.names.length === 0" class="text-sm text-gray-400">{{ __('Enter details to preview the table names.') }}</p>
                    </div>
                </template>

                <template x-if="mode === 'single'">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-[#8A7B9E] mb-4">{{ __('Tips') }}</h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex gap-2">
                                <span class="text-[#8A3330]">&bull;</span>
                                {{ __('Table numbers must be unique.') }}
                            </li>
                            <li class="flex gap-2">
                                <span class="text-[#8A3330]">&bull;</span>
                                {{ __('Adding several tables at once? Switch to "Multiple Tables" above.') }}
                            </li>
                            <li class="flex gap-2">
                                <span class="text-[#8A3330]">&bull;</span>
                                {{ __('Each table gets its own printable QR code automatically.') }}
                            </li>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
