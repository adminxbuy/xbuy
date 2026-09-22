@props([
    'checked'  => false,
    'disabled' => false,
    'label'    => '',
    'name'     => '',
    'value'    => '1',
])

<label class="relative inline-flex items-center cursor-pointer select-none {{ $disabled ? 'opacity-50 pointer-events-none' : '' }}">
    <input
        type="checkbox"
        class="sr-only peer"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    >
    <div class="w-10 h-5 bg-input peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-ring/50 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-border after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary dark:bg-input/80"></div>
    @if($label)
        <span class="ms-2 text-sm font-medium text-foreground">{{ $label }}</span>
    @endif
</label>
