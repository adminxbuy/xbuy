@props([
    'type' => 'text',
    'error' => null,
])

<input
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'flex h-9 w-full min-w-0 rounded-lg border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50' . ($error ? ' border-destructive focus:border-destructive focus:ring-destructive/50' : '')
    ]) }}
/>

@if($error)
    <p class="text-xs text-destructive mt-1">{{ $error }}</p>
@endif
