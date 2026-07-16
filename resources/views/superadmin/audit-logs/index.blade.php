<x-app-layout>
    <x-slot name="header">
        <div
            class="flex items-center justify-between"
            x-data
            x-init="
                Echo.private('audit-logs').listen('.AuditLogCreated', () => window.location.reload());
                turboCleanup(() => Echo.leave('audit-logs'));
            "
        >
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Audit Logs') }}
            </h2>
        </div>
    </x-slot>

    @php
        $authEvents = ['login', 'logout', 'failed_login'];

        $subjectLabel = fn (?string $subjectType) => match ($subjectType ? class_basename($subjectType) : null) {
            'MenuCategory' => __('Menu Category'),
            'MenuItem' => __('Menu Item'),
            'SpaceCategory' => __('Space Category'),
            'Setting' => __('Settings'),
            null => null,
            default => class_basename($subjectType),
        };

        $actionLabel = function ($log) use ($authEvents, $subjectLabel) {
            if (in_array($log->event, $authEvents, true)) {
                return __(ucwords(str_replace('_', ' ', $log->event)));
            }

            $subject = $subjectLabel($log->subject_type);

            if (! $subject) {
                return match ($log->event) {
                    'created' => __('Record Created'),
                    'updated' => __('Updated'),
                    'deleted' => __('Deleted'),
                    default => ucfirst($log->event ?? ''),
                };
            }

            return match ($log->event) {
                'created' => __(':subject Created', ['subject' => $subject]),
                'updated' => __(':subject Updated', ['subject' => $subject]),
                'deleted' => __(':subject Deleted', ['subject' => $subject]),
                default => "{$subject} ".ucfirst($log->event ?? ''),
            };
        };
    @endphp

    @if ($logs->isEmpty())
        <x-empty-state
            :title="__('No activity yet')"
            :description="__('Changes made across the system (menu, tables, users, settings) will appear here.')"
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden sm:block bg-white border border-[#E5DDD0] rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E5DDD0]">
                    <thead class="bg-[#FAF6EE]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Date & Time') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('User') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Action') }}</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#8A7B9E] uppercase tracking-wider">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5DDD0]">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-[#FAF6EE]">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $log->causer->name ?? __('System') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide',
                                        'bg-green-100 text-green-800' => $log->event === 'created',
                                        'bg-blue-100 text-blue-800' => $log->event === 'updated',
                                        'bg-red-100 text-red-800' => $log->event === 'deleted',
                                        'bg-teal-100 text-teal-800' => $log->event === 'login',
                                        'bg-gray-100 text-gray-600' => $log->event === 'logout',
                                        'bg-amber-100 text-amber-800' => $log->event === 'failed_login',
                                    ])>
                                        {{ $actionLabel($log) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $log->description }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="sm:hidden space-y-3">
            @foreach ($logs as $log)
                <div class="bg-white border border-[#E5DDD0] rounded-xl p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</p>
                        <span @class([
                            'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wide shrink-0',
                            'bg-green-100 text-green-800' => $log->event === 'created',
                            'bg-blue-100 text-blue-800' => $log->event === 'updated',
                            'bg-red-100 text-red-800' => $log->event === 'deleted',
                            'bg-teal-100 text-teal-800' => $log->event === 'login',
                            'bg-gray-100 text-gray-600' => $log->event === 'logout',
                            'bg-amber-100 text-amber-800' => $log->event === 'failed_login',
                        ])>
                            {{ $actionLabel($log) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-900">{{ $log->causer->name ?? __('System') }}</p>
                    <p class="mt-1 text-sm text-gray-700">{{ $log->description }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    @endif
</x-app-layout>
