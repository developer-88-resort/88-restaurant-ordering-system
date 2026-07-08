<x-app-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Welcome back, :name', ['name' => Auth::user()->name]) }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __("Overview of today's restaurant operations.") }}</p>
    </div>

    {{-- Today's operations --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:0ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __("Today's Sales") }}</div>
            <div class="mt-2 text-2xl font-bold text-[#8A3330]">₱{{ number_format($todaysSales, 2) }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ __('Paid orders only') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:80ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Active Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $activeOrders }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ __('In progress right now') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:160ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Pending Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $pendingOrders }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ __('Awaiting kitchen') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:240ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Table Occupancy') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $occupiedTables }} <span class="text-base font-normal text-gray-400">/ {{ $totalTables }}</span></div>
            <p class="mt-1 text-xs text-gray-400">{{ __('Tables occupied') }}</p>
        </div>
    </div>

    {{-- Business snapshot --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:320ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Unpaid Orders') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $unpaidOrders }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ __('Needs collection') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:400ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Popular This Week') }}</div>
            <div class="mt-2 text-lg font-bold text-gray-900 truncate">{{ $popularThisWeek->item_name ?? '—' }}</div>
            <p class="mt-1 text-xs text-gray-400">{{ $popularThisWeek ? __(':qty sold', ['qty' => $popularThisWeek->total_qty]) : __('No sales yet') }}</p>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:480ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Admin Accounts') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $adminCount }}</div>
        </div>
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-5 animate-fade-slide-up [animation-delay:560ms]">
            <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Staff Accounts') }}</div>
            <div class="mt-2 text-2xl font-bold text-gray-900">{{ $staffCount }}</div>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden animate-fade-slide-up [animation-delay:640ms]">
        <div class="px-6 py-4 border-b border-[#E5DDD0] flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">{{ __('Recent Orders') }}</h3>
            <a href="{{ route('orders.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">{{ __('View All') }} &rarr;</a>
        </div>

        @if ($recentOrders->isEmpty())
            <p class="text-sm text-gray-400 px-6 py-8 text-center">{{ __('No orders yet.') }}</p>
        @else
            {{-- Desktop table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Order') }}</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Table') }}</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Total') }}</th>
                            <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($recentOrders as $order)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-3 text-sm font-mono font-medium text-gray-900">{{ $order->orderNumber() }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $order->table->table_number }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full {{ $order->status->badgeClasses() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-3 text-right text-sm">
                                    <a href="{{ route('orders.show', $order) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('View') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-[#E5DDD0]">
                @foreach ($recentOrders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="block px-4 py-3 hover:bg-[#FAF6EE]">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-mono font-medium text-gray-900">{{ $order->orderNumber() }}</p>
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full shrink-0 {{ $order->status->badgeClasses() }}">
                                {{ $order->status->label() }}
                            </span>
                        </div>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="text-xs text-gray-500">{{ $order->table->table_number }}</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
