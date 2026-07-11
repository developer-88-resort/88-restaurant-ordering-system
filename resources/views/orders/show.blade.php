<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight font-mono">
                {{ $order->orderNumber() }}
            </h2>
            <a href="{{ route('orders.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                {{ __('Back to Orders') }}
            </a>
        </div>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        {{-- Items --}}
        <div class="flex-1 w-full">
            <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E5DDD0]">
                        <thead class="bg-[#FAF6EE]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Item') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Qty') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $item->item_name }}
                                        @if ($item->notes)
                                            <p class="text-xs text-gray-500 font-normal mt-0.5">{{ $item->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-600">{{ $item->quantity }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-600">₱{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">₱{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-[#FAF6EE]">
                            <tr>
                                <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-900">{{ __('Total') }}</td>
                                <td class="px-6 py-3 text-right text-base font-bold text-[#8A3330]">₱{{ number_format($order->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @if ($order->notes)
                <div class="mt-6 bg-white border border-[#E5DDD0] rounded-xl p-6">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Order Notes') }}</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Details / actions --}}
        <div class="w-full lg:w-80 shrink-0 space-y-6">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 space-y-4">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Location') }}</p>
                    <p class="text-sm font-medium text-gray-900">{{ $order->locationLabel() }}</p>
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E] mb-1">{{ __('Order Status') }}</p>
                    @if ($order->status->isFinal())
                        <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $order->status->badgeClasses() }}">
                            {{ $order->status->label() }}
                        </span>
                    @else
                        <form action="{{ route('orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" onchange="this.form.submit()"
                                    class="w-full text-xs font-semibold rounded-full px-3 py-1.5 border-0 focus:ring-2 focus:ring-[#8A3330] {{ $order->status->badgeClasses() }}">
                                @foreach (\App\Enums\OrderStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($order->status === $status)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>

                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E] mb-1">{{ __('Payment') }}</p>
                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full {{ $order->payment_status->badgeClasses() }}">
                        {{ $order->payment_status->label() }}
                    </span>
                    @if ($order->payment_method)
                        <span class="ml-1 text-xs text-gray-400 uppercase">{{ $order->payment_method }}</span>
                    @endif

                    @if ($order->payment_status === \App\Enums\PaymentStatus::Paid)
                        @if ($order->paid_at)
                            <p class="mt-1 text-xs text-gray-400">{{ __('Paid on') }} {{ $order->paid_at->format('M d, Y g:i A') }}</p>
                        @endif
                        @if ($order->amount_received !== null)
                            <dl class="mt-3 space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ __('Amount Received') }}</dt>
                                    <dd class="text-gray-900">₱{{ number_format($order->amount_received, 2) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">{{ __('Change Due') }}</dt>
                                    <dd class="text-gray-900">₱{{ number_format($order->change_amount, 2) }}</dd>
                                </div>
                            </dl>
                        @endif
                        @if ($order->receipt_number)
                            <a href="{{ route('orders.receipt', $order) }}" class="mt-3 inline-block text-sm text-[#8A3330] hover:underline font-medium">
                                {{ __('View Receipt') }} ({{ $order->receipt_number }})
                            </a>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('orders.void-payment', $order) }}"
                            x-data="{ open: false, reason: '' }"
                            @submit.prevent="reason.trim().length > 0 && (open = true)"
                            class="mt-3"
                        >
                            @csrf
                            @method('PATCH')

                            <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">
                                {{ __('Void Reason') }}
                            </label>
                            <textarea
                                name="void_reason" x-model="reason" required rows="2"
                                placeholder="{{ __('e.g. Customer cancelled order') }}"
                                class="mt-1 w-full text-sm rounded-lg border-[#E5DDD0] focus:border-red-500 focus:ring-red-500"
                            ></textarea>

                            <button type="submit" class="mt-2 text-sm text-red-600 hover:underline font-medium">
                                {{ __('Void Payment') }}
                            </button>

                            <dialog
                                x-ref="dialog"
                                x-effect="open ? $refs.dialog.showModal() : $refs.dialog.close()"
                                @cancel="open = false"
                                @click="$event.target === $refs.dialog && (open = false)"
                                class="rounded-xl border border-[#E5DDD0] p-0 backdrop:bg-black/40 max-w-sm w-[calc(100%-2rem)] m-auto"
                            >
                                <div class="p-6">
                                    <h3 class="font-semibold text-gray-900">{{ __('Void this payment?') }}</h3>
                                    <p class="mt-2 text-sm text-gray-600">{{ __('This will mark the payment as Voided. The order can be paid again afterwards.') }}</p>
                                    <p class="mt-2 text-sm text-gray-500 italic" x-text="reason"></p>
                                    <div class="mt-6 flex justify-end gap-3">
                                        <button type="button" @click="open = false" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                            {{ __('Cancel') }}
                                        </button>
                                        <button type="button" @click="open = false; $root.submit()"
                                                class="text-sm font-medium rounded-md px-4 py-2 bg-red-600 hover:bg-red-700 text-white">
                                            {{ __('Void Payment') }}
                                        </button>
                                    </div>
                                </div>
                            </dialog>
                        </form>
                    @else
                        @if ($order->payment_status === \App\Enums\PaymentStatus::Voided)
                            <dl class="text-xs space-y-1">
                                <div class="flex justify-between"><dt class="text-gray-500">{{ __('Void By') }}</dt><dd class="text-gray-700">{{ $order->voidedBy->name ?? __('Unknown') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">{{ __('Void Date') }}</dt><dd class="text-gray-700">{{ $order->voided_at?->format('M d, Y g:i A') }}</dd></div>
                                <div class="flex justify-between gap-2"><dt class="text-gray-500 shrink-0">{{ __('Reason') }}</dt><dd class="text-gray-700 text-right">{{ $order->void_reason }}</dd></div>
                            </dl>
                            @if ($order->receipt_number)
                                <a href="{{ route('orders.receipt', $order) }}" class="mt-2 inline-block text-sm text-[#8A3330] hover:underline font-medium">
                                    {{ __('View Voided Receipt') }} ({{ $order->receipt_number }})
                                </a>
                            @endif
                            <p class="mt-1 text-xs text-gray-400">{{ __('You can accept payment again below.') }}</p>
                        @endif

                        <div class="mt-3">
                            <form
                                method="POST"
                                action="{{ route('orders.mark-as-paid', $order) }}"
                                x-data="{ open: false, amountReceived: {{ $order->total_amount }} }"
                                @submit.prevent="open = true"
                            >
                                @csrf
                                @method('PATCH')

                                <label class="block text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">
                                    {{ __('Amount Received') }}
                                </label>
                                <input
                                    type="number" step="0.01" min="{{ $order->total_amount }}"
                                    name="amount_received" x-model.number="amountReceived" required
                                    class="mt-1 w-full text-sm rounded-lg border-[#E5DDD0] focus:border-[#8A3330] focus:ring-[#8A3330]"
                                >

                                <button type="submit" class="mt-3 w-full text-sm font-medium rounded-md px-4 py-2 bg-[#8A3330] hover:bg-[#742927] text-white">
                                    {{ __('Mark as Paid (Cash)') }}
                                </button>

                                <dialog
                                    x-ref="dialog"
                                    x-effect="open ? $refs.dialog.showModal() : $refs.dialog.close()"
                                    @cancel="open = false"
                                    @click="$event.target === $refs.dialog && (open = false)"
                                    class="rounded-xl border border-[#E5DDD0] p-0 backdrop:bg-black/40 max-w-sm w-[calc(100%-2rem)] m-auto"
                                >
                                    <div class="p-6">
                                        <h3 class="font-semibold text-gray-900">{{ __('Confirm Cash Payment') }}</h3>
                                        <dl class="mt-4 space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <dt class="text-gray-500">{{ __('Total') }}</dt>
                                                <dd class="font-medium text-gray-900">₱{{ number_format($order->total_amount, 2) }}</dd>
                                            </div>
                                            <div class="flex justify-between">
                                                <dt class="text-gray-500">{{ __('Amount Received') }}</dt>
                                                <dd class="font-medium text-gray-900">₱<span x-text="amountReceived.toFixed(2)"></span></dd>
                                            </div>
                                            <div class="flex justify-between pt-2 border-t border-dashed border-[#D9CCBA]">
                                                <dt class="text-gray-500">{{ __('Change Due') }}</dt>
                                                <dd class="font-semibold text-[#8A3330]">₱<span x-text="(amountReceived - {{ $order->total_amount }}).toFixed(2)"></span></dd>
                                            </div>
                                        </dl>
                                        <div class="mt-6 flex justify-end gap-3">
                                            <button type="button" @click="open = false" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                                {{ __('Cancel') }}
                                            </button>
                                            <button type="button" @click="open = false; $root.submit()"
                                                    class="text-sm font-medium rounded-md px-4 py-2 bg-[#8A3330] hover:bg-[#742927] text-white">
                                                {{ __('Confirm Payment') }}
                                            </button>
                                        </div>
                                    </div>
                                </dialog>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-dashed border-[#D9CCBA] space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Created By') }}</p>
                        <p class="text-sm text-gray-700">{{ $order->creator->name ?? __('Unknown') }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Created At') }}</p>
                        <p class="text-sm text-gray-700">{{ $order->created_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
