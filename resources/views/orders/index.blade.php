<x-app-layout>
    <x-slot name="header">
        <div class="bg-white border border-[#E5DDD0] rounded-2xl shadow-sm p-5 sm:p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 shrink-0 rounded-2xl bg-[#8A3330] flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="white" class="h-7 w-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-gray-900 leading-tight">{{ __('Order Management') }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ __('Monitor and manage all customer orders') }}</p>
                </div>
            </div>
            <a href="{{ route('orders.create') }}"
               class="inline-flex items-center justify-center gap-1.5 rounded-full bg-[#8A3330] hover:bg-[#742927] px-5 py-2.5 text-sm font-semibold text-white transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                {{ __('New Order') }}
            </a>
        </div>
    </x-slot>

    @php
        $filterIcon = fn (?string $status) => match ($status) {
            null => '<path stroke-linecap="round" stroke-linejoin="round" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122" />',
            'pending' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'preparing' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1.001A3.75 3.75 0 0012 18z" />',
            'ready' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />',
            'served' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />',
            'completed' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'cancelled' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        };
    @endphp

    <div class="bg-white border border-[#E5DDD0] rounded-2xl shadow-sm p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('orders.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold transition {{ is_null($activeStatus) ? 'bg-[#8A3330] text-white' : 'bg-white border border-[#E5DDD0] text-gray-600 hover:bg-[#FAF6EE]' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">{!! $filterIcon(null) !!}</svg>
                {{ __('All') }}
            </a>
            @foreach (\App\Enums\OrderStatus::cases() as $status)
                <a href="{{ route('orders.index', ['status' => $status->value]) }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold transition {{ $activeStatus === $status->value ? 'bg-[#8A3330] text-white' : 'bg-white border border-[#E5DDD0] text-gray-600 hover:bg-[#FAF6EE]' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">{!! $filterIcon($status->value) !!}</svg>
                    {{ $status->label() }}
                </a>
            @endforeach
        </div>
    </div>

    @if ($orders->isEmpty())
        <x-empty-state
            :title="__('No orders yet')"
            :description="__('Create an order for a walk-in customer to get started.')"
            :actionLabel="__('New Order')"
            :actionHref="route('orders.create')"
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-[#E5DDD0] rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Order') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Table') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Payment') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Total') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Created') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 rounded-full flex items-center justify-center {{ $order->status->badgeClasses() }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-gray-900 font-mono">{{ $order->orderNumber() }}</p>
                                            <p class="text-xs text-gray-400">{{ __('Order ID') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v4H4V6z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 10v8M17 10v8" />
                                        </svg>
                                        {{ $order->table->table_number }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold leading-5 rounded-full {{ $order->status->badgeClasses() }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $order->status->dotClasses() }}"></span>
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full {{ $order->payment_status->badgeClasses() }}">
                                        {{ $order->payment_status->label() }}
                                    </span>
                                    @if ($order->payment_method)
                                        <span class="ml-1 text-xs text-gray-400 uppercase">{{ $order->payment_method }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#8A3330]">₱{{ number_format($order->total_amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 text-sm text-gray-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-gray-400">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        {{ $order->created_at->format('M d, g:i A') }}
                                    </span>
                                    <p class="mt-1 text-xs text-gray-400">{{ $order->created_at->diffForHumans() }}</p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <a href="{{ route('orders.show', $order) }}"
                                       class="inline-flex items-center gap-1.5 rounded-full border border-[#E5DDD0] px-3.5 py-1.5 text-xs font-semibold text-[#8A3330] hover:bg-[#FAF6EE] transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="block bg-white border border-[#E5DDD0] rounded-2xl shadow-sm p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-gray-900 font-mono">{{ $order->orderNumber() }} <span class="font-sans font-normal text-gray-500">&middot; {{ $order->table->table_number }}</span></p>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full shrink-0 {{ $order->status->badgeClasses() }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $order->status->dotClasses() }}"></span>
                            {{ $order->status->label() }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span>
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full {{ $order->payment_status->badgeClasses() }}">
                                {{ $order->payment_status->label() }}
                            </span>
                            @if ($order->payment_method)
                                <span class="ml-1 text-xs text-gray-400 uppercase">{{ $order->payment_method }}</span>
                            @endif
                        </span>
                        <span class="font-semibold text-[#8A3330]">₱{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <p class="mt-2 text-xs text-gray-400">{{ $order->created_at->format('M d, Y g:i A') }} &middot; {{ $order->created_at->diffForHumans() }}</p>
                </a>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
</x-app-layout>
