<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Menu Categories') }}
            </h2>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('menu-items.index') }}" class="text-sm text-[#8A3330] hover:underline font-medium">
                    {{ __('Back to Menu Items') }}
                </a>
                <a href="{{ route('menu-categories.create') }}">
                    <x-primary-button>{{ __('New Category') }}</x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    @if ($categories->isEmpty())
        <x-empty-state
            :title="__('No menu categories yet')"
            :description="__('Create categories like Appetizers, Main Course, or Drinks to organize your menu items.')"
            :actionLabel="__('New Category')"
            :actionHref="route('menu-categories.create')"
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Items') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Sort Order') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Status') }}</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($categories as $category)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $category->menu_items_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $category->sort_order }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <form action="{{ route('menu-categories.toggle-status', $category) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        @if ($category->is_active)
                                            <button type="submit" class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800 hover:bg-green-200">{{ __('Active') }}</button>
                                        @else
                                            <button type="submit" class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200">{{ __('Inactive') }}</button>
                                        @endif
                                    </form>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-3">
                                    <a href="{{ route('menu-categories.edit', $category) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                                    <x-confirm-form
                                        :action="route('menu-categories.destroy', $category)"
                                        method="DELETE"
                                        class="inline"
                                        :title="__('Delete this category?')"
                                        :message="__('This will permanently remove :name.', ['name' => $category->name])"
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
            @foreach ($categories as $category)
                <div class="bg-white border border-[#E5DDD0] rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $category->name }}</p>
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __(':count items', ['count' => $category->menu_items_count]) }}
                                &middot; {{ __('Sort') }} {{ $category->sort_order }}
                            </p>
                        </div>
                        <form action="{{ route('menu-categories.toggle-status', $category) }}" method="POST" class="shrink-0">
                            @csrf
                            @method('PATCH')
                            @if ($category->is_active)
                                <button type="submit" class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800 hover:bg-green-200">{{ __('Active') }}</button>
                            @else
                                <button type="submit" class="inline-flex px-2.5 py-0.5 text-xs font-semibold leading-5 rounded-full bg-gray-100 text-gray-800 hover:bg-gray-200">{{ __('Inactive') }}</button>
                            @endif
                        </form>
                    </div>

                    <div class="mt-3 pt-3 border-t border-[#E5DDD0] flex items-center justify-end gap-4 text-sm">
                        <a href="{{ route('menu-categories.edit', $category) }}" class="text-[#8A3330] hover:text-[#5f2120]">{{ __('Edit') }}</a>
                        <x-confirm-form
                            :action="route('menu-categories.destroy', $category)"
                            method="DELETE"
                            :title="__('Delete this category?')"
                            :message="__('This will permanently remove :name.', ['name' => $category->name])"
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
