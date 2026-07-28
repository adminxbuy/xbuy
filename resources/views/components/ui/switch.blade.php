@props([
    'checked' => false,
    'disabled' => false,
    'label' => '',
])

<label class="relative inline-flex items-center cursor-pointer" {{ $disabled ? 'disabled' : '' }}>
    <input
        type="checkbox"
        class="sr-only peer"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    <div class="w-9 h-5 bg-input peer-focus:ring-2 peer-focus:ring-ring/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary dark:bg-input/80"></div>
    @if($label)
        <span class="ms-2 text-sm font-medium text-foreground">{{ $label }}</span>
    @endif
</label>
