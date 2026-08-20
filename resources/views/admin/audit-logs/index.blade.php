<x-layouts.admin title="سجل النشاط" heading="سجل النشاط">
    @forelse ($logs as $log)
        <div class="card mb-2 p-3">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold text-slate-900">{{ $log->action->label() }}</span>
                <span class="text-[11px] text-slate-400">{{ $log->created_at?->diffForHumans() }}</span>
            </div>
            <div class="mt-1 text-xs text-slate-500">
                {{ $log->user?->name ?? 'النظام' }}
                @if ($log->subject_type)
                    · {{ class_basename($log->subject_type) }}#{{ $log->subject_id }}
                @endif
                @if ($log->ip_address) · <span dir="ltr">{{ $log->ip_address }}</span> @endif
            </div>
            @if ($log->metadata)
                <div class="mt-1 truncate text-[11px] text-slate-400" dir="ltr">{{ json_encode($log->metadata, JSON_UNESCAPED_UNICODE) }}</div>
            @endif
        </div>
    @empty
        <x-empty-state title="لا يوجد نشاط بعد" />
    @endforelse

    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.admin>
