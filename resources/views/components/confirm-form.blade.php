@props([
    'action',
    'method' => 'POST',
    'title' => __('Are you sure?'),
    'message' => null,
    'confirmLabel' => __('Confirm'),
    'variant' => 'danger',
])

@php
    $confirmClasses = match ($variant) {
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        default => 'bg-[#8A3330] hover:bg-[#742927] text-white',
    };
@endphp

<form
    method="POST"
    action="{{ $action }}"
    {{ $attributes }}
    x-data="{ open: false }"
    @submit.prevent="open = true"
>
    @csrf
    @if (strtoupper($method) !== 'POST')
        @method($method)
    @endif

    {{ $slot }}

    <dialog
        x-ref="dialog"
        x-effect="open ? $refs.dialog.showModal() : $refs.dialog.close()"
        @cancel="open = false"
        @click="$event.target === $refs.dialog && (open = false)"
        class="rounded-xl border border-[#E5DDD0] p-0 backdrop:bg-black/40 max-w-sm w-[calc(100%-2rem)] m-auto"
    >
        <div class="p-6">
            <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
            @if ($message)
                <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
            @endif
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="open = false" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    {{ __('Cancel') }}
                </button>
                <button type="button" @click="open = false; $root.submit()"
                        class="text-sm font-medium rounded-md px-4 py-2 {{ $confirmClasses }}">
                    {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </dialog>
</form>
