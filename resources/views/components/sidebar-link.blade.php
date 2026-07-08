@props(['href' => null, 'active' => false, 'disabled' => false])

@if ($disabled)
    <div {{ $attributes->merge(['class' => 'flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-400 cursor-not-allowed']) }}>
        <span>{{ $slot }}</span>
        <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-300 bg-gray-100 rounded px-1.5 py-0.5">{{ __('Soon') }}</span>
    </div>
@else
    <a
        href="{{ $href }}"
        {{ $attributes->merge(['class' => 'block px-3 py-2 rounded-lg text-sm font-medium transition ' . ($active ? 'bg-[#8A3330] text-white' : 'text-gray-700 hover:bg-gray-50')]) }}
    >
        {{ $slot }}
    </a>
@endif
