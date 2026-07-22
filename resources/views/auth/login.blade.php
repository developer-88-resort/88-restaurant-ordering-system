<x-guest-layout>
    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            @if (file_exists(public_path('images/logo.png')))
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="88 Hot Spring Resort"
                    class="h-28 w-auto object-contain mb-4"
                >
            @else
                <div class="h-28 w-28 rounded-full bg-[#8A3330] flex items-center justify-center mb-4">
                    <span class="text-white text-3xl font-semibold" style="font-family: 'Playfair Display', serif;">88</span>
                </div>
            @endif

            <h1
                class="text-[29px] font-medium text-center text-black leading-none"
                style="font-family: 'Playfair Display', serif;"
            >
                88 Hot Spring Resort
            </h1>

            <p class="text-[#6F6258] text-lg mt-1 font-normal">
                {{ __('Management Portal') }}
            </p>
        </div>

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <input
                id="email"
                type="email"
                name="email"
                placeholder="{{ __('Email') }}"
                autocomplete="username"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full mb-4 rounded-xl border border-[#D9CCBA] bg-white px-4 py-3 text-sm text-[#333] placeholder:text-gray-400 outline-none focus:border-[#8A3330] focus:ring-2 focus:ring-[#8A3330]"
            >

            <div class="relative mb-4" x-data="{ showPassword: false }">
                <input
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    placeholder="{{ __('Password') }}"
                    autocomplete="current-password"
                    required
                    class="w-full rounded-xl border border-[#D9CCBA] bg-white px-4 py-3 pr-11 text-sm text-[#333] placeholder:text-gray-400 outline-none focus:border-[#8A3330] focus:ring-2 focus:ring-[#8A3330]"
                >
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-[#8A3330] transition-colors"
                    :aria-label="showPassword ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                >
                    <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                    </svg>
                </button>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
                    <p class="text-sm text-red-700">
                        {{ $errors->first() }}
                    </p>
                </div>
            @endif

            <button
                type="submit"
                class="w-full rounded-xl bg-[#8A3330] hover:bg-[#742927] text-white font-semibold py-3 transition duration-200"
            >
                {{ __('Sign in') }}
            </button>

            <p class="mt-4 text-center text-sm">
                <a href="{{ route('password.request') }}" class="text-[#8A3330] hover:underline font-medium">{{ __('Forgot your password?') }}</a>
            </p>
        </form>

        {{-- Footer --}}
        <p class="mt-8 text-center text-sm text-[#8A7B6D]">
            {{ __('Authorized personnel only. Contact management for access.') }}
        </p>

    </div>
</x-guest-layout>
