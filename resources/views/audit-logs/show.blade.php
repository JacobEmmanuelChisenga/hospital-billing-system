<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Audit Entry</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $log->action_type->label() }} — {{ $log->created_at->format('d M Y H:i') }}</p>
            </div>
            <a href="{{ route('audit-logs.index') }}" class="text-sm text-hospital-700 hover:underline">&larr; Audit Log</a>
        </div>
    </x-slot>

    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-gray-500">Action</dt>
                <dd class="mt-1">
                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $log->action_type->badgeClass() }}">
                        {{ $log->action_type->label() }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Date &amp; Time</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $log->created_at->format('d M Y H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Staff User</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $log->user?->name ?? 'Unknown / deleted user' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Related Record</dt>
                <dd class="mt-1 font-medium text-gray-900">
                    @if ($url = $log->relatedUrl())
                        <a href="{{ $url }}" class="text-hospital-700 hover:underline">{{ $log->relatedSummary() }}</a>
                    @elseif ($log->relatedSummary())
                        {{ $log->relatedSummary() }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Description</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $log->description }}</dd>
            </div>
        </dl>

        @if ($log->metadata)
            <div class="mt-6 border-t border-gray-100 pt-6">
                <h3 class="text-sm font-semibold text-gray-800">Additional details</h3>
                <dl class="mt-3 grid gap-3 sm:grid-cols-2 text-sm">
                    @foreach ($log->metadata as $key => $value)
                        <div>
                            <dt class="text-gray-500">{{ str($key)->headline() }}</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                @if (is_array($value))
                                    {{ json_encode($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</x-app-layout>
