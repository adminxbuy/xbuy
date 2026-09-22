@props(['align' => 'left', 'muted' => false, 'numeric' => false])

{{-- Table cell with the shared padding/alignment used across admin tables. --}}

@php
$alignClass = match ($align) {
    'right'  => 'text-right',
    'center' => 'text-center',
    default  => 'text-left',
};

$classes = 'p-4 align-middle text-sm text-foreground border-b border-border/60 transition-colors ' . $alignClass;

if ($muted) {
    $classes .= ' text-muted-foreground';
}
if ($numeric) {
    $classes .= ' tabular-nums';
}
@endphp

<td {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</td>
