@props([
 'id' => '',
 'title' => '',
 'description' => '',
 'size' => 'md',
 'show' => false,
])

@php
 $sizes = [
 'sm' => 'sm:max-w-sm',
 'md' => 'sm:max-w-lg',
 'lg' => 'sm:max-w-2xl',
 'xl' => 'sm:max-w-4xl',
 ];
 $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div
 x-data="{ open: {{ $show ? 'true' : 'false' }} }"
 x-show="open"
 x-cloak
 @if($id) id="{{ $id }}" @endif
 {{ $attributes }}
>
 <!-- Overlay -->
 <div
 x-show="open"
 x-transition:enter="transition ease-out duration-200"
 x-transition:enter-start="opacity-0"
 x-transition:enter-end="opacity-100"
 x-transition:leave="transition ease-in duration-150"
 x-transition:leave-start="opacity-100"
 x-transition:leave-end="opacity-0"
 class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
 @click="open = false"
 ></div>

 <!-- Content -->
 <div
 x-show="open"
 x-transition:enter="transition ease-out duration-200"
 x-transition:enter-start="opacity-0 scale-95"
 x-transition:enter-end="opacity-100 scale-100"
 x-transition:leave="transition ease-in duration-150"
 x-transition:leave-start="opacity-100 scale-100"
 x-transition:leave-end="opacity-0 scale-95"
 class="fixed inset-0 z-50 flex items-center justify-center p-4"
 >
 <div
 class="w-full {{ $sizeClass }} rounded-xl border border-border bg-background p-6 shadow-lg"
 @click.outside="open = false"
 >
 @if($title)
 <div class="flex flex-col gap-2 mb-4">
 <div class="flex items-center justify-between">
 <h3 class="text-lg font-semibold text-foreground">{{ $title }}</h3>
 <button @click="open = false" class="rounded-sm opacity-70 hover:opacity-100 transition-opacity">
 <i data-lucide="x" class="w-4 h-4"></i>
 </button>
 </div>
 @if($description)
 <p class="text-sm text-muted-foreground">{{ $description }}</p>
 @endif
 </div>
 @endif

 <div class="max-h-[70vh] overflow-y-auto">
 {{ $slot }}
 </div>

 @if(isset($footer))
 <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end mt-6 pt-4 border-t border-border">
 {{ $footer }}
 </div>
 @endif
 </div>
 </div>
</div>
