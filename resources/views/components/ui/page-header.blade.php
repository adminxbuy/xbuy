@props(['title' => '', 'description' => ''])

<div class="flex flex-wrap items-end justify-between gap-4">
 <div>
 <h2 class="text-2xl font-bold tracking-tight text-foreground">{{ $title }}</h2>
 @if($description)
 <p class="text-sm text-muted-foreground mt-1">{{ $description }}</p>
 @endif
 </div>
 @if(isset($actions))
 <div class="flex items-center gap-2">
 {{ $actions }}
 </div>
 @endif
</div>
