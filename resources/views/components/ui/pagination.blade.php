@props(['paginator'])

@if($paginator->hasPages())
    <div class="flex items-center justify-between px-2 py-4">
        <div class="text-xs text-muted-foreground">
            Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
        </div>
        <div class="flex items-center gap-1">
            @if($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium border border-border bg-muted text-muted-foreground cursor-not-allowed opacity-50">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </a>
            @endif

            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium text-muted-foreground">{{ $element }}</span>
                @endif

                @if(is_array($element))
                    @foreach($element as $page => $url)
                        @if($page == $paginator->currentPage())
                            <span class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium bg-primary text-primary-foreground">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            @else
                <span class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-lg text-xs font-medium border border-border bg-muted text-muted-foreground cursor-not-allowed opacity-50">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </span>
            @endif
        </div>
    </div>
@endif
