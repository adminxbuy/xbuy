@props(['icon' => 'inbox', 'title' => 'No results', 'description' => 'No data found.'])

<div class="flex flex-col items-center justify-center py-12 text-center">
    <div class="rounded-full bg-muted p-4 mb-4">
        <i data-lucide="{{ $icon }}" class="w-8 h-8 text-muted-foreground"></i>
    </div>
    <h3 class="text-sm font-semibold text-foreground mb-1">{{ $title }}</h3>
    <p class="text-xs text-muted-foreground max-w-sm">{{ $description }}</p>
</div>
