@props(['id', 'title', 'description' => null, 'size' => 'md'])

@php
$sizes = [
    'sm'  => 'max-w-sm',
    'md'  => 'max-w-lg',
    'lg'  => 'max-w-2xl',
    'xl'  => 'max-w-4xl',
];
$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

{{--
    Accessible event-driven dialog/modal component.

    To OPEN:  $dispatch('open-dialog',  { id: '{{ $id }}' })
    To CLOSE: $dispatch('close-dialog', { id: '{{ $id }}' })

    Alpine.js handles backdrop click, Escape key, and enter/leave transitions.
    Body scroll lock is applied automatically via the 'open' state watcher.

    Usage:
    <x-ui.dialog id="confirm-delete" title="Delete Item" description="This cannot be undone.">
        <x-ui.button variant="destructive" @click="$dispatch('close-dialog', { id: 'confirm-delete' })">
            Confirm Delete
        </x-ui.button>
    </x-ui.dialog>

    <x-ui.button @click="$dispatch('open-dialog', { id: 'confirm-delete' })">Delete</x-ui.button>
--}}

<div
    x-data="{ open: false }"
    x-show="open"
    x-cloak
    @open-dialog.window="if ($event.detail.id === '{{ $id }}') { open = true; document.body.style.overflow = 'hidden'; }"
    @close-dialog.window="if ($event.detail.id === '{{ $id }}') { open = false; document.body.style.overflow = ''; }"
    @keydown.escape.window="open = false; document.body.style.overflow = '';"
    class="relative z-50"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm"
        @click="open = false; document.body.style.overflow = '';"
        aria-hidden="true"
    ></div>

    {{-- Dialog panel --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            class="relative w-full {{ $sizeClass }} rounded-xl border border-border bg-card p-6 shadow-xl"
            @click.stop
            role="dialog"
            aria-modal="true"
            aria-labelledby="dialog-title-{{ $id }}"
        >
            {{-- Header --}}
            <div class="flex flex-col space-y-1.5 pb-4">
                <h2 id="dialog-title-{{ $id }}" class="text-lg font-semibold tracking-tight text-foreground">
                    {{ $title }}
                </h2>
                @if($description)
                    <p class="text-sm text-muted-foreground">{{ $description }}</p>
                @endif
            </div>

            {{-- Close button --}}
            <button
                @click="open = false; document.body.style.overflow = '';"
                class="absolute right-4 top-4 rounded-sm opacity-70 transition-opacity hover:opacity-100 focus:outline-none focus:ring-1 focus:ring-ring"
                aria-label="Close dialog"
            >
                <i data-lucide="x" class="size-4 text-muted-foreground"></i>
            </button>

            {{-- Content --}}
            <div>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
