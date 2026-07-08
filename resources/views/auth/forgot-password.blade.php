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
                Reset Password
            </p>
        </div>

        <p class="mb-4 text-sm text-[#6F6258] text-center">
            {{ __('Enter your email address and we will send you a link to reset your password.') }}
        </p>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <input
                id="email"
                type="email"
                name="email"
                placeholder="Email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full mb-4 rounded-xl border border-[#D9CCBA] bg-white px-4 py-3 text-sm text-[#333] placeholder:text-gray-400 outline-none focus:border-[#8A3330] focus:ring-2 focus:ring-[#8A3330]"
            >

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
                {{ __('Email Password Reset Link') }}
            </button>
        </form>

        <p class="mt-6 text-center text-sm">
            <a href="{{ route('login') }}" class="text-[#8A3330] hover:underline font-medium">{{ __('Back to Sign In') }}</a>
        </p>

    </div>
</x-guest-layout>
