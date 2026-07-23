<x-customer-layout>
    <div class="px-4 py-10 max-w-sm mx-auto text-center">
        <div class="bg-white border border-[#D9CCBA] rounded-xl p-8">
            <p class="text-sm text-[#8A7B6D] uppercase tracking-wide">{{ $space->area->name }}</p>
            <p class="text-3xl font-bold text-[#8A3330] mt-1">{{ $space->name }}</p>

            <p class="mt-6 text-gray-700">
                @if (! $space->area->is_active)
                    {{ __('This area is not currently accepting orders.') }}
                @else
                    {{ __(':name is currently :status.', ['name' => $space->name, 'status' => $space->status->label()]) }}
                @endif
            </p>
            <p class="mt-2 text-sm text-gray-500">
                {{ __('In the meantime, please ask our staff for assistance with your order.') }}
            </p>
        </div>
    </div>
</x-customer-layout>
