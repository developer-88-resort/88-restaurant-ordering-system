<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reports') }}
            </h2>
            @php
                $pdfQuery = array_filter([
                    'range' => (! $selectedMonth && ! $selectedDate) ? $range : null,
                    'month' => $selectedMonth,
                    'date' => $selectedDate,
                ]);
            @endphp
            <a href="{{ route('superadmin.reports.pdf', $pdfQuery) }}" data-turbo="false"
               class="inline-flex items-center gap-1.5 rounded-full bg-[#8A3330] hover:bg-[#742927] px-4 py-2 text-sm font-semibold text-white transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 12m0 0l4.5-4.5M12 12V3" />
                </svg>
                {{ __('Download PDF') }}
            </a>
        </div>
    </x-slot>

    {{-- Date range filter --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        @foreach (['today' => __('Today'), 'week' => __('This Week'), 'month' => __('This Month'), 'all' => __('All Time')] as $value => $label)
            <a href="{{ route('superadmin.reports.index', ['range' => $value]) }}"
               class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ (! $selectedMonth && ! $selectedDate && $range === $value) ? 'bg-[#8A3330] text-white' : 'bg-[#F3E1DC] text-gray-700 hover:bg-[#e9d3cb]' }}">
                {{ $label }}
            </a>
        @endforeach

        {{-- Calendar picker: pick a specific day, or browse to view an entire past month --}}
        <div
            x-data="{
                open: false,
                viewYear: {{ (int) explode('-', $calendarMonth)[0] }},
                viewMonth: {{ (int) explode('-', $calendarMonth)[1] - 1 }},
                selectedDate: {{ $selectedDate ? "'".$selectedDate."'" : 'null' }},
                weekdays: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                pad(n) { return n.toString().padStart(2, '0'); },
                get monthLabel() {
                    return new Date(this.viewYear, this.viewMonth, 1).toLocaleString('en-US', { month: 'long', year: 'numeric' });
                },
                get daysGrid() {
                    const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay();
                    const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                    const cells = [];
                    for (let i = 0; i < firstDay; i++) cells.push(null);
                    for (let d = 1; d <= daysInMonth; d++) cells.push(d);
                    return cells;
                },
                prevMonth() { this.viewMonth--; if (this.viewMonth < 0) { this.viewMonth = 11; this.viewYear--; } },
                nextMonth() { this.viewMonth++; if (this.viewMonth > 11) { this.viewMonth = 0; this.viewYear++; } },
                dateKey(day) { return this.viewYear + '-' + this.pad(this.viewMonth + 1) + '-' + this.pad(day); },
                isToday(day) {
                    const t = new Date();
                    return t.getFullYear() === this.viewYear && t.getMonth() === this.viewMonth && t.getDate() === day;
                },
                isFuture(day) {
                    const t = new Date(); t.setHours(0, 0, 0, 0);
                    return new Date(this.viewYear, this.viewMonth, day) > t;
                },
                isSelected(day) { return this.selectedDate === this.dateKey(day); },
                get displayLabel() {
                    if (!this.selectedDate) return @js(__('Pick a date'));
                    const [y, m, d] = this.selectedDate.split('-').map(Number);
                    return new Date(y, m - 1, d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                },
                goTo(url) { window.location.href = url; },
            }"
            @click.outside="open = false"
            class="relative"
        >
            <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium border transition"
                    :class="selectedDate ? 'border-[#8A3330] text-[#8A3330] bg-[#F3E1DC]' : 'border-[#D9CCBA] text-gray-600 bg-white hover:bg-[#FAF6EE]'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                <span x-text="displayLabel"></span>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute z-50 mt-2 w-72 bg-white border border-[#E5DDD0] rounded-xl shadow-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <button type="button" @click="prevMonth()" class="p-1.5 rounded-md hover:bg-[#FAF6EE] text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>
                    <span class="text-sm font-semibold text-gray-900" x-text="monthLabel"></span>
                    <button type="button" @click="nextMonth()" class="p-1.5 rounded-md hover:bg-[#FAF6EE] text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold text-gray-400 mb-1">
                    <template x-for="wd in weekdays" :key="wd"><span x-text="wd"></span></template>
                </div>

                <div class="grid grid-cols-7 gap-y-1">
                    <template x-for="(day, idx) in daysGrid" :key="idx">
                        <div class="flex items-center justify-center">
                            <button x-show="day !== null" type="button"
                                    x-text="day"
                                    :disabled="isFuture(day)"
                                    @click="goTo('{{ route('superadmin.reports.index') }}?date=' + dateKey(day))"
                                    class="w-8 h-8 rounded-full text-sm transition"
                                    :class="{
                                        'bg-[#8A3330] text-white font-semibold': isSelected(day),
                                        'ring-1 ring-[#8A3330] text-[#8A3330] font-semibold': isToday(day) && !isSelected(day),
                                        'text-gray-300 cursor-not-allowed': isFuture(day),
                                        'text-gray-700 hover:bg-[#FAF6EE]': !isSelected(day) && !isToday(day) && !isFuture(day),
                                    }"
                            ></button>
                        </div>
                    </template>
                </div>

                <div class="mt-3 pt-3 border-t border-[#E5DDD0] flex items-center justify-between">
                    <button type="button" @click="goTo('{{ route('superadmin.reports.index') }}')" x-show="selectedDate"
                            class="text-xs text-gray-500 hover:text-gray-700">
                        {{ __('Clear') }}
                    </button>
                    <button type="button"
                            @click="goTo('{{ route('superadmin.reports.index') }}?month=' + viewYear + '-' + pad(viewMonth + 1))"
                            class="text-xs font-semibold text-[#8A3330] hover:underline ml-auto">
                        {{ __('View Entire Month') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:0ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Total Revenue') }}</div>
            <div class="mt-2 text-2xl font-bold text-[#8A3330]">₱{{ number_format($totalRevenue, 2) }}</div>
            <x-trend-badge :data="$comparison['totalRevenue'] ?? null" />
            <p class="mt-1 text-xs text-gray-400">{{ $rangeLabel }} &middot; {{ __('Paid orders, gross (before discounts)') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:80ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Total Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalOrders }}</div>
            <x-trend-badge :data="$comparison['totalOrders'] ?? null" />
            <p class="mt-1 text-xs text-gray-400">{{ __('Excludes cancelled') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:160ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Average Order Value') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">₱{{ number_format($averageOrderValue, 2) }}</div>
            <x-trend-badge :data="$comparison['averageOrderValue'] ?? null" />
            <p class="mt-1 text-xs text-gray-400">{{ __('Per paid order') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:240ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Cancelled Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $cancelledOrders }}</div>
            <x-trend-badge :data="$comparison['cancelledOrders'] ?? null" :invert="true" />
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
            @php
                $maxRevenue = $dailySales->max('revenue') ?: 1;
                $gridLines = [1, 0.75, 0.5, 0.25, 0];
            @endphp
            <div class="flex gap-3">
                {{-- Y-axis labels --}}
                <div class="flex flex-col justify-between h-48 pb-1 text-[10px] text-gray-400 shrink-0 text-right">
                    @foreach ($gridLines as $fraction)
                        <span>₱{{ number_format($maxRevenue * $fraction, 0) }}</span>
                    @endforeach
                </div>

                <div class="relative flex-1 min-w-0">
                    {{-- Gridlines --}}
                    <div class="absolute inset-0 pb-1">
                        @foreach ($gridLines as $fraction)
                            <div class="absolute left-0 right-0 border-t border-gray-100" style="bottom: {{ $fraction * 100 }}%"></div>
                        @endforeach
                    </div>

                    <div class="relative flex items-end gap-2 h-48 overflow-x-auto pb-1">
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
                </div>
            </div>
        @endif
    </div>

    {{-- Tax & discount summary — from persisted invoice snapshots, so this
         always matches what was actually shown on each invoice. --}}
    <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 mb-6 animate-fade-slide-up [animation-delay:360ms]">
        <h3 class="font-semibold text-gray-900">{{ __('Tax & Discount Summary') }}</h3>
        <p class="text-sm text-gray-500 mb-6">{{ __('By invoice date') }} &middot; {{ $rangeLabel }}</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Net Collected') }}</p>
                <p class="mt-1 text-lg font-bold text-[#8A3330]">₱{{ number_format($taxSummary['netAmountCollected'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('VATable Sales') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['vatableSales'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('VAT-Exempt Sales') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['vatExemptSales'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('VAT Amount') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['vatAmount'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Service Charges') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['serviceCharges'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Senior Discounts') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['seniorDiscounts'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('PWD Discounts') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['pwdDiscounts'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Promo Discounts') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">₱{{ number_format($taxSummary['promoDiscounts'], 2) }}</p>
            </div>
            <div>
                <p class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Voided Invoices') }}</p>
                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $taxSummary['voidedInvoices'] }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
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
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($bestSellers as $item)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $item->item_name }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600 text-right">{{ $item->total_qty }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($item->total_revenue, 2) }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-12 h-1.5 rounded-full bg-[#F3E1DC] overflow-hidden">
                                                <div class="h-full bg-[#8A3330] rounded-full" style="width: {{ min(100, $item->percent) }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500 w-9 text-right">{{ number_format($item->percent, 0) }}%</span>
                                        </div>
                                    </td>
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
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($categorySales as $category)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $category->category_name }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($category->total_revenue, 2) }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-12 h-1.5 rounded-full bg-[#F3E1DC] overflow-hidden">
                                                <div class="h-full bg-[#8A3330] rounded-full" style="width: {{ min(100, $category->percent) }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500 w-9 text-right">{{ number_format($category->percent, 0) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Sales by area --}}
        <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden animate-fade-slide-up [animation-delay:560ms]">
            <div class="px-6 py-4 border-b border-[#E5DDD0]">
                <h3 class="font-semibold text-gray-900">{{ __('Sales by Area') }}</h3>
            </div>
            @if ($areaSales->isEmpty())
                <p class="text-sm text-gray-400 px-6 py-8 text-center">{{ __('No area sales for this period.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5DDD0]">
                        <thead class="bg-[#FAF6EE]">
                            <tr>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Area') }}</th>
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Orders') }}</th>
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Revenue') }}</th>
                                <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($areaSales as $area)
                                <tr>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-900">{{ $area->area_name }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600 text-right">{{ $area->order_count }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($area->total_revenue, 2) }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="w-12 h-1.5 rounded-full bg-[#F3E1DC] overflow-hidden">
                                                <div class="h-full bg-[#8A3330] rounded-full" style="width: {{ min(100, $area->percent) }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-500 w-9 text-right">{{ number_format($area->percent, 0) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
