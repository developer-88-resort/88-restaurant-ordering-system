<x-customer-layout>
    <div
        x-data="{ step: 'name', name: '' }"
        class="px-4 py-10 max-w-md mx-auto min-h-[calc(100vh-8rem)] flex flex-col justify-center"
    >
        {{-- Step 1: name --}}
        <div x-show="step === 'name'" x-cloak>
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 rounded-2xl bg-[#F3E1DC] flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-8 w-8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
                    </svg>
                </div>
                <h1 class="mt-4 text-xl font-bold text-gray-900">{{ __('Welcome to :resort!', ['resort' => \App\Models\Setting::current()->resort_name]) }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __("What's your name?") }}</p>
            </div>

            <form @submit.prevent="name.trim().length > 0 && (step = 'tiles')">
                <input
                    type="text" x-model="name" required autofocus
                    placeholder="{{ __('Enter your name') }}"
                    class="w-full text-center text-lg rounded-xl border-[#D9CCBA] focus:border-[#8A3330] focus:ring-[#8A3330] py-3.5"
                >
                <button type="submit"
                        class="mt-4 w-full bg-[#8A3330] hover:bg-[#742927] text-white font-semibold rounded-xl py-3.5 transition">
                    {{ __('Continue') }}
                </button>
            </form>
        </div>

        {{-- Step 2: tiles --}}
        <div x-show="step === 'tiles'" x-cloak>
            <div class="text-center mb-6">
                <h1 class="text-xl font-bold text-gray-900">{{ __('Hi') }} <span x-text="name"></span>!</h1>
                <p class="mt-1 text-sm text-gray-500">{{ __('What would you like to do?') }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                {{-- Order Food (takeout) --}}
                <a :href="'{{ route('customer.welcome.takeout') }}?name=' + encodeURIComponent(name)"
                   class="group flex flex-col items-center justify-center gap-3 aspect-square rounded-3xl bg-white border-2 border-[#E5DDD0] hover:border-[#8A3330] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 p-4">
                    <div class="h-14 w-14 rounded-2xl bg-[#F3E1DC] group-hover:bg-[#8A3330] flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-[#8A3330] group-hover:text-white transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800 text-center">{{ __('Order Food') }}</span>
                </a>

                {{-- Choose a Seat --}}
                <a :href="'{{ route('customer.welcome.seats') }}?name=' + encodeURIComponent(name)"
                   class="group flex flex-col items-center justify-center gap-3 aspect-square rounded-3xl bg-white border-2 border-[#E5DDD0] hover:border-[#8A3330] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 p-4">
                    <div class="h-14 w-14 rounded-2xl bg-[#F3E1DC] group-hover:bg-[#8A3330] flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-[#8A3330] group-hover:text-white transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25a2.25 2.25 0 01-2.25-2.25v-2.25z" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800 text-center">{{ __('Choose a Seat') }}</span>
                </a>

                {{-- Call a Staff --}}
                <a :href="'{{ route('customer.welcome.call-staff.form') }}?name=' + encodeURIComponent(name)"
                   class="group flex flex-col items-center justify-center gap-3 aspect-square rounded-3xl bg-white border-2 border-[#E5DDD0] hover:border-[#8A3330] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 p-4">
                    <div class="h-14 w-14 rounded-2xl bg-[#F3E1DC] group-hover:bg-[#8A3330] flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-[#8A3330] group-hover:text-white transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800 text-center">{{ __('Call a Staff') }}</span>
                </a>

                {{-- View Menu Only --}}
                <a :href="'{{ route('customer.welcome.menu') }}?name=' + encodeURIComponent(name)"
                   class="group flex flex-col items-center justify-center gap-3 aspect-square rounded-3xl bg-white border-2 border-[#E5DDD0] hover:border-[#8A3330] hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 p-4">
                    <div class="h-14 w-14 rounded-2xl bg-[#F3E1DC] group-hover:bg-[#8A3330] flex items-center justify-center transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-7 w-7 text-[#8A3330] group-hover:text-white transition-colors">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-800 text-center">{{ __('View Menu') }}</span>
                </a>
            </div>

            <button type="button" @click="step = 'name'" class="mt-6 text-sm text-gray-400 hover:text-gray-600 mx-auto block">
                &larr; {{ __('Back') }}
            </button>
        </div>
    </div>
</x-customer-layout>
