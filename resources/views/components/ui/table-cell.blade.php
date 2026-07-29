@props(['align' => 'left', 'muted' => false, 'numeric' => false])

{{-- Table cell with the shared padding/alignment used across admin tables. --}}

@php
 $alignClass = match ($align) {
 'right' => 'text-right',
 'center' => 'text-center',
 default => 'text-left',
 };
 $classes = 'px-4 py-3 align-middle ' . $alignClass;
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
