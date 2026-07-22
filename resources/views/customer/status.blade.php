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
            paymentStatus: '{{ $order->payment_status->value }}',
            hasReceipt: {{ $order->receipt_number ? 'true' : 'false' }},
            receiptUrl: '{{ route('customer.orders.receipt', $order->public_token) }}',
            toasts: [],
            paymentMeta: {
                unpaid: { label: @js(__('Unpaid')), dot: 'bg-red-500' },
                paid: { label: @js(__('Paid')), dot: 'bg-green-500' },
                voided: { label: @js(__('Payment Voided')), dot: 'bg-gray-500' },
            },
            currentStepIndex() {
                return ({ pending: 0, preparing: 1, ready: 2, served: 3, completed: 3 })[this.status] ?? 0;
            },
            get paymentLabel() {
                return this.paymentMeta[this.paymentStatus]?.label ?? this.paymentStatus;
            },
            get paymentDotClass() {
                return this.paymentMeta[this.paymentStatus]?.dot ?? 'bg-gray-400';
            },
            pushToast(message) {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, message });
                setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 5000);
            },
            onUpdate(e) {
                if (this.paymentStatus !== 'paid' && e.payment_status === 'paid') {
                    this.pushToast(@js(__('Payment received. Thank you!')));
                }
                this.status = e.status;
                this.paymentStatus = e.payment_status;
                this.hasReceipt = e.has_receipt;
            },
        }"
        x-init="Echo.channel('order.{{ $order->public_token }}').listen('.CustomerOrderStatusUpdated', (e) => onUpdate(e))"
        class="px-4 py-6 pb-10 max-w-2xl mx-auto"
    >
        {{-- Live toasts --}}
        <div class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-4 z-[60] flex flex-col gap-3 sm:w-96">
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    x-transition:enter="transition ease-out duration-[400ms]"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-300"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="flex items-start gap-3 rounded-xl border border-[#E5DDD0] bg-white pl-4 pr-3 py-3.5 shadow-xl"
                >
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-4 w-4 text-green-600">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                    </div>
                    <p class="flex-1 pt-1 text-sm font-medium text-gray-800" x-text="toast.message"></p>
                </div>
            </template>
        </div>

        <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 text-center">
            <p class="text-xs text-[#8A7B9E] uppercase tracking-wide">{{ __('Order') }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $order->orderNumber() }}</p>
        </div>

        {{-- Cancelled banner --}}
        <div x-show="status === 'cancelled'" x-cloak class="mt-6 bg-red-50 border border-red-200 rounded-xl p-6 text-center">
            <p class="font-semibold text-red-800">{{ __('This order was cancelled.') }}</p>
            <p class="text-sm text-red-700 mt-1">{{ __('Please ask our staff if you have any questions.') }}</p>
        </div>

        {{-- Completed / thank-you banner --}}
        <div x-show="status === 'completed'" x-cloak class="mt-6 bg-[#F3E1DC] border border-[#E5C9BE] rounded-xl p-6 text-center">
            <div class="mx-auto h-10 w-10 rounded-full bg-[#8A3330] flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="white" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <p class="font-semibold text-[#8A3330] mt-3">{{ __('Thank you for dining with us!') }}</p>
            <p class="text-sm text-[#8A3330]/80 mt-1">{{ __('We hope you enjoyed your meal at 88 Hot Spring Resort. See you again soon!') }}</p>

            @if ($order->space)
                <a href="{{ route('customer.spaces.show', $order->space) }}"
                   class="inline-flex items-center justify-center mt-5 px-5 py-2.5 rounded-lg font-semibold text-white bg-[#8A3330] hover:bg-[#742927] transition">
                    {{ __('Order Again') }}
                </a>
            @endif
        </div>

        {{-- Progress indicator --}}
        <div class="mt-6 bg-white border border-[#E5DDD0] rounded-xl p-6" x-show="status !== 'cancelled' && status !== 'completed'">
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
                            @if ($item->notes)
                                <span class="block text-xs text-gray-400 italic font-normal mt-0.5">{{ $item->notes }}</span>
                            @endif
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

        {{-- Payment status --}}
        <div class="mt-4 bg-white border border-[#E5DDD0] rounded-xl px-6 py-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full" :class="paymentDotClass"></span>
                <span class="text-sm font-medium text-gray-700" x-text="paymentLabel"></span>
            </div>
            <a x-show="hasReceipt" x-cloak :href="receiptUrl" class="text-sm font-semibold text-[#8A3330] hover:underline">
                {{ __('View Receipt') }}
            </a>
        </div>

        @if ($order->notes)
            <div class="mt-4 text-sm bg-amber-50 text-amber-800 border border-amber-200 rounded-md px-4 py-3">
                {{ $order->notes }}
            </div>
        @endif
    </div>
</x-customer-layout>
