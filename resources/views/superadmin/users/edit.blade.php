<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Account') }} — {{ $user->name }}
        </h2>
    </x-slot>

    <div class="max-w-3xl">
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

                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <x-input-label for="password" :value="__('New Password')" />
                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" />
                        <p class="text-sm text-gray-500 mt-1">{{ __('Leave blank to keep the current password.') }}</p>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm" required>
                            <option value="superadmin" @selected(old('role', $user->role->value) === 'superadmin')>{{ __('Superadmin') }}</option>
                            <option value="admin" @selected(old('role', $user->role->value) === 'admin')>{{ __('Admin') }}</option>
                            <option value="staff" @selected(old('role', $user->role->value) === 'staff')>{{ __('Staff') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>
                </div>

                <div class="mt-5 flex items-center">
                    <input id="is_active" name="is_active" type="checkbox" value="1"
                           class="rounded border-gray-300 text-[#8A3330] shadow-sm focus:ring-[#8A3330]"
                           @checked(old('is_active', $user->is_active))>
                    <label for="is_active" class="ms-2 text-sm text-gray-600">{{ __('Active') }}</label>
                </div>

                <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                    <a href="{{ route('superadmin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                    <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
