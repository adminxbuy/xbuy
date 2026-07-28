@props(['icon' => '', 'label' => '', 'value' => '', 'change' => null, 'changeLabel' => 'vs previous period'])

<x-ui.card>
    <div class="flex items-start justify-between">
        <div class="space-y-1">
            <span class="text-xs font-semibold text-muted-foreground uppercase tracking-wider">{{ $label }}</span>
            <h3 class="text-2xl font-bold tracking-tight text-foreground mt-1">{{ $value }}</h3>
        </div>
        @if($icon)
            <div class="p-2.5 rounded-lg bg-muted text-muted-foreground">
                <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
            </div>
        @endif
    </div>
    @if($change !== null)
        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
            <span>{{ $changeLabel }}</span>
            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $change >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $change >= 0 ? '+' : '' }}{{ $change }}%
            </span>
        </div>
    @endif
</x-ui.card>
