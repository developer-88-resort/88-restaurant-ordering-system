<x-app-layout>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Welcome back, :name', ['name' => Auth::user()->name]) }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __("Overview of today's restaurant operations.") }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-[#F3E1DC] text-[#8A3330]">
            {{ now()->translatedFormat('l, F j, Y') }}
        </span>
    </div>

    {{-- Today's operations --}}
    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">{{ __("Today's Operations") }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:0ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __("Today's Sales") }}</div>
                    <div class="mt-2 text-2xl font-bold text-[#8A3330] truncate">₱{{ number_format($todaysSales, 2) }}</div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#8A3330] text-white flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('Paid orders only') }}</p>
        </div>

        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:80ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Active Orders') }}</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $activeOrders }}</div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('In progress right now') }}</p>
        </div>

        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:160ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Pending Orders') }}</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $pendingOrders }}</div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('Awaiting kitchen') }}</p>
        </div>

        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:240ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Space Occupancy') }}</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $occupiedSpaces }} <span class="text-base font-normal text-gray-400">/ {{ $totalSpaces }}</span></div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('Spaces occupied') }}</p>
        </div>
    </div>

    {{-- Business snapshot --}}
    <h2 class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-3">{{ __('Business Snapshot') }}</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:320ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Unpaid Orders') }}</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $unpaidOrders }}</div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('Needs collection') }}</p>
        </div>

        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:400ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Popular This Week') }}</div>
                    <div class="mt-2 text-lg font-bold text-gray-900 truncate">
                        @if ($popularThisWeek)
                            {{ $popularThisWeek->category_name ?? __('Uncategorized') }} — {{ $popularThisWeek->item_name }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">
                {{ $popularThisWeek ? __(':qty sold', ['qty' => $popularThisWeek->total_qty]) : __('No sales yet') }}
            </p>
        </div>

        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:480ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Admin Accounts') }}</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $adminCount }}</div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('With portal access') }}</p>
        </div>

        <div class="group bg-white border border-[#E5DDD0] rounded-xl p-5 shadow-sm hover:shadow-md hover:border-[#8A3330]/30 hover:-translate-y-0.5 transition-all duration-200 animate-fade-slide-up [animation-delay:560ms]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-mono text-[11px] uppercase tracking-wider text-[#8A7B9E]">{{ __('Staff Accounts') }}</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ $staffCount }}</div>
                </div>
                <div class="h-10 w-10 rounded-lg bg-[#F3E1DC] text-[#8A3330] flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 pt-3 border-t border-[#E5DDD0]/70 text-xs text-gray-400">{{ __('With portal access') }}</p>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden shadow-sm animate-fade-slide-up [animation-delay:640ms]">
        <div class="px-6 py-4 border-b border-[#E5DDD0] flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">{{ __('Recent Orders') }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ __('Latest activity across all locations') }}</p>
            </div>
            <a href="{{ route('orders.index') }}" class="text-sm text-[#8A3330] hover:text-[#5f2120] font-medium whitespace-nowrap">{{ __('View All') }} &rarr;</a>
        </div>

        @if ($recentOrders->isEmpty())
            <x-empty-state
                :title="__('No orders yet')"
                :description="__('Orders placed by your team will show up here as they come in.')"
                :actionLabel="__('New Order')"
                :actionHref="route('orders.create')"
            />
        @else
            {{-- Desktop table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Order') }}</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Location') }}</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Placed') }}</th>
                            <th class="px-6 py-2.5 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Total') }}</th>
                            <th class="px-6 py-2.5 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($recentOrders as $order)
                            <tr class="hover:bg-[#FAF6EE] transition-colors">
                                <td class="px-6 py-3 text-sm font-mono font-medium text-gray-900">{{ $order->orderNumber() }}</td>
                                <td class="px-6 py-3 text-sm text-gray-600">{{ $order->locationLabel() }}</td>
                                <td class="px-6 py-3 text-sm text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full {{ $order->status->badgeClasses() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-3 text-right text-sm">
                                    <a href="{{ route('orders.show', $order) }}" class="text-[#8A3330] hover:text-[#5f2120] font-medium">{{ __('View') }}</a>
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
                            <span class="text-xs text-gray-500">{{ $order->locationLabel() }}</span>
                            <span class="text-sm font-semibold text-gray-900">₱{{ number_format($order->total_amount, 2) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
