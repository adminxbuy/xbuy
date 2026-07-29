@props(['paginator'])

{{--
    Pagination. Renders a compact window of page links.

    Note: this deliberately builds its own $elements array rather than relying on
    the $elements variable Laravel injects into its built-in pagination views —
    that variable is not passed to anonymous components, so referencing it here
    would raise an undefined-variable error.
--}}

@if($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $onEachSide = 1;

        // Build a windowed page list with '...' separators.
        $pages = [];
        if ($last <= 7) {
            $pages = range(1, $last);
        } else {
            $pages[] = 1;
            $start = max(2, $current - $onEachSide);
            $end = min($last - 1, $current + $onEachSide);
            if ($start > 2) {
                $pages[] = '...';
            }
            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
            if ($end < $last - 1) {
                $pages[] = '...';
            }
            $pages[] = $last;
        }

        $navClass = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md text-xs font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors';
        $navDisabledClass = 'inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md text-xs font-medium border border-border bg-muted text-muted-foreground cursor-not-allowed opacity-50';
    @endphp

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-border px-4 py-3">
        <p class="text-xs text-muted-foreground">
            Showing <span class="font-medium text-foreground">{{ $paginator->firstItem() }}</span>
            to <span class="font-medium text-foreground">{{ $paginator->lastItem() }}</span>
            of <span class="font-medium text-foreground">{{ $paginator->total() }}</span> results
        </p>

        <nav class="flex items-center gap-1" role="navigation" aria-label="Pagination">
            @if($paginator->onFirstPage())
                <span class="{{ $navDisabledClass }}" aria-disabled="true">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="{{ $navClass }}" aria-label="Previous page">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                </a>
            @endif

            @foreach($pages as $page)
                @if($page === '...')
                    <span class="inline-flex items-center justify-center h-8 min-w-8 px-2 text-xs text-muted-foreground">&hellip;</span>
                @elseif($page == $current)
                    <span class="inline-flex items-center justify-center h-8 min-w-8 px-2 rounded-md text-xs font-medium bg-primary text-primary-foreground" aria-current="page">{{ $page }}</span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="{{ $navClass }}">{{ $page }}</a>
                @endif
            @endforeach

            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="{{ $navClass }}" aria-label="Next page">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            @else
                <span class="{{ $navDisabledClass }}" aria-disabled="true">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </span>
            @endif
        </nav>
    </div>
@endif
