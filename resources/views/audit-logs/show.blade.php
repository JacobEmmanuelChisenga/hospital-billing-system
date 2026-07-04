<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Audit Entry" subtitle="{{ $log->action_type->label() }} — {{ $log->created_at->format('d M Y H:i') }}">
            <x-slot name="actions">
                <a href="{{ route('audit-logs.index') }}" class="btn-ghost">&larr; Audit Log</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="card card-body max-w-3xl">
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            <div>
                <dt class="text-slate-500">Action</dt>
                <dd class="mt-1">
                    <span class="badge {{ $log->action_type->badgeClass() }}">
                        {{ $log->action_type->label() }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-slate-500">Date &amp; Time</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $log->created_at->format('d M Y H:i:s') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Staff User</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $log->user?->name ?? 'Unknown / deleted user' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Related Record</dt>
                <dd class="mt-1 font-medium text-slate-900">
                    @if ($url = $log->relatedUrl())
                        <a href="{{ $url }}" class="action-link">{{ $log->relatedSummary() }}</a>
                    @elseif ($log->relatedSummary())
                        {{ $log->relatedSummary() }}
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-slate-500">Description</dt>
                <dd class="mt-1 font-medium text-slate-900">{{ $log->description }}</dd>
            </div>
        </dl>

        @if ($log->metadata)
            <div class="panel-footer -mx-6 -mb-6 mt-6 px-6 py-6">
                <h3 class="section-title">Additional details</h3>
                <dl class="mt-3 grid gap-3 sm:grid-cols-2 text-sm">
                    @foreach ($log->metadata as $key => $value)
                        <div>
                            <dt class="text-slate-500">{{ str($key)->headline() }}</dt>
                            <dd class="mt-1 font-medium text-slate-900">
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
