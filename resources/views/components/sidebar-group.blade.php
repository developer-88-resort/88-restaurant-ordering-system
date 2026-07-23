@props(['label'])

<div>
    <div class="mb-2 flex items-center px-3" :class="sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''">
        <p class="truncate text-xs font-semibold uppercase tracking-wider text-gray-400" :class="sidebarCollapsed ? 'lg:hidden' : ''">
            {{ $label }}
        </p>
        <div class="hidden w-6 border-t border-[#E5DDD0]" :class="sidebarCollapsed ? 'lg:block' : ''"></div>
    </div>
    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
