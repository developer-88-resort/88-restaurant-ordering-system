<x-customer-layout location-label="{{ __('Menu') }}">
    <div class="px-4 py-4 max-w-5xl mx-auto">
        <a href="{{ route('customer.welcome.show') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
            &larr; {{ __('Back') }}
        </a>
        @if ($customerName)
            <p class="mt-2 text-sm text-[#8A7B6D]">{{ __('Just browsing, :name? Take your time!', ['name' => $customerName]) }}</p>
        @endif
    </div>

    <div class="px-4 pb-10 max-w-5xl mx-auto space-y-8">
        @if ($categories->isEmpty())
            <x-empty-state
                :title="__('Nothing on the menu right now')"
                :description="__('Please check back later.')"
            />
        @else
            @foreach ($categories as $category)
                <div>
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="h-5 w-1 rounded-full bg-[#8A3330]"></span>
                        <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                        <span class="text-xs font-medium text-[#8A7B6D] bg-[#F7F0E3] border border-[#E5DDD0] rounded-full px-2 py-0.5">{{ $category->menuItems->count() }}</span>
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($category->menuItems as $item)
                            <div class="w-full flex items-center gap-3 bg-white border border-[#E5DDD0] rounded-xl p-3 shadow-sm">
                                <div class="h-16 w-16 rounded-lg bg-[#FAF6EE] shrink-0 overflow-hidden flex items-center justify-center">
                                    @if ($item->image_path)
                                        <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-6 w-6 text-[#D9CCBA]">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $item->name }}</p>
                                    <p class="text-sm font-semibold text-[#8A3330] mt-0.5">₱{{ number_format($item->price, 2) }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-customer-layout>
