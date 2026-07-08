@props(['title', 'description' => null, 'actionLabel' => null, 'actionHref' => null])

<div class="bg-white border border-[#E5DDD0] rounded-xl py-16 px-6 flex flex-col items-center justify-center text-center">
    <div class="h-14 w-14 rounded-full bg-[#F3E1DC] flex items-center justify-center mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-7 w-7">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.25 2.25v13.5a2.25 2.25 0 002.25 2.25h10.176a2.25 2.25 0 002.25-2.25V6a2.25 2.25 0 00-2.25-2.25H15M9 3.75c0 1.036.84 1.875 1.875 1.875h2.25c1.036 0 1.875-.84 1.875-1.875M9 3.75c0-1.036.84-1.875 1.875-1.875h2.25c1.036 0 1.875.84 1.875 1.875M9 12h6m-6 3.75h6" />
        </svg>
    </div>
    <h3 class="text-base font-semibold text-gray-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ $description }}</p>
    @endif
    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="mt-6">
            <x-primary-button>{{ $actionLabel }}</x-primary-button>
        </a>
    @endif
</div>
