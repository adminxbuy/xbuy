@props([
    'align' => 'right',
    'width' => '48',
])

<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute z-50 mt-2 rounded-xl border border-border bg-card shadow-lg py-1 text-sm"
        :class="{{ $align === 'right' ? "'right-0'" : "'left-0'" }}"
        style="min-width: {{ $width }}rem;"
        x-cloak
        @click="open = false"
    >
        {{ $slot }}
    </div>
</div>
