@props([
    'variant' => 'default',
    'dismissible' => true,
])

@php
    $variants = [
        'default' => 'bg-muted border-border text-foreground',
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950 dark:border-emerald-800 dark:text-emerald-200',
        'destructive' => 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950 dark:border-rose-800 dark:text-rose-200',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950 dark:border-amber-800 dark:text-amber-200',
        'info' => 'bg-sky-50 border-sky-200 text-sky-800 dark:bg-sky-950 dark:border-sky-800 dark:text-sky-200',
    ];
    
    $icons = [
        'success' => 'check-circle',
        'destructive' => 'alert-circle',
        'warning' => 'alert-triangle',
        'info' => 'info',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
    $iconName = $icons[$variant] ?? 'info';
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    {{ $attributes->merge(['class' => "flex items-center justify-between p-4 rounded-xl border shadow-sm $variantClass"]) }}
>
    <div class="flex items-center gap-3">
        <i data-lucide="{{ $iconName }}" class="w-5 h-5 flex-shrink-0"></i>
        <span class="text-sm font-medium">{{ $slot }}</span>
    </div>
    @if($dismissible)
        <button @click="show = false" class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    @endif
</div>
