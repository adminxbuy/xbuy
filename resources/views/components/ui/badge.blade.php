@props([
    'variant' => 'default',
    'class' => ''
])

@php
$baseClass = 'inline-flex items-center justify-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 w-fit whitespace-nowrap shrink-0 gap-1';

$variants = [
    'default'     => 'border-transparent bg-primary text-primary-foreground',
    'secondary'   => 'border-transparent bg-secondary text-secondary-foreground',
    'destructive' => 'border-transparent bg-destructive/15 text-destructive border-destructive/20',
    'outline'     => 'border-border text-foreground',
    'success'     => 'border-emerald-500/20 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400',
    'warning'     => 'border-amber-500/20 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400',
    'info'        => 'border-sky-500/20 bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400',
    'muted'       => 'border-border bg-muted text-muted-foreground',
];

$variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<span {{ $attributes->merge(['class' => "$baseClass $variantClass $class"]) }}>
    {{ $slot }}
</span>
