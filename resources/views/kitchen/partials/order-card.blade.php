@php
    $accentClasses = match ($accentColor ?? 'amber') {
        'blue' => ['button' => 'bg-blue-600 hover:bg-blue-700'],
        'purple' => ['button' => 'bg-purple-600 hover:bg-purple-700'],
        default => ['button' => 'bg-amber-600 hover:bg-amber-700'],
    };
@endphp

<div class="rounded-xl bg-white border border-[#E5DDD0] shadow-sm">
    <div class="px-5 pt-4 pb-3">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <span class="text-lg font-bold text-gray-900">#{{ str_pad((string) $order->id, 4, '0', STR_PAD_LEFT) }}</span>
                <p class="text-sm text-gray-500 truncate">
                    @if ($order->order_type === \App\Enums\OrderType::Takeout)
                        {{ __('Take-out') }}
                    @else
                        {{ $order->locationLabel() }}
                        <span class="text-gray-300">&bull;</span>
                        {{ $order->order_type->label() }}
                    @endif
                </p>
            </div>
            <div
                x-data="{
                    start: new Date('{{ $order->created_at->toIso8601String() }}').getTime(),
                    now: Date.now(),
                }"
                x-init="setInterval(() => now = Date.now(), 1000)"
                class="text-sm font-semibold text-gray-400 shrink-0"
                x-text="(() => {
                    const diff = Math.max(0, Math.floor((now - start) / 1000));
                    const m = Math.floor(diff / 60);
                    const s = diff % 60;
                    return m + ':' + String(s).padStart(2, '0');
                })()"
            ></div>
        </div>
    </div>

    <div class="border-t border-[#E5DDD0]"></div>

    <div class="px-5 py-4 space-y-1.5">
        @foreach ($order->items as $item)
            <div class="text-sm text-gray-800">
                <span class="font-semibold">{{ $item->quantity }}&times;</span>
                {{ $item->item_name }}
                @if ($item->notes)
                    <div class="pl-5 text-xs text-gray-400">{{ $item->notes }}</div>
                @endif
            </div>
        @endforeach

        @if ($order->notes)
            <div class="mt-2 text-xs bg-amber-50 text-amber-800 border border-amber-200 rounded-md px-2 py-1.5">
                {{ $order->notes }}
            </div>
        @endif
    </div>

    <div class="px-5 pb-5 flex gap-2">
        <form action="{{ route('orders.update-status', $order) }}" method="POST" class="flex-1">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ $nextStatus }}">
            <button type="submit" class="w-full {{ $accentClasses['button'] }} text-white text-xs font-bold uppercase tracking-wider rounded-lg py-3 transition">
                {{ $buttonLabel }}
            </button>
        </form>

        <x-confirm-form
            :action="route('orders.update-status', $order)"
            method="PATCH"
            class="shrink-0"
            :title="__('Cancel this order?')"
            :message="__('This cannot be undone.')"
            :confirm-label="__('Cancel Order')"
        >
            <input type="hidden" name="status" value="cancelled">
            <button type="submit" class="h-full px-4 border border-[#E5DDD0] text-gray-600 text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-gray-50 transition">
                {{ __('Cancel') }}
            </button>
        </x-confirm-form>
    </div>
</div>
