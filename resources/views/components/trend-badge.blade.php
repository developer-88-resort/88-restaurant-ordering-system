@props(['data', 'invert' => false])

@if ($data)
    @php
        $percent = $data['percent'];
        $isFlat = ! is_null($percent) && abs($percent) < 0.05;
        $isUp = ! is_null($percent) && $percent >= 0.05;
        $goodColor = $invert ? 'text-red-600' : 'text-green-600';
        $badColor = $invert ? 'text-green-600' : 'text-red-600';
    @endphp
    <p class="mt-1 flex items-center gap-1.5 text-xs">
        @if (is_null($percent))
            <span class="inline-flex items-center gap-0.5 font-semibold text-blue-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                </svg>
                {{ __('New') }}
            </span>
        @elseif ($isFlat)
            <span class="inline-flex items-center gap-0.5 font-semibold text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                </svg>
                0%
            </span>
        @else
            <span class="inline-flex items-center gap-0.5 font-semibold {{ $isUp ? $goodColor : $badColor }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                    @if ($isUp)
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                    @endif
                </svg>
                {{ number_format(abs($percent), 1) }}%
            </span>
        @endif
        <span class="text-gray-400">{{ $data['label'] }}</span>
    </p>
@endif
