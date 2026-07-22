<x-app-layout>
    @php
        $canManageMenu = in_array(Auth::user()->role, [\App\Enums\UserRole::Superadmin, \App\Enums\UserRole::Admin], true);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Menu Management') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Manage your menu items and categories.') }}</p>
            </div>
            @if ($canManageMenu)
                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('menu-categories.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                        {{ __('Manage Categories') }}
                    </a>
                    @if (! $hasCategories)
                        <span class="inline-flex items-center px-4 py-2 bg-gray-300 rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                            {{ __('New Item') }}
                        </span>
                    @else
                        <a href="{{ route('menu-items.create') }}">
                            <x-primary-button>{{ __('New Item') }}</x-primary-button>
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </x-slot>

    @if (! $hasCategories)
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center rounded-xl border border-[#E5DDD0] bg-white p-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5 text-amber-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-900">{{ __('No categories yet') }}</p>
                <p class="mt-0.5 text-sm text-gray-500">{{ __('You need at least one category before you can add menu items.') }}</p>
            </div>
            @if ($canManageMenu)
                <a href="{{ route('menu-categories.create') }}" class="shrink-0">
                    <x-primary-button>{{ __('New Category') }}</x-primary-button>
                </a>
            @endif
        </div>
    @endif

    {{-- Filter toolbar --}}
    <form method="GET" action="{{ route('menu-items.index') }}" class="mb-6 space-y-3">
        @if ($showArchived)
            <input type="hidden" name="archived" value="1">
        @endif

        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#a99c8f" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 pointer-events-none">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}"
                       placeholder="{{ __('Search menu...') }}" autocomplete="off"
                       class="w-full rounded-xl border border-[#D9CCBA] bg-white pl-11 pr-4 py-2.5 text-sm text-gray-700 placeholder:text-gray-400 outline-none focus:border-[#8A3330] focus:ring-2 focus:ring-[#8A3330]">
            </div>

            <select name="category_id" onchange="this.form.submit()"
                    class="rounded-xl border-[#D9CCBA] text-sm text-gray-700 focus:border-[#8A3330] focus:ring-[#8A3330]">
                <option value="">{{ __('All Categories') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>

            <select name="availability" onchange="this.form.submit()"
                    class="rounded-xl border-[#D9CCBA] text-sm text-gray-700 focus:border-[#8A3330] focus:ring-[#8A3330]">
                <option value="">{{ __('Any Availability') }}</option>
                @foreach ($availabilityOptions as $option)
                    <option value="{{ $option->value }}" @selected(($filters['availability'] ?? null) === $option->value)>{{ $option->label() }}</option>
                @endforeach
            </select>

            <select name="sort" onchange="this.form.submit()"
                    class="rounded-xl border-[#D9CCBA] text-sm text-gray-700 focus:border-[#8A3330] focus:ring-[#8A3330]">
                <option value="">{{ __('Sort Order') }}</option>
                <option value="name_asc" @selected(($filters['sort'] ?? null) === 'name_asc')>{{ __('Name (A-Z)') }}</option>
                <option value="price_asc" @selected(($filters['sort'] ?? null) === 'price_asc')>{{ __('Price (Low to High)') }}</option>
                <option value="price_desc" @selected(($filters['sort'] ?? null) === 'price_desc')>{{ __('Price (High to Low)') }}</option>
                <option value="prep_asc" @selected(($filters['sort'] ?? null) === 'prep_asc')>{{ __('Prep Time (Fast First)') }}</option>
                <option value="newest" @selected(($filters['sort'] ?? null) === 'newest')>{{ __('Newest First') }}</option>
            </select>

            <button type="submit" class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl bg-[#8A3330] text-white text-sm font-semibold hover:bg-[#742927] transition">
                {{ __('Search') }}
            </button>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-sm">
            <label class="inline-flex items-center gap-1.5 text-gray-600 cursor-pointer">
                <input type="checkbox" name="featured" value="1" onchange="this.form.submit()" @checked($filters['featured'] ?? false)
                       class="rounded border-gray-300 text-[#8A3330] focus:ring-[#8A3330]">
                {{ __('Featured only') }}
            </label>

            @if (($filters['q'] ?? '') !== '' || ($filters['category_id'] ?? '') !== '' || ($filters['availability'] ?? '') !== '' || ($filters['sort'] ?? '') !== '' || ($filters['featured'] ?? false))
                <a href="{{ route('menu-items.index', $showArchived ? ['archived' => 1] : []) }}" class="text-[#8A3330] hover:underline font-medium">
                    {{ __('Clear filters') }}
                </a>
            @endif

            <span class="ml-auto inline-flex rounded-full border border-[#E5DDD0] bg-white p-0.5">
                <a href="{{ route('menu-items.index') }}"
                   class="px-3 py-1 rounded-full text-xs font-semibold transition {{ ! $showArchived ? 'bg-[#8A3330] text-white' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ __('Active') }}
                </a>
                <a href="{{ route('menu-items.index', ['archived' => 1]) }}"
                   class="px-3 py-1 rounded-full text-xs font-semibold transition {{ $showArchived ? 'bg-[#8A3330] text-white' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ __('Archived') }} ({{ $archivedCount }})
                </a>
            </span>
        </div>
    </form>

    @if ($items->isEmpty())
        <x-empty-state
            :title="$showArchived ? __('No archived items') : __('No menu items found')"
            :description="$showArchived ? __('Items you archive will show up here.') : __('Try adjusting your search or filters, or add your first menu item.')"
            :actionLabel="($showArchived || ! $hasCategories || ! $canManageMenu) ? null : __('New Item')"
            :actionHref="($showArchived || ! $hasCategories || ! $canManageMenu) ? null : route('menu-items.create')"
        />
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4"
             x-data="{
                 statuses: @js($items->getCollection()->mapWithKeys(fn ($i) => [$i->id => $i->availability_status->value])),
             }"
             x-init="
                 Echo.channel('menu').listen('.MenuItemAvailabilityChanged', (e) => {
                     if (e.menu_item_id in statuses) statuses[e.menu_item_id] = e.availability_status;
                 });
             "
        >
            @foreach ($items as $item)
                <div class="group bg-white border border-[#E5DDD0] rounded-xl overflow-hidden flex flex-col shadow-sm hover:shadow-md hover:border-[#D9CCBA] transition-all duration-200">
                    <div class="aspect-square bg-gradient-to-br from-[#FAF6EE] to-[#F1E9DA] relative overflow-hidden">
                        @if ($item->primaryImageUrl())
                            <img src="{{ $item->primaryImageUrl() }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-6 w-6 text-[#D9CCBA]">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                        @endif

                        <div class="absolute top-1.5 left-1.5 flex flex-col gap-1 items-start">
                            @if ($item->is_featured)
                                <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide rounded-full bg-amber-500 text-white shadow-sm">{{ __('Featured') }}</span>
                            @endif
                            @if ($item->is_best_seller)
                                <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide rounded-full bg-[#8A3330] text-white shadow-sm">{{ __('Best Seller') }}</span>
                            @endif
                        </div>

                        @if ($showArchived)
                            <span class="absolute top-1.5 right-1.5 inline-flex items-center px-2 py-1 text-[10px] font-semibold rounded-full bg-slate-700 text-white shadow-sm">
                                {{ __('Archived') }}
                            </span>
                        @elseif ($canManageMenu)
                            <select
                                x-model="statuses[{{ $item->id }}]"
                                @change="
                                    const value = $event.target.value, previous = statuses[{{ $item->id }}];
                                    fetch('{{ route('menu-items.set-availability', $item) }}', {
                                        method: 'PATCH',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                            'Accept': 'application/json',
                                            'Content-Type': 'application/json',
                                        },
                                        body: JSON.stringify({ status: value }),
                                    }).catch(() => statuses[{{ $item->id }}] = previous);
                                "
                                :class="{
                                    'bg-green-100 text-green-800': statuses[{{ $item->id }}] === 'available',
                                    'bg-gray-200 text-gray-700': statuses[{{ $item->id }}] === 'out_of_stock',
                                    'bg-amber-100 text-amber-800': statuses[{{ $item->id }}] === 'seasonal',
                                    'bg-slate-700 text-white': statuses[{{ $item->id }}] === 'hidden',
                                }"
                                class="absolute top-1.5 right-1.5 text-[10px] font-semibold rounded-full border-none py-1 pl-2 pr-5 shadow-sm cursor-pointer focus:ring-2 focus:ring-[#8A3330]"
                            >
                                @foreach ($availabilityOptions as $option)
                                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="p-2.5 flex-1 flex flex-col">
                        <span class="text-[9px] font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ $item->menuCategory->name }}</span>
                        <h3 class="mt-0.5 text-sm font-semibold text-gray-900 leading-snug truncate" title="{{ $item->name }}">{{ $item->name }}</h3>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @if ($item->prep_time_minutes)
                                <span class="text-[10px] text-gray-400">{{ __(':minutes min prep', ['minutes' => $item->prep_time_minutes]) }}</span>
                            @endif
                            @if ($item->hasVariants())
                                <span class="text-[10px] font-semibold text-[#8A7B9E]">{{ __(':count variants', ['count' => $item->variants->count()]) }}</span>
                            @endif
                        </div>

                        <div class="mt-auto pt-2 flex items-end justify-between">
                            <span class="text-sm font-bold text-[#8A3330]">{{ $item->priceRangeLabel() }}</span>
                            @if ($canManageMenu)
                                <div class="flex items-center">
                                    @if ($showArchived)
                                        <form action="{{ route('menu-items.restore', $item) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="{{ __('Restore') }}" class="p-1 rounded-md text-gray-400 hover:text-green-600 hover:bg-green-50 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('menu-items.edit', $item) }}" title="{{ __('Edit') }}"
                                           class="p-1 rounded-md text-gray-400 hover:text-[#8A3330] hover:bg-[#8A3330]/5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <x-confirm-form
                                            :action="route('menu-items.destroy', $item)"
                                            method="DELETE"
                                            :title="__('Archive this item?')"
                                            :message="__('You can restore :name later from the Archived tab.', ['name' => $item->name])"
                                            :confirm-label="__('Archive')"
                                        >
                                            <button type="submit" title="{{ __('Archive') }}" class="p-1 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25m-2.25 2.25V6.75M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                                </svg>
                                            </button>
                                        </x-confirm-form>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    @endif
</x-app-layout>
