<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Account') }} — {{ $user->name }}
        </h2>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
            <form method="POST" action="{{ route('superadmin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name', $user->name)" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email')" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $user->email)" required />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-5">
                    <x-input-label for="role" :value="__('Role')" />
                    <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm" required>
                        <option value="superadmin" @selected(old('role', $user->role->value) === 'superadmin')>{{ __('Superadmin') }}</option>
                        <option value="admin" @selected(old('role', $user->role->value) === 'admin')>{{ __('Admin') }}</option>
                        <option value="staff" @selected(old('role', $user->role->value) === 'staff')>{{ __('Staff') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                    <a href="{{ route('superadmin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
            <h3 class="font-semibold text-gray-900">{{ __('Password') }}</h3>

            @if ($user->isPendingActivation())
                <p class="mt-1 text-sm text-gray-500">{{ __("This account hasn't been activated yet. Resend the invitation email so they can set their own password.") }}</p>

                <form method="POST" action="{{ route('superadmin.users.resend-invitation', $user) }}" class="mt-4">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Resend Invitation') }}</x-secondary-button>
                </form>
            @else
                <p class="mt-1 text-sm text-gray-500">{{ __("For security, passwords can only be set by the account owner. Send them a reset link instead of choosing a password for them.") }}</p>

                <form method="POST" action="{{ route('superadmin.users.send-password-reset', $user) }}" class="mt-4">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Send Password Reset Link') }}</x-secondary-button>
                </form>
            @endif
        </div>

        <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
            <h3 class="font-semibold text-gray-900">{{ __('Account Status') }}</h3>

            @if ($user->is_active)
                <p class="mt-1 text-sm text-gray-500">{{ __('This account is active and can sign in. Deactivating keeps their record for audit and history, but blocks access.') }}</p>

                @if ($isLastActiveSuperadmin)
                    <p class="mt-4 text-sm text-amber-700">{{ __('You cannot deactivate the last remaining Superadmin.') }}</p>
                @else
                    <x-confirm-form
                        :action="route('superadmin.users.deactivate', $user)"
                        method="POST"
                        class="mt-4"
                        :title="__('Deactivate this account?')"
                        :message="__(':name will no longer be able to sign in. Their account and history are kept and can be reactivated anytime.', ['name' => $user->name])"
                        :confirm-label="__('Deactivate')"
                    >
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-red-300 rounded-md font-semibold text-xs text-red-600 uppercase tracking-widest shadow-sm hover:bg-red-50 transition ease-in-out duration-150">
                            {{ __('Deactivate') }}
                        </button>
                    </x-confirm-form>
                @endif
            @else
                <p class="mt-1 text-sm text-gray-500">{{ __('This account is deactivated and cannot sign in. Reactivate to restore access.') }}</p>

                <form method="POST" action="{{ route('superadmin.users.reactivate', $user) }}" class="mt-4">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Reactivate') }}</x-secondary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
