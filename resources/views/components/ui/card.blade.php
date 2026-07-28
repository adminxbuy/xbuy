@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card text-card-foreground shadow-sm' . ($padding ? ' p-6' : '')]) }}>
    {{ $slot }}
</div>
