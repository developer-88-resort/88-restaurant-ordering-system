@props(['href' => null, 'active' => false, 'disabled' => false, 'badge' => null])

@if ($disabled)
    <div
        title="{{ trim(strip_tags($slot)) }}"
        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-400 cursor-not-allowed"
        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
    >
        <span class="shrink-0 h-5 w-5 [&>svg]:h-5 [&>svg]:w-5">{{ $icon ?? '' }}</span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $slot }}</span>
        <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-300 bg-gray-100 rounded px-1.5 py-0.5" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ __('Soon') }}</span>
    </div>
@else
    <a
        href="{{ $href }}"
        title="{{ trim(strip_tags($slot)) }}"
        {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150 ' . ($active ? 'bg-[#8A3330] text-white shadow-sm shadow-[#8A3330]/20' : 'text-gray-600 hover:bg-[#F3E1DC]/70 hover:text-[#8A3330]')]) }}
        :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''"
    >
        <span class="relative shrink-0 h-5 w-5 [&>svg]:h-5 [&>svg]:w-5 {{ $active ? 'text-white' : 'text-gray-400' }}">
            {{ $icon ?? '' }}
            @isset($badge)
                <span
                    x-show="sidebarCollapsed && pendingOrdersCount > 0"
                    x-cloak
                    x-text="pendingOrdersCount"
                    class="hidden lg:flex absolute -top-1.5 -right-1.5 h-4 min-w-[1rem] px-1 items-center justify-center rounded-full bg-red-600 text-white text-[10px] font-bold leading-none"
                >{{ $badge }}</span>
            @endisset
        </span>
        <span class="truncate flex-1" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $slot }}</span>
        @isset($badge)
            <span
                x-show="pendingOrdersCount > 0"
                x-cloak
                x-text="pendingOrdersCount"
                :class="sidebarCollapsed ? 'lg:hidden' : ''"
                class="shrink-0 h-5 min-w-[1.25rem] px-1.5 flex items-center justify-center rounded-full bg-red-600 text-white text-[11px] font-bold leading-none"
            >{{ $badge }}</span>
        @endisset
    </a>
@endif
