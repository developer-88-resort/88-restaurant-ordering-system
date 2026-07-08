@php
    $itemColors = ['#E85D9C', '#4FA8E8', '#F2A93B', '#5FBF7A', '#B892E8'];
    $shortCode = strtoupper(substr(md5($order->id.$order->created_at), 0, 6));
@endphp

<div class="relative overflow-hidden rounded-xl bg-white border border-[#E5DDD0] text-[#2A241C] font-mono shadow-sm">
    {{-- Ticket notches --}}
    <span class="absolute -left-2.5 top-[52px] h-5 w-5 rounded-full bg-[#F7F0E3] border border-[#E5DDD0]"></span>
    <span class="absolute -right-2.5 top-[52px] h-5 w-5 rounded-full bg-[#F7F0E3] border border-[#E5DDD0]"></span>

    <div class="px-5 pt-4 pb-3">
        <div class="flex items-start justify-between gap-2">
            <span class="text-2xl font-bold tracking-tight">{{ strtoupper($order->table->table_number) }}</span>
            <div class="text-right">
                <div class="text-[11px] text-[#8A7B6D]">#{{ $shortCode }}</div>
                <div
                    x-data="{
                        start: new Date('{{ $order->created_at->toIso8601String() }}').getTime(),
                        now: Date.now(),
                    }"
                    x-init="setInterval(() => now = Date.now(), 1000)"
                    class="text-sm font-semibold"
                    x-text="(() => {
                        const diff = Math.max(0, Math.floor((now - start) / 1000));
                        const m = Math.floor(diff / 60);
                        const s = diff % 60;
                        return m + ':' + String(s).padStart(2, '0');
                    })()"
                ></div>
            </div>
        </div>
    </div>

    <div class="border-t-2 border-dashed border-[#D9CCBA]"></div>

    <div class="px-5 py-4 space-y-1.5">
        @foreach ($order->items as $index => $item)
            <div>
                <span class="font-bold">{{ $item->quantity }}&times;</span>
                <span class="font-semibold" style="color: {{ $itemColors[$index % count($itemColors)] }}">{{ $item->item_name }}</span>
                @if ($item->notes)
                    <div class="pl-5 text-xs text-[#8A7B6D]">{{ $item->notes }}</div>
                @endif
            </div>
        @endforeach

        @if ($order->notes)
            <div class="mt-2 text-xs bg-amber-100 text-amber-900 border border-amber-300 rounded-md px-2 py-1.5">
                {{ $order->notes }}
            </div>
        @endif
    </div>

    <div class="px-5 pb-5 flex gap-2">
        <form action="{{ route('orders.update-status', $order) }}" method="POST" class="flex-1">
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" value="{{ $nextStatus }}">
            <button type="submit" class="w-full bg-[#1A1712] hover:bg-black text-[#F5F0E6] text-xs font-bold uppercase tracking-wider rounded-lg py-3 transition">
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
            <button type="submit" class="h-full px-4 border border-[#2A241C]/30 text-[#2A241C] text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-[#2A241C]/5 transition">
                {{ __('Cancel') }}
            </button>
        </x-confirm-form>
    </div>
</div>
