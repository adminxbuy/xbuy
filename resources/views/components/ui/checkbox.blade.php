@props([
    'checked' => false,
    'disabled' => false,
])

<label class="flex items-center gap-2 cursor-pointer" {{ $disabled ? 'disabled' : '' }}>
    <input
        type="checkbox"
        class="w-4 h-4 rounded border border-input shadow-xs transition-colors focus:border-ring focus:ring-2 focus:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50 bg-transparent checked:bg-primary checked:border-primary"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    @if(isset($slot) && $slot)
        <span class="text-sm text-foreground">{{ $slot }}</span>
    @endif
</label>
