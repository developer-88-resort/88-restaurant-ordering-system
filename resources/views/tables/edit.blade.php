<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Table') }} — {{ $table->table_number }}
        </h2>
    </x-slot>

    <div class="flex flex-col lg:flex-row gap-6 items-start">
        <div class="flex-1 max-w-2xl w-full">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-8">
                <form method="POST" action="{{ route('tables.update', $table) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <x-input-label for="table_number" :value="__('Table Number')" />
                            <x-text-input id="table_number" name="table_number" type="text" class="block mt-1 w-full" :value="old('table_number', $table->table_number)" required autofocus />
                            <x-input-error :messages="$errors->get('table_number')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full border-gray-300 focus:border-[#8A3330] focus:ring-[#8A3330] rounded-md shadow-sm" required>
                                @foreach (\App\Enums\TableStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected(old('status', $table->status->value) === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 space-x-3 border-t border-[#E5DDD0] pt-6">
                        <a href="{{ route('tables.index') }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancel') }}</a>
                        <x-primary-button>{{ __('Save Changes') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="w-full lg:w-72 shrink-0">
            <div class="bg-white border border-[#E5DDD0] rounded-xl p-6 text-center">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('QR Code') }}</h3>
                <img src="{{ route('tables.qr-code', $table) }}" alt="{{ __('QR code for') }} {{ $table->table_number }}" class="mx-auto h-40 w-40 border border-[#E5DDD0] rounded">
                <a href="{{ route('tables.print', $table) }}" target="_blank" class="block mt-3 text-sm text-[#8A3330] hover:underline">{{ __('Print') }}</a>
                <a href="{{ route('tables.qr-code', ['table' => $table, 'download' => 1]) }}" class="block mt-1 text-sm text-[#8A3330] hover:underline">{{ __('Download SVG') }}</a>
            </div>
        </div>
    </div>
</x-app-layout>
