@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'bg-white border border-[#E5DDD0] rounded-xl p-6 sm:p-8']) }}>
    <div class="mb-5">
        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide">{{ $title }}</h3>
        @if ($description)
            <p class="mt-1 text-xs text-gray-500">{{ $description }}</p>
        @endif
    </div>

    {{ $slot }}
</div>
