@props(['categories'])

<div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
    <h3 class="text-sm font-semibold text-gray-900">{{ __('Existing Categories') }}</h3>
    <p class="text-xs text-gray-500 mt-1">{{ __('Reference for naming and sort order.') }}</p>

    @if ($categories->isEmpty())
        <p class="mt-4 text-sm text-gray-400">{{ __('No categories yet — this will be your first one.') }}</p>
    @else
        <ul class="mt-4 space-y-2">
            @foreach ($categories as $category)
                <li class="flex items-center justify-between gap-3 text-sm">
                    <span class="text-gray-700 truncate {{ $category->is_active ? '' : 'opacity-50' }}">
                        {{ $category->name }}
                        @unless ($category->is_active)
                            <span class="text-[10px] text-gray-400">({{ __('inactive') }})</span>
                        @endunless
                    </span>
                    <span class="shrink-0 font-mono text-xs text-[#8A7B9E] bg-[#FAF6EE] rounded px-2 py-0.5">{{ $category->sort_order }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
