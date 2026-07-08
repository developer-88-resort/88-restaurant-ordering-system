<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Users') }}
            </h2>
            <a href="{{ route('superadmin.users.create') }}">
                <x-primary-button>{{ __('New Account') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    @if ($users->isEmpty())
        <x-empty-state
            :title="__('No user accounts yet')"
            :description="__('Create Superadmin, Admin, and Staff accounts to give them access to this portal.')"
            :actionLabel="__('New Account')"
            :actionHref="route('superadmin.users.create')"
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Role') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Online') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($users as $user)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="text-xs text-gray-400">({{ __('You') }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-[#F3E1DC] text-[#8A3330]">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($user->is_active)
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800">{{ __('Active') }}</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-gray-100 text-gray-800">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if (in_array($user->id, $onlineUserIds))
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                            {{ __('Online') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                            <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                            {{ __('Offline') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    @if ($user->id === auth()->id())
                                        <span class="text-gray-300">{{ __('Edit') }}</span>
                                        <span class="text-gray-300">{{ __('Delete') }}</span>
                                    @else
                                        <a href="{{ route('superadmin.users.edit', $user) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                                        <x-confirm-form
                                            :action="route('superadmin.users.destroy', $user)"
                                            method="DELETE"
                                            class="inline"
                                            :title="__('Delete this account?')"
                                            :message="__('This will permanently remove :name\'s account and access.', ['name' => $user->name])"
                                            :confirm-label="__('Delete')"
                                        >
                                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                        </x-confirm-form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @foreach ($users as $user)
                <div class="bg-white border border-[#E5DDD0] rounded-xl p-4">
                    <div class="flex items-center gap-3">
                        <x-avatar :user="$user" class="h-11 w-11 text-sm shrink-0" />
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900 truncate">
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="text-xs text-gray-400 font-normal">({{ __('You') }})</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-[#F3E1DC] text-[#8A3330]">
                            {{ $user->role->label() }}
                        </span>
                        @if ($user->is_active)
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800">{{ __('Active') }}</span>
                        @else
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-gray-100 text-gray-800">{{ __('Inactive') }}</span>
                        @endif
                        @if (in_array($user->id, $onlineUserIds))
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                {{ __('Online') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-400">
                                <span class="h-2 w-2 rounded-full bg-gray-300"></span>
                                {{ __('Offline') }}
                            </span>
                        @endif
                    </div>

                    @unless ($user->id === auth()->id())
                        <div class="mt-3 pt-3 border-t border-[#E5DDD0] flex items-center justify-end gap-4 text-sm">
                            <a href="{{ route('superadmin.users.edit', $user) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                            <x-confirm-form
                                :action="route('superadmin.users.destroy', $user)"
                                method="DELETE"
                                :title="__('Delete this account?')"
                                :message="__('This will permanently remove :name\'s account and access.', ['name' => $user->name])"
                                :confirm-label="__('Delete')"
                            >
                                <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                            </x-confirm-form>
                        </div>
                    @endunless
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
