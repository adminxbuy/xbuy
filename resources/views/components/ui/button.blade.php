@props([
    'variant'  => 'default', // default | destructive | outline | secondary | ghost | link
    'size'     => 'default', // default | sm | lg | icon
    'type'     => 'button',
    'disabled' => false,
    'as'       => 'button',
    'href'     => null,
])

@php
$base = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 select-none cursor-pointer active:scale-[0.98]';

$variants = [
    'default'     => 'bg-primary text-primary-foreground shadow hover:bg-primary/90',
    'destructive' => 'bg-destructive text-destructive-foreground shadow-sm hover:bg-destructive/90',
    'outline'     => 'border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground',
    'secondary'   => 'bg-secondary text-secondary-foreground shadow-sm hover:bg-secondary/80',
    'ghost'       => 'hover:bg-accent hover:text-accent-foreground',
    'link'        => 'text-primary underline-offset-4 hover:underline',
    'success'     => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700',
];

$sizes = [
    'default' => 'h-9 px-4 py-2',
    'sm'      => 'h-8 rounded-md px-3 text-xs',
    'lg'      => 'h-10 rounded-md px-8 text-base',
    'icon'    => 'h-9 w-9 p-0',
];

$variantClass = $variants[$variant] ?? $variants['default'];
$sizeClass    = $sizes[$size] ?? $sizes['default'];
$classes      = "$base $variantClass $sizeClass";
@endphp

@if($as === 'a' && $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
