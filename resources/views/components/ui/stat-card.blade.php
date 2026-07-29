@props([
    'icon' => '',
    'label' => '',
    'value' => '',
    'change' => null,
    'changeLabel' => 'vs previous period',
    'caption' => null,
    'iconStyle' => 'muted',
])

{{--
    Stat card.

    Two ways to show trend:
    - `change` (numeric): renders the shadcn-style footer row ("vs previous period  +5%").
    - `badge` slot: renders an inline pill next to the value (used on the dashboard,
      e.g. a coloured +/-% badge), optionally with a `caption` line beneath.

    `iconStyle`: 'muted' (default, neutral chip) or 'primary' (brand-tinted chip).
--}}

@php
    $iconWrap = $iconStyle === 'primary'
        ? 'bg-primary/10 text-brand-ink'
        : 'bg-muted text-muted-foreground';
@endphp

<x-ui.card>
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1 min-w-0">
            <span class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ $label }}</span>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-metric text-foreground">{{ $value }}</span>
                @isset($badge)
                    {{ $badge }}
                @endisset
            </div>
            @if($caption)
                <p class="text-xs text-muted-foreground">{{ $caption }}</p>
            @endif
        </div>
        @if($icon)
            <div class="p-2.5 rounded-lg shrink-0 {{ $iconWrap }}">
                <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
            </div>
        @endif
    </div>
    @if($change !== null)
        <div class="mt-4 pt-3 border-t border-border flex items-center justify-between text-xs text-muted-foreground">
            <span>{{ $changeLabel }}</span>
            <span class="inline-flex items-center gap-1 text-xs font-medium {{ $change >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                {{ $change >= 0 ? '+' : '' }}{{ $change }}%
            </span>
        </div>
    @endif
</x-ui.card>
