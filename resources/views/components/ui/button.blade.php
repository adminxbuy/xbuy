@props([
    'variant' => 'default',
    'size' => 'default',
    'as' => 'button',
    'href' => null,
])

@php
    $baseClass = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-lg text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';
    
    $variants = [
        'default' => 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm',
        'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-sm',
        'outline' => 'border border-border bg-transparent hover:bg-muted text-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'ghost' => 'hover:bg-muted text-foreground',
        'link' => 'text-foreground underline-offset-4 hover:underline',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm',
    ];
    
    $sizes = [
        'default' => 'h-9 px-4 py-2',
        'sm' => 'h-8 px-3 text-xs',
        'lg' => 'h-10 px-6',
        'icon' => 'h-9 w-9',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
    $sizeClass = $sizes[$size] ?? $sizes['default'];
@endphp

@if($as === 'a' && $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => "$baseClass $variantClass $sizeClass"]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['class' => "$baseClass $variantClass $sizeClass"]) }}>
        {{ $slot }}
    </button>
@endif
