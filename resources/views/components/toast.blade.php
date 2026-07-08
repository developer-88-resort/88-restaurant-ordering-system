@php
    $toasts = [];
    if (session('status')) {
        $toasts[] = ['id' => 'status', 'type' => 'success', 'message' => session('status')];
    }
    if (session('error')) {
        $toasts[] = ['id' => 'error', 'type' => 'error', 'message' => session('error')];
    }
@endphp

@if (! empty($toasts))
    <div
        x-data="{ toasts: @js($toasts) }"
        class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-4 z-[60] flex flex-col gap-3 sm:w-96"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-data="{ width: '100%' }"
                x-init="requestAnimationFrame(() => requestAnimationFrame(() => width = '0%')); setTimeout(() => toasts = toasts.filter(t => t.id !== toast.id), 4000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-1"
                class="relative overflow-hidden flex items-start gap-3 rounded-xl border border-[#E5DDD0] bg-white pl-4 pr-3 py-3.5 shadow-xl"
            >
                <div :class="toast.type === 'error' ? 'bg-red-100' : 'bg-green-100'"
                     class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full">
                    <svg x-show="toast.type !== 'error'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-4 w-4 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-4 w-4 text-red-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>

                <p class="flex-1 pt-1 text-sm font-medium text-gray-800" x-text="toast.message"></p>

                <button type="button" @click="toasts = toasts.filter(t => t.id !== toast.id)"
                        class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="absolute bottom-0 left-0 h-0.5"
                     :class="toast.type === 'error' ? 'bg-red-400' : 'bg-green-400'"
                     :style="{ width: width, transitionProperty: 'width', transitionDuration: '4000ms', transitionTimingFunction: 'linear' }">
                </div>
            </div>
        </template>
    </div>
@endif
