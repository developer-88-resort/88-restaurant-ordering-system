<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        <div class="flex-1 w-full space-y-6">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 text-center">
                <div
                    class="flex flex-col items-center rounded-xl border-2 border-dashed p-4 transition-colors"
                    :class="dragging ? 'border-[#8A3330] bg-[#FAF6EE]' : 'border-transparent'"
                    x-data="{ preview: null, dragging: false }"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    @drop.prevent="
                        dragging = false;
                        const file = $event.dataTransfer.files[0];
                        if (file) {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            $refs.avatarInput.files = dt.files;
                            $refs.avatarInput.dispatchEvent(new Event('change'));
                        }
                    "
                >
                    <template x-if="preview">
                        <img :src="preview" class="h-24 w-24 rounded-full object-cover border border-[#E5DDD0]">
                    </template>
                    <template x-if="!preview">
                        <x-avatar :user="$user" class="h-24 w-24 text-2xl" />
                    </template>

                    <form method="POST" action="{{ route('profile.avatar.update') }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <label x-show="!preview" class="cursor-pointer text-sm font-medium text-[#8A3330] hover:underline">
                            {{ __('Choose Photo') }}
                            <input type="file" name="avatar" accept="image/*" class="hidden" x-ref="avatarInput"
                                   @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </label>

                        <div x-show="preview" class="flex items-center justify-center gap-3">
                            <button type="submit" class="text-sm font-medium text-white bg-[#8A3330] hover:bg-[#742927] rounded-md px-3 py-1.5">
                                {{ __('Save Photo') }}
                            </button>
                            <button type="button" class="text-sm text-gray-600 hover:text-gray-900"
                                    @click="preview = null; $refs.avatarInput.value = ''">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>

                    <p x-show="!preview" class="mt-1 text-xs text-gray-400 hidden lg:block">{{ __('or drag a photo here') }}</p>

                    @if ($user->avatar_path)
                        <x-confirm-form
                            :action="route('profile.avatar.destroy')"
                            method="DELETE"
                            class="mt-1"
                            :title="__('Remove your profile picture?')"
                            :confirm-label="__('Remove')"
                        >
                            <button type="submit" class="text-sm text-red-600 hover:text-red-900">{{ __('Remove Photo') }}</button>
                        </x-confirm-form>
                    @endif

                    <x-input-error :messages="$errors->get('avatar')" class="mt-1" />
                    <p class="mt-1 text-xs text-gray-400">{{ __('JPG or PNG, up to 2MB.') }}</p>
                </div>

                <div class="mt-6 pt-6 border-t border-dashed border-[#D9CCBA] text-left space-y-3">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Name') }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Role') }}</p>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide bg-[#F3E1DC] text-[#8A3330]">
                            {{ $user->role->label() }}
                        </span>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Email') }}</p>
                        <p class="text-sm text-gray-700 break-all">{{ $user->email }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-[#8A7B9E]">{{ __('Member Since') }}</p>
                        <p class="text-sm text-gray-700">{{ $user->created_at->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
