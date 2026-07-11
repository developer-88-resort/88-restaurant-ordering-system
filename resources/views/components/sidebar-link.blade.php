@props(['href' => null, 'active' => false, 'disabled' => false])

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
        <span class="shrink-0 h-5 w-5 [&>svg]:h-5 [&>svg]:w-5 {{ $active ? 'text-white' : 'text-gray-400' }}">{{ $icon ?? '' }}</span>
        <span class="truncate" :class="sidebarCollapsed ? 'lg:hidden' : ''">{{ $slot }}</span>
    </a>
@endif
