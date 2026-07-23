<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Invite User') }}
        </h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ __("Send a secure invitation link so the user can set their own password.") }}</p>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        {{-- Invite form --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 sm:p-8">
                <form method="POST" action="{{ route('superadmin.users.store') }}" data-draft-key="superadmin-user-create">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-5">
                        <x-input-label for="role" :value="__('Role')" />
                        <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm" required>
                            <option value="superadmin" @selected(old('role') === 'superadmin')>{{ __('Superadmin') }}</option>
                            <option value="admin" @selected(old('role') === 'admin')>{{ __('Admin') }}</option>
                            <option value="staff" @selected(old('role', 'staff') === 'staff')>{{ __('Staff') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                        <a href="{{ route('superadmin.users.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Send Invitation') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Invitation info panel --}}
        <div class="space-y-6">
            @if ($pendingCount > 0)
                <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">{{ __('Pending Invitations') }}</p>
                        <p class="text-2xl font-semibold text-gray-900 mt-0.5">{{ $pendingCount }}</p>
                    </div>
                    <div class="h-11 w-11 rounded-full bg-[#F3E1DC] flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                </div>
            @endif

            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
                <h3 class="text-sm font-semibold text-gray-900">{{ __('How invitation works') }}</h3>
                <ol class="mt-4 space-y-4">
                    @foreach ([
                        __('You send an invite with just a name, email, and role.'),
                        __('The user gets an email with a secure activation link.'),
                        __('They open the link and create their own password.'),
                        __('Their account becomes active and they can sign in.'),
                    ] as $index => $step)
                        <li class="flex gap-3">
                            <span class="h-6 w-6 rounded-full bg-[#F3E1DC] text-[#8A3330] text-xs font-semibold flex items-center justify-center shrink-0">{{ $index + 1 }}</span>
                            <p class="text-sm text-gray-600 leading-6">{{ $step }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="bg-[#FAF6EE] border border-[#E5DDD0] rounded-xl p-6">
                <div class="flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5 shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('Link expires in 7 days') }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ __('If it expires before they set up their account, you can resend a fresh invitation from the users list.') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6">
                <div class="flex gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#8A3330" class="h-5 w-5 shrink-0 mt-0.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('Private by design') }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ __("They'll receive an email invitation to set their own password. No password is created or seen by you.") }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent invitations --}}
    <div class="mt-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Recent Invitations') }}</h3>

        @if ($recentInvitations->isEmpty())
            <x-empty-state
                :title="__('No pending invitations')"
                :description="__('Invitations you send will show up here until the user activates their account.')"
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
                                <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Sent') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E5DDD0]">
                            @foreach ($recentInvitations as $invitee)
                                <tr class="hover:bg-[#FAF6EE]">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $invitee->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $invitee->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-[#F3E1DC] text-[#8A3330]">
                                            {{ $invitee->role->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full {{ $invitee->invitationStatus()->badgeClasses() }}">
                                            {{ $invitee->invitationStatus()->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invitee->created_at->diffForHumans() }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <form action="{{ route('superadmin.users.resend-invitation', $invitee) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Resend Invitation') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="sm:hidden space-y-3">
                @foreach ($recentInvitations as $invitee)
                    <div class="bg-white border border-[#E5DDD0] rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <x-avatar :user="$invitee" class="h-11 w-11 text-sm shrink-0" />
                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $invitee->name }}</p>
                                <p class="text-sm text-gray-500 truncate">{{ $invitee->email }}</p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-[#F3E1DC] text-[#8A3330]">
                                {{ $invitee->role->label() }}
                            </span>
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full {{ $invitee->invitationStatus()->badgeClasses() }}">
                                {{ $invitee->invitationStatus()->label() }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $invitee->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="mt-3 pt-3 border-t border-[#E5DDD0] flex items-center justify-end text-sm">
                            <form action="{{ route('superadmin.users.resend-invitation', $invitee) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Resend Invitation') }}</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
