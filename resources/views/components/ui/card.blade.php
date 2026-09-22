{{--
    Card wrapper. Sub-components (card-header, card-title, card-description, card-content,
    card-footer) handle internal spacing. Use `padding` prop for legacy single-slot usage.

    Usage:
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title>Title</x-ui.card-title>
            <x-ui.card-description>Description</x-ui.card-description>
        </x-ui.card-header>
        <x-ui.card-content>
            Content
        </x-ui.card-content>
    </x-ui.card>
--}}

@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-card text-card-foreground shadow-sm' . ($padding ? ' p-6' : '')]) }}>
    {{ $slot }}
</div>
