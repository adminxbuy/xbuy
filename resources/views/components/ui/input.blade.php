@props([
    'type'  => 'text',
    'error' => null,
])

<input
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'flex h-9 w-full min-w-0 rounded-md border shadow-sm '
            . ($error ? 'border-destructive focus-visible:ring-destructive/50' : 'border-input')
            . ' bg-transparent px-3 py-1 text-sm transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50'
    ]) }}
/>

@if($error)
    <p class="text-xs text-destructive mt-1">{{ $error }}</p>
@endif
