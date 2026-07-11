@php
    $steps = [
        ['value' => 'pending', 'label' => __('Pending')],
        ['value' => 'preparing', 'label' => __('Preparing')],
        ['value' => 'ready', 'label' => __('Ready')],
        ['value' => 'served', 'label' => __('Served')],
    ];
@endphp

<x-customer-layout :location-label="$order->locationLabel()">
    <div
        x-data="{
            status: '{{ $order->status->value }}',
            currentStepIndex() {
                return ({ pending: 0, preparing: 1, ready: 2, served: 3, completed: 3 })[this.status] ?? 0;
            },
        }"
        x-init="Echo.channel('order.{{ $order->public_token }}').listen('.CustomerOrderStatusUpdated', (e) => { status = e.status; })"
        class="px-4 py-6 pb-10 max-w-2xl mx-auto"
    >
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 text-center">
            <p class="text-xs text-[#8A7B9E] uppercase tracking-wide">{{ __('Order') }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $order->orderNumber() }}</p>
        </div>

        {{-- Cancelled banner --}}
        <div x-show="status === 'cancelled'" x-cloak class="mt-6 bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <p class="font-semibold text-red-800">{{ __('This order was cancelled.') }}</p>
            <p class="text-sm text-red-700 mt-1">{{ __('Please ask our staff if you have any questions.') }}</p>
        </div>

        {{-- Progress indicator --}}
        <div class="mt-6 bg-white border border-[#E5DDD0] rounded-xl p-6" x-show="status !== 'cancelled'">
            <div class="flex items-start">
                @foreach ($steps as $i => $step)
                    <div class="flex-1 flex flex-col items-center relative">
                        @if (! $loop->last)
                            <div class="absolute top-4 left-1/2 w-full h-0.5"
                                 :class="{{ $i }} < currentStepIndex() ? 'bg-[#8A3330]' : 'bg-gray-200'"></div>
                        @endif
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-xs font-bold z-10 transition-colors"
                             :class="{{ $i }} <= currentStepIndex() ? 'bg-[#8A3330] text-white' : 'bg-gray-100 text-gray-400'">
                            {{ $i + 1 }}
                        </div>
                        <span class="mt-2 text-xs font-medium text-center transition-colors"
                              :class="{{ $i }} <= currentStepIndex() ? 'text-[#8A3330]' : 'text-gray-400'">
                            {{ $step['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Items --}}
        <div class="mt-6 bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-[#E5DDD0]">
                <h3 class="font-semibold text-gray-900">{{ __('Order Details') }}</h3>
            </div>
            <div class="divide-y divide-[#E5DDD0]">
                @foreach ($order->items as $item)
                    <div class="px-6 py-3 flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-gray-900">
                            <span class="font-semibold">{{ $item->quantity }}&times;</span> {{ $item->item_name }}
                        </p>
                        <span class="text-sm font-semibold text-gray-900 shrink-0">₱{{ number_format($item->subtotal, 2) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="px-6 py-4 bg-[#FAF6EE] flex items-center justify-between">
                <span class="font-semibold text-gray-900">{{ __('Total') }}</span>
                <span class="text-lg font-bold text-[#8A3330]">₱{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        @if ($order->notes)
            <div class="mt-4 text-sm bg-amber-50 text-amber-800 border border-amber-200 rounded-md px-4 py-3">
                {{ $order->notes }}
            </div>
        @endif
    </div>
</x-customer-layout>
