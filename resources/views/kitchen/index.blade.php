<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between"
             x-data="{ connected: false }"
             x-init="
                Echo.private('kitchen').listen('.KitchenUpdated', () => window.location.reload());
                Echo.connector.pusher.connection.bind('connected', () => connected = true);
                Echo.connector.pusher.connection.bind('disconnected', () => connected = false);
                Echo.connector.pusher.connection.bind('unavailable', () => connected = false);
             ">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                {{ __('Kitchen Display') }}
                <span class="h-2 w-2 rounded-full" :class="connected ? 'bg-green-500' : 'bg-gray-300'" :title="connected ? '{{ __('Live') }}' : '{{ __('Reconnecting…') }}'"></span>
            </h2>
            <a href="{{ route('kitchen.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                {{ __('Refresh') }}
            </a>
        </div>
    </x-slot>

    @if ($newCount > 0)
        <div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 mb-6">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-700">{{ __('New') }}</span>
            <span class="h-6 w-6 flex items-center justify-center rounded-full bg-amber-500 text-white text-xs font-bold">{{ $newCount }}</span>
        </div>
    @endif

    <div x-data x-init="setInterval(() => window.location.reload(), 60000)" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Pending --}}
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                <h3 class="font-semibold text-gray-900">{{ __('Pending') }}</h3>
                <span class="text-xs text-gray-400">({{ $pending->count() }})</span>
            </div>
            <div class="space-y-4">
                @forelse ($pending as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'nextStatus' => 'preparing',
                        'buttonLabel' => __('Start Preparing'),
                    ])
                @empty
                    <p class="text-sm text-gray-400 text-center py-10 border border-dashed border-[#D9CCBA] rounded-xl">{{ __('No pending orders.') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Preparing --}}
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                <h3 class="font-semibold text-gray-900">{{ __('Preparing') }}</h3>
                <span class="text-xs text-gray-400">({{ $preparing->count() }})</span>
            </div>
            <div class="space-y-4">
                @forelse ($preparing as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'nextStatus' => 'ready',
                        'buttonLabel' => __('Mark Ready'),
                    ])
                @empty
                    <p class="text-sm text-gray-400 text-center py-10 border border-dashed border-[#D9CCBA] rounded-xl">{{ __('Nothing being prepared.') }}</p>
                @endforelse
            </div>
        </div>

        {{-- Ready --}}
        <div>
            <div class="flex items-center gap-2 mb-4">
                <span class="h-2.5 w-2.5 rounded-full bg-purple-500"></span>
                <h3 class="font-semibold text-gray-900">{{ __('Ready') }}</h3>
                <span class="text-xs text-gray-400">({{ $ready->count() }})</span>
            </div>
            <div class="space-y-4">
                @forelse ($ready as $order)
                    @include('kitchen.partials.order-card', [
                        'order' => $order,
                        'nextStatus' => 'served',
                        'buttonLabel' => __('Mark Served'),
                    ])
                @empty
                    <p class="text-sm text-gray-400 text-center py-10 border border-dashed border-[#D9CCBA] rounded-xl">{{ __('Nothing ready yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
