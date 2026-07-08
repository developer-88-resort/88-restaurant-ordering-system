<x-guest-layout>
    <div class="w-full max-w-sm text-center">

        <div class="flex flex-col items-center mb-8">
            @if (file_exists(public_path('images/logo.png')))
                <img src="{{ asset('images/logo.png') }}" alt="88 Hot Spring Resort" class="h-24 w-24 rounded-full object-cover mb-4">
            @endif

            <h1 class="text-2xl font-medium text-black" style="font-family: 'Playfair Display', serif;">
                88 Hot Spring Resort
            </h1>
        </div>

        <div class="bg-white border border-[#D9CCBA] rounded-xl p-8">
            <p class="text-sm text-[#8A7B6D] uppercase tracking-wide">{{ __('Table') }}</p>
            <p class="text-3xl font-bold text-[#8A3330] mt-1">{{ $table->table_number }}</p>

            <p class="mt-6 text-gray-700">
                {{ __('Menu ordering will be available here soon.') }}
            </p>
            <p class="mt-2 text-sm text-gray-500">
                {{ __('In the meantime, please ask our staff for assistance with your order.') }}
            </p>
        </div>

    </div>
</x-guest-layout>
