@props([
    'variant' => 'default',
    'class' => ''
])

@php
    $baseClass = 'inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 gap-1 transition-colors';
    
    $variants = [
        'default' => 'border-transparent bg-primary text-primary-foreground',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground',
        'destructive' => 'border-transparent bg-destructive text-destructive-foreground',
        'outline' => 'text-foreground',
        'success' => 'border-transparent bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
        'warning' => 'border-transparent bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
        'info' => 'border-transparent bg-sky-50 text-sky-700 dark:bg-sky-950 dark:text-sky-300',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<span {{ $attributes->merge(['class' => "$baseClass $variantClass $class"]) }}>
    {{ $slot }}
</span>
