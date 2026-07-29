@props([
 'placeholder' => 'Select...',
 'options' => [],
 'error' => null,
])

<select
 {{ $attributes->merge([
 'class' => 'flex h-9 w-full min-w-0 rounded-lg border border-input bg-transparent px-3 py-1 text-sm transition-[color,box-shadow] outline-none focus:border-ring focus:ring-2 focus:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50' . ($error ? ' border-destructive' : '')
 ]) }}
>
 @if($placeholder)
 <option value="">{{ $placeholder }}</option>
 @endif
 {{ $slot }}
</select>

@if($error)
 <p class="text-xs text-destructive mt-1">{{ $error }}</p>
@endif
