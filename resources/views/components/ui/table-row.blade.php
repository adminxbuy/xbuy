@props(['clickable' => false, 'href' => null])

{{--
    Table row. Adds the shared bottom border and hover treatment so rows look
    identical across every admin table. Set :clickable or pass an href to get
    the pointer affordance for row-level navigation.
--}}

@php
    $rowClass = 'border-b border-border transition-colors last:border-0 hover:bg-muted/50';
    if ($clickable || $href) {
        $rowClass .= ' cursor-pointer';
    }
@endphp

<tr
    @if($href) onclick="window.location='{{ $href }}'" @endif
    {{ $attributes->merge(['class' => $rowClass]) }}
>
    {{ $slot }}
</tr>
