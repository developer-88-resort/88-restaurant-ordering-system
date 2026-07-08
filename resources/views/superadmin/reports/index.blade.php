<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reports') }}
        </h2>
    </x-slot>

    {{-- Date range filter --}}
    <div class="flex flex-wrap gap-2 mb-6">
        @foreach (['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')] as $value => $label)
            <a href="{{ route('superadmin.reports.index', ['range' => $value]) }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ $range === $value ? 'bg-[#8A3330] text-white' : 'bg-[#F3E1DC] text-gray-700 hover:bg-[#e9d3cb]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:0ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Total Revenue') }}</div>
            <div class="mt-2 text-2xl font-bold text-[#8A3330]">₱{{ number_format($totalRevenue, 2) }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ $rangeLabel }} &middot; {{ __('Paid orders only') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:80ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Total Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalOrders }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ __('Excludes cancelled') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:160ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Average Order Value') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">₱{{ number_format($averageOrderValue, 2) }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ __('Per paid order') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:240ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Cancelled Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $cancelledOrders }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ $rangeLabel }}</p>
        </div>
    </div>

    {{-- Daily revenue chart --}}
    <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 mb-6 animate-fade-slide-up [animation-delay:320ms]">
        <h3 class="font-semibold text-gray-900">{{ __('Daily Revenue') }}</h3>
        <p class="text-sm text-gray-500 mb-6">{{ $rangeLabel }}</p>

        @if ($dailySales->isEmpty())
            <p class="text-sm text-gray-400 py-12 text-center">{{ __('No sales data for this period.') }}</p>
        @else
            @php $maxRevenue = $dailySales->max('revenue') ?: 1; @endphp
            <div class="flex items-end gap-2 h-48 overflow-x-auto pb-1">
                @foreach ($dailySales as $day)
                    @php $heightPct = max(4, ($day->revenue / $maxRevenue) * 100); @endphp
                    <div class="w-14 shrink-0 flex flex-col items-center justify-end h-full relative group">
                        <div class="absolute -top-8 hidden group-hover:block bg-gray-900 text-white text-xs rounded-md px-2 py-1 whitespace-nowrap z-10">
                            ₱{{ number_format($day->revenue, 2) }}
                        </div>
                        <div class="w-full max-w-[32px] mx-auto bg-[#8A3330] group-hover:bg-[#742927] rounded-t-md transition-colors animate-grow-bar"
                             style="--bar-height: {{ $heightPct }}%; animation-delay: {{ 400 + $loop->index * 60 }}ms"></div>
                        <span class="mt-2 text-[10px] text-gray-400 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($day->sale_date)->format('M d') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Best sellers --}}
        <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden animate-fade-slide-up [animation-delay:400ms]">
            <div class="px-6 py-4 border-b border-[#E5DDD0]">
                <h3 class="font-semibold text-gray-900">{{ __('Best-Selling Items') }}</h3>
            </div>
            @if ($bestSellers->isEmpty())
                <p class="text-sm text-gray-400 px-6 py-8 text-center">{{ __('No item sales for this period.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5DDD0]">
                        <thead class="bg-[#FAF6EE]">
                            <tr>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Item') }}</th>
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Qty Sold') }}</th>
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($bestSellers as $item)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600 text-right">{{ $item->total_qty }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($item->total_revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Sales by category --}}
        <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden animate-fade-slide-up [animation-delay:480ms]">
            <div class="px-6 py-4 border-b border-[#E5DDD0]">
                <h3 class="font-semibold text-gray-900">{{ __('Sales by Category') }}</h3>
            </div>
            @if ($categorySales->isEmpty())
                <p class="text-sm text-gray-400 px-6 py-8 text-center">{{ __('No category sales for this period.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5DDD0]">
                        <thead class="bg-[#FAF6EE]">
                            <tr>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Category') }}</th>
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($categorySales as $category)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $category->category_name }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($category->total_revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
