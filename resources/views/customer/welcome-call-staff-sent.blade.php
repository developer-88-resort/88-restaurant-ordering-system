<x-customer-layout>
    <div class="px-4 py-10 max-w-sm mx-auto text-center">
        <div class="bg-white border border-[#E5DDD0] rounded-2xl p-8">
            <div class="mx-auto h-14 w-14 rounded-full bg-green-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-7 w-7 text-green-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </div>
            <h1 class="mt-4 text-lg font-bold text-gray-900">{{ __('On the way!') }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ __('A staff member has been notified and will come to assist you shortly.') }}</p>

            <a href="{{ route('customer.welcome.show') }}" class="mt-6 inline-block w-full bg-[#8A3330] hover:bg-[#742927] text-white font-semibold rounded-xl py-3.5 transition">
                {{ __('Back to Start') }}
            </a>
        </div>
    </div>
</x-customer-layout>
