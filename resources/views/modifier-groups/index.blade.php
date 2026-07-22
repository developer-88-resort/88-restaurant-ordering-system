<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Modifier Groups') }}
            </h2>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('menu-items.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                    {{ __('Back to Menu Items') }}
                </a>
                <a href="{{ route('modifier-groups.create') }}">
                    <x-primary-button>{{ __('New Modifier Group') }}</x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="mb-4 flex justify-end">
        <span class="inline-flex rounded-full border border-[#E5DDD0] bg-white p-0.5">
            <a href="{{ route('modifier-groups.index') }}"
               class="px-3 py-1 rounded-full text-xs font-semibold transition {{ ! $showArchived ? 'bg-[#8A3330] text-white' : 'text-gray-500 hover:text-gray-700' }}">
                {{ __('Active') }}
            </a>
            <a href="{{ route('modifier-groups.index', ['archived' => 1]) }}"
               class="px-3 py-1 rounded-full text-xs font-semibold transition {{ $showArchived ? 'bg-[#8A3330] text-white' : 'text-gray-500 hover:text-gray-700' }}">
                {{ __('Archived') }} ({{ $archivedCount }})
            </a>
        </span>
    </div>

    @if ($groups->isEmpty())
        <x-empty-state
            :title="$showArchived ? __('No archived modifier groups') : __('No modifier groups yet')"
            :description="$showArchived ? __('Groups you archive will show up here.') : __('Create reusable groups like Rice Options, Spice Level, or Add-ons that you can attach to any menu item.')"
            :actionLabel="$showArchived ? null : __('New Modifier Group')"
            :actionHref="$showArchived ? null : route('modifier-groups.create')"
        />
    @else
        <div class="bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Type') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Required') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Options') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($groups as $group)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $group->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $group->selection_type->label() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if ($group->is_required)
                                        <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-amber-100 text-amber-800">{{ __('Required') }}</span>
                                    @else
                                        <span class="text-gray-400">{{ __('Optional') }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $group->options_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    @if ($showArchived)
                                        <form action="{{ route('modifier-groups.restore', $group) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="text-green-700 hover:text-green-900">{{ __('Restore') }}</button>
                                        </form>
                                    @else
                                        <a href="{{ route('modifier-groups.edit', $group) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                                        <x-confirm-form
                                            :action="route('modifier-groups.destroy', $group)"
                                            method="DELETE"
                                            class="inline"
                                            :title="__('Archive this modifier group?')"
                                            :message="__('You can restore :name later from the Archived tab. Items it is currently attached to will no longer show it.', ['name' => $group->name])"
                                            :confirm-label="__('Archive')"
                                        >
                                            <button type="submit" class="text-red-600 hover:text-red-900">{{ __('Archive') }}</button>
                                        </x-confirm-form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
