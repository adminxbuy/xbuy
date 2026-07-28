@props(['error' => null])

<textarea
    {{ $attributes->merge([
        'class' => 'flex min-h-16 w-full rounded-lg border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground focus:border-ring focus:ring-2 focus:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 resize-y' . ($error ? ' border-destructive' : '')
    ]) }}
>{{ $slot }}</textarea>

@if($error)
    <p class="text-xs text-destructive mt-1">{{ $error }}</p>
@endif
