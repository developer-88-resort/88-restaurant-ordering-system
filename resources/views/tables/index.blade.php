<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tables') }}
            </h2>
            <a href="{{ route('tables.create') }}">
                <x-primary-button>{{ __('New Table') }}</x-primary-button>
            </a>
        </div>
    </x-slot>

    @if ($tables->isEmpty())
        <x-empty-state
            :title="__('No tables yet')"
            :description="__('Add tables so customers can scan a QR code to start ordering.')"
            :actionLabel="__('New Table')"
            :actionHref="route('tables.create')"
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Table') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('QR Code') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($tables as $table)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $table->table_number }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('tables.update-status', $table) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" onchange="this.form.submit()"
                                                class="text-xs font-semibold rounded-full px-3 py-1 border-0 focus:ring-2 focus:ring-[#8A3330] {{ $table->status->badgeClasses() }}">
                                            @foreach (\App\Enums\TableStatus::cases() as $status)
                                                <option value="{{ $status->value }}" @selected($table->status === $status)>{{ $status->label() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ route('tables.qr-code', $table) }}" alt="{{ __('QR code for') }} {{ $table->table_number }}" class="h-14 w-14 border border-[#E5DDD0] rounded">
                                        <a href="{{ route('tables.print', $table) }}" target="_blank" class="text-sm text-[#8A3330] hover:underline">{{ __('Print') }}</a>
                                        <a href="{{ route('tables.qr-code', ['table' => $table, 'download' => 1]) }}" class="text-sm text-[#8A3330] hover:underline">{{ __('Download') }}</a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    <a href="{{ route('tables.edit', $table) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                                    <x-confirm-form
                                        :action="route('tables.destroy', $table)"
                                        method="DELETE"
                                        class="inline"
                                        :title="__('Delete this table?')"
                                        :message="__('This will permanently remove :table and its QR code.', ['table' => $table->table_number])"
                                        :confirm-label="__('Delete')"
                                    >
                                        <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                    </x-confirm-form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @foreach ($tables as $table)
                <div class="bg-white border border-[#E5DDD0] rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ route('tables.qr-code', $table) }}" alt="{{ __('QR code for') }} {{ $table->table_number }}" class="h-14 w-14 border border-[#E5DDD0] rounded shrink-0">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $table->table_number }}</p>
                                <div class="mt-1 flex gap-3">
                                    <a href="{{ route('tables.print', $table) }}" target="_blank" class="text-xs text-[#8A3330] hover:underline">{{ __('Print') }}</a>
                                    <a href="{{ route('tables.qr-code', ['table' => $table, 'download' => 1]) }}" class="text-xs text-[#8A3330] hover:underline">{{ __('Download') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('tables.update-status', $table) }}" method="POST" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()"
                                class="w-full text-xs font-semibold rounded-full px-3 py-1.5 border-0 focus:ring-2 focus:ring-[#8A3330] {{ $table->status->badgeClasses() }}">
                            @foreach (\App\Enums\TableStatus::cases() as $status)
                                <option value="{{ $status->value }}" @selected($table->status === $status)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </form>

                    <div class="mt-3 pt-3 border-t border-[#E5DDD0] flex items-center justify-end gap-4 text-sm">
                        <a href="{{ route('tables.edit', $table) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                        <x-confirm-form
                            :action="route('tables.destroy', $table)"
                            method="DELETE"
                            :title="__('Delete this table?')"
                            :message="__('This will permanently remove :table and its QR code.', ['table' => $table->table_number])"
                            :confirm-label="__('Delete')"
                        >
                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                        </x-confirm-form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
