@props([
 'variant' => 'default',
 'class' => ''
])

@php
 $baseClass = 'inline-flex items-center justify-center rounded-md border px-2 py-0.5 text-xs font-medium w-fit whitespace-nowrap shrink-0 gap-1 transition-colors';

 $variants = [
 'default' => 'border-transparent bg-primary text-primary-foreground',
 'secondary' => 'border-border bg-muted text-muted-foreground',
 'destructive' => 'border-transparent bg-destructive text-destructive-foreground',
 'outline' => 'border-border text-foreground',
 'success' => 'border-border bg-muted text-foreground',
 'warning' => 'border-border bg-muted text-foreground',
 'info' => 'border-border bg-muted text-foreground',
 'muted' => 'border-border bg-muted text-muted-foreground',
 ];

 $variantClass = $variants[$variant] ?? $variants['default'];
@endphp

<span {{ $attributes->merge(['class' => "$baseClass $variantClass $class"]) }}>
 {{ $slot }}
</span>
