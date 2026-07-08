<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Menu Management') }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ __('Manage your menu items and categories.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('menu-categories.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                    {{ __('Manage Categories') }}
                </a>
                @if ($categories->isEmpty())
                    <span class="inline-flex items-center px-4 py-2 bg-gray-300 rounded-md font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('New Item') }}
                    </span>
                @else
                    <a href="{{ route('menu-items.create') }}">
                        <x-primary-button>{{ __('New Item') }}</x-primary-button>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>


    @if ($categories->isEmpty())
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
            <a href="{{ route('menu-categories.create') }}" class="shrink-0">
                <x-primary-button>{{ __('New Category') }}</x-primary-button>
            </a>
        </div>
    @endif

    {{-- Search --}}
    <form method="GET" action="{{ route('menu-items.index') }}" class="mb-4">
        @if ($activeCategoryId)
            <input type="hidden" name="category" value="{{ $activeCategoryId }}">
        @endif
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#a99c8f" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 pointer-events-none">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="{{ __('Search menu...') }}"
                class="w-full rounded-xl border border-[#D9CCBA] bg-white pl-11 pr-4 py-3 text-sm text-gray-700 placeholder:text-gray-400 outline-none focus:border-[#8A3330] focus:ring-2 focus:ring-[#8A3330]"
            >
        </div>
    </form>

    {{-- Category pills --}}
    @if ($categories->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('menu-items.index', array_filter(['search' => $search])) }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ is_null($activeCategoryId) ? 'bg-[#8A3330] text-white' : 'bg-[#F3E1DC] text-gray-700 hover:bg-[#e9d3cb]' }}">
                {{ __('All') }}
            </a>
            @foreach ($categories as $category)
                <a href="{{ route('menu-items.index', array_filter(['category' => $category->id, 'search' => $search])) }}"
                   class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ $activeCategoryId === $category->id ? 'bg-[#8A3330] text-white' : 'bg-[#F3E1DC] text-gray-700 hover:bg-[#e9d3cb]' }} {{ $category->is_active ? '' : 'opacity-50' }}">
                    {{ $category->name }}
                    @unless ($category->is_active)
                        <span class="text-[10px]">({{ __('inactive') }})</span>
                    @endunless
                </a>
            @endforeach
        </div>
    @endif

    {{-- Item grid --}}
    @if ($items->isEmpty())
        <x-empty-state
            :title="__('No menu items found')"
            :description="__('Try adjusting your search or filters, or add your first menu item.')"
            :actionLabel="$categories->isEmpty() ? null : __('New Item')"
            :actionHref="$categories->isEmpty() ? null : route('menu-items.create')"
        />
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach ($items as $item)
                <div class="group bg-white border border-[#E5DDD0] rounded-xl overflow-hidden flex flex-col shadow-sm hover:shadow-md hover:border-[#D9CCBA] transition-all duration-200">
                    <div class="aspect-square bg-gradient-to-br from-[#FAF6EE] to-[#F1E9DA] relative overflow-hidden">
                        @if ($item->image_path)
                            <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-6 w-6 text-[#D9CCBA]">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                            </div>
                        @endif

                        <form action="{{ route('menu-items.toggle-availability', $item) }}" method="POST" class="absolute top-1.5 right-1.5">
                            @csrf
                            @method('PATCH')
                            @if ($item->is_available)
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold leading-4 rounded-full bg-white/90 backdrop-blur text-green-700 shadow-sm hover:bg-green-50">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>{{ __('Available') }}
                                </button>
                            @else
                                <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 text-[11px] font-semibold leading-4 rounded-full bg-white/90 backdrop-blur text-gray-500 shadow-sm hover:bg-gray-100">
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-400"></span>{{ __('Out of Stock') }}
                                </button>
                            @endif
                        </form>
                    </div>

                    <div class="p-2.5 flex-1 flex flex-col">
                        <span class="text-[9px] font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ $item->menuCategory->name }}</span>
                        <h3 class="mt-0.5 text-sm font-semibold text-gray-900 leading-snug truncate" title="{{ $item->name }}">{{ $item->name }}</h3>

                        <div class="mt-auto pt-2 flex items-end justify-between">
                            <span class="text-sm font-bold text-[#8A3330]">₱{{ number_format($item->price, 2) }}</span>
                            <div class="flex items-center">
                                <a href="{{ route('menu-items.edit', $item) }}" title="{{ __('Edit') }}"
                                   class="p-1 rounded-md text-gray-400 hover:text-[#8A3330] hover:bg-[#8A3330]/5 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                <x-confirm-form
                                    :action="route('menu-items.destroy', $item)"
                                    method="DELETE"
                                    :title="__('Delete this item?')"
                                    :message="__('This will permanently remove :name from the menu.', ['name' => $item->name])"
                                    :confirm-label="__('Delete')"
                                >
                                    <button type="submit" title="{{ __('Delete') }}" class="p-1 rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </x-confirm-form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
