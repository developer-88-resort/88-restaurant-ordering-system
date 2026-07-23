<x-customer-layout>
    <div x-data="{ open: false, targetUrl: '', targetLabel: '' }" class="px-4 py-6 max-w-2xl mx-auto">
        <a href="{{ route('customer.welcome.show') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
            &larr; {{ __('Back') }}
        </a>

        <div class="mt-3 mb-6 text-center">
            <h1 class="text-xl font-bold text-gray-900">{{ __('Choose a Seat') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Pick where you\'ll be sitting.') }}</p>
        </div>

        @if ($areas->isEmpty())
            <x-empty-state
                :title="__('No seats available right now')"
                :description="__('Please ask our staff for assistance.')"
            />
        @else
            <div class="space-y-8">
                @foreach ($areas as $area)
                    @php $spaces = $area->categories->flatMap->spaces; @endphp
                    @if ($spaces->isNotEmpty())
                        <div>
                            <h2 class="text-sm font-semibold uppercase tracking-wide text-[#8A7B6D] mb-3">{{ $area->name }}</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($spaces as $space)
                                    @php $isAvailable = $space->status === \App\Enums\SpaceStatus::Available; @endphp
                                    @if ($isAvailable)
                                        <button type="button"
                                                @click="open = true; targetUrl = '{{ route('customer.spaces.show', $space->qr_token) }}?name={{ urlencode($customerName ?? '') }}'; targetLabel = {{ Js::from($area->name.' — '.$space->name) }}"
                                                class="flex flex-col items-center justify-center gap-1.5 rounded-2xl bg-white border-2 border-[#E5DDD0] hover:border-[#8A3330] hover:shadow-md transition-all p-4 text-center">
                                            <span class="font-semibold text-gray-900">{{ $space->name }}</span>
                                            <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $space->status->badgeClasses() }}">
                                                {{ $space->status->label() }}
                                            </span>
                                        </button>
                                    @else
                                        <button type="button"
                                                @click="notifyUnavailable({{ Js::from($space->name) }}, {{ Js::from($space->status->label()) }})"
                                                class="flex flex-col items-center justify-center gap-1.5 rounded-2xl bg-[#FAF6EE] border-2 border-[#E5DDD0] p-4 text-center opacity-60 cursor-not-allowed">
                                            <span class="font-semibold text-gray-500">{{ $space->name }}</span>
                                            <span class="inline-flex px-2 py-0.5 text-[10px] font-semibold rounded-full {{ $space->status->badgeClasses() }}">
                                                {{ $space->status->label() }}
                                            </span>
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Step 1: the existing "Are you sure?" confirm dialog — unchanged. --}}
        <dialog
            x-ref="dialog"
            x-effect="open ? $refs.dialog.showModal() : $refs.dialog.close()"
            @cancel="open = false"
            @click="$event.target === $refs.dialog && (open = false)"
            class="rounded-xl border border-[#E5DDD0] p-0 backdrop:bg-black/40 max-w-sm w-[calc(100%-2rem)] m-auto"
        >
            <div class="p-6 text-center">
                <h3 class="font-semibold text-gray-900">{{ __('Are you sure?') }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ __('You selected:') }}</p>
                <p class="mt-1 text-lg font-bold text-[#8A3330]" x-text="targetLabel"></p>
                <div class="mt-6 flex justify-center gap-3">
                    <button type="button" @click="open = false" class="text-sm font-medium text-gray-600 hover:text-gray-900 px-4 py-2">
                        {{ __('Cancel') }}
                    </button>
                    {{-- Step 2: once confirmed, a SweetAlert2 notification tells
                         the customer their seat was selected, THEN proceeds. --}}
                    <button type="button" @click="open = false; notifySeatSelected(targetUrl, targetLabel)"
                            class="text-sm font-medium rounded-md px-5 py-2 bg-[#8A3330] hover:bg-[#742927] text-white">
                        {{ __('Yes, Continue') }}
                    </button>
                </div>
            </div>
        </dialog>
    </div>

    <script>
        function notifySeatSelected(url, label) {
            Swal.fire({
                title: '{{ __('Confirmed!') }}',
                text: label + ' {{ __('has been selected.') }}',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                window.location.href = url;
            });
        }

        function notifyUnavailable(name, status) {
            const template = {{ Js::from(__(':name is currently :status.')) }};

            Swal.fire({
                title: '{{ __('Not Available') }}',
                text: template.replace(':name', name).replace(':status', status),
                icon: 'warning',
                confirmButtonColor: '#8A3330',
            });
        }
    </script>
</x-customer-layout>
