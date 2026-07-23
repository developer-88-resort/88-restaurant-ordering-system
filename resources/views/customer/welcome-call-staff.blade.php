<x-customer-layout>
    <div class="px-4 py-10 max-w-sm mx-auto text-center">
        <div class="bg-white border border-[#E5DDD0] rounded-2xl p-8">
            <div class="mx-auto h-14 w-14 rounded-2xl bg-[#F3E1DC] flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-7 w-7">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
            </div>
            <h1 class="mt-4 text-lg font-bold text-gray-900">{{ __('Call a Staff?') }}</h1>
            <p class="mt-2 text-sm text-gray-500">{{ __("We'll let our staff know you need help right away.") }}</p>

            <form method="POST" action="{{ route('customer.welcome.call-staff.send') }}" class="mt-6">
                @csrf
                <input type="hidden" name="customer_name" value="{{ $customerName }}">
                <button type="submit" class="w-full bg-[#8A3330] hover:bg-[#742927] text-white font-semibold rounded-xl py-3.5 transition">
                    {{ __('Yes, Call a Staff Now') }}
                </button>
            </form>

            <a href="{{ route('customer.welcome.show') }}" class="mt-4 inline-block text-sm text-gray-400 hover:text-gray-600">
                {{ __('Cancel') }}
            </a>
        </div>
    </div>
</x-customer-layout>
