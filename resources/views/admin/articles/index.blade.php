@extends('layouts.admin')

@section('title', 'Blog Articles')

@section('content')
<div class="p-6 space-y-6" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Articles / Blog Publishing</h1>
            <p class="text-zinc-500 text-sm">Write, edit, and publish content that automatically feeds into the public frontend blog.</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.trash.index', 'articles') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
                <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                <span>Trash ({{ $trashedArticles->count() }})</span>
            </a>
            <a href="{{ route('admin.articles.create') }}" class="bg-black hover:bg-zinc-900 text-white font-bold py-2.5 px-5 rounded-xl text-sm flex items-center space-x-2 transition-all shadow-md active:scale-95">
                <i data-lucide="plus-circle" class="w-4.5 h-4.5"></i>
                <span>Write Article</span>
            </a>
        </div>
    </div>

    <!-- Stats summary grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-5 flex items-center space-x-4">
            <div class="p-3 bg-zinc-200/55 text-zinc-800 rounded-xl">
                <i data-lucide="book-open" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Total Articles</p>
                <h3 class="text-2xl font-bold text-zinc-900">{{ \App\Models\Article::count() }}</h3>
            </div>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-5 flex items-center space-x-4">
            <div class="p-3 bg-emerald-50 text-emerald-705 border border-emerald-200/60 rounded-xl">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Published</p>
                <h3 class="text-2xl font-bold text-emerald-700">{{ \App\Models\Article::where('is_published', true)->count() }}</h3>
            </div>
        </div>
        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-5 flex items-center space-x-4">
            <div class="p-3 bg-amber-50 text-amber-705 border border-amber-200/60 rounded-xl">
                <i data-lucide="eye" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-xs font-semibold text-zinc-500 uppercase tracking-wide">Total Article Views</p>
                <h3 class="text-2xl font-bold text-zinc-700">{{ \App\Models\Article::sum('views_count') }}</h3>
            </div>
        </div>
    </div>

    <!-- Filters and Search Toolbar -->
    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <form action="{{ route('admin.articles.index') }}" method="GET" class="w-full flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search title, excerpt or content..." 
                       class="w-full pl-9 pr-4 py-2 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 w-4 h-4"></i>
            </div>
            
            <select name="status" class="py-2 px-3 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 bg-white">
                <option value="">All Statuses</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>

            <select name="category" class="py-2 px-3 border border-zinc-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 bg-white">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>

            <div class="flex gap-2">
                <button type="submit" class="bg-zinc-900 hover:bg-zinc-800 text-white font-bold py-2 px-4 rounded-xl text-sm transition-all">Filter</button>
                @if(request()->anyFilled(['search', 'status', 'category']))
                    <a href="{{ route('admin.articles.index') }}" class="bg-zinc-150 hover:bg-zinc-200 text-zinc-700 font-bold py-2 px-4 rounded-xl text-sm transition-all flex items-center justify-center">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center space-x-2 text-yellow-900 font-medium text-sm">
            <i data-lucide="info" class="w-4 h-4 shrink-0"></i>
            <span>You have selected <strong x-text="selectedIds.length"></strong> articles. Choose an action:</span>
        </div>
        <form action="{{ route('admin.articles.bulk-action') }}" method="POST" class="flex items-center space-x-2 w-full sm:w-auto">
            @csrf
            <template x-for="id in selectedIds">
                <input type="hidden" name="ids[]" :value="id">
            </template>
            <select name="action" x-model="bulkAction" required class="py-1.5 px-3 border border-yellow-300 rounded-xl text-xs bg-white focus:outline-none focus:ring-1 focus:ring-yellow-500">
                <option value="">Choose bulk action...</option>
                <option value="publish">Publish Selected</option>
                <option value="draft">Move to Draft</option>
                <option value="delete">Move to Trash (Soft Delete)</option>
            </select>
            <button type="submit" :disabled="!bulkAction" class="bg-zinc-900 hover:bg-zinc-800 text-white disabled:opacity-50 text-white font-bold px-4 py-1.5 rounded-xl text-xs transition-all shadow-sm">
                Apply
            </button>
            <button type="button" @click="selectedIds = []; selectAll = false" class="text-zinc-500 hover:text-zinc-800 text-xs px-2.5 py-1.5">
                Clear
            </button>
        </form>
    </div>

    <!-- Table Container -->
    <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-50/75 border-b border-zinc-200">
                        <th class="py-4 px-5 w-12 text-center">
                            <input type="checkbox" x-model="selectAll" 
                                   @change="if(selectAll) { selectedIds = @json($articles->pluck('id')->toArray()) } else { selectedIds = [] }"
                                   class="rounded border-zinc-300 text-yellow-500 focus:ring-yellow-400 w-4 h-4">
                        </th>
                        <th class="py-4 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-wide">Article Title</th>
                        <th class="py-4 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-wide">Category</th>
                        <th class="py-4 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-wide">Views</th>
                        <th class="py-4 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-wide">Publish Status</th>
                        <th class="py-4 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-wide">Date Created</th>
                        <th class="py-4 px-5 text-xs font-semibold text-zinc-500 uppercase tracking-wide text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($articles as $article)
                        <tr class="hover:bg-zinc-50/50 transition-colors">
                            <td class="py-4 px-5 text-center">
                                <input type="checkbox" :value="{{ $article->id }}" x-model="selectedIds"
                                       @change="selectAll = (selectedIds.length === {{ $articles->count() }})"
                                       class="rounded border-zinc-300 text-yellow-500 focus:ring-yellow-400 w-4 h-4">
                            </td>
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3.5">
                                    <div class="w-12 h-10 rounded-lg overflow-hidden border border-zinc-200 shrink-0 bg-zinc-100">
                                        @if($article->cover_image)
                                            <img src="{{ $article->cover_image }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-zinc-400 bg-zinc-50">
                                                <i data-lucide="image" class="w-4 h-4"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-sm text-zinc-900">{{ $article->title }}</div>
                                        @if($article->subtitle)
                                            <div class="text-xs text-zinc-400 line-clamp-1 mt-0.5">{{ $article->subtitle }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800">
                                    {{ $article->category ?: 'General' }}
                                </span>
                            </td>
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-1.5 text-zinc-600 text-sm font-semibold">
                                    <i data-lucide="eye" class="w-4 h-4 text-zinc-400"></i>
                                    <span>{{ number_format($article->views_count) }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-5" x-data="{ 
                                isPublished: {{ $article->is_published ? 'true' : 'false' }}, 
                                async toggle() {
                                    try {
                                        let response = await fetch('{{ route('admin.articles.toggle-status', $article->id) }}', {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                'Content-Type': 'application/json'
                                            }
                                        });
                                        let res = await response.json();
                                        if(res.success) {
                                            this.isPublished = res.is_published;
                                        }
                                    } catch(e) {
                                        console.error(e);
                                    }
                                }
                            }">
                                <button @click="toggle()" class="focus:outline-none flex items-center space-x-2">
                                    <span class="w-9 h-5 rounded-full p-0.5 transition-colors duration-200 ease-in-out shrink-0" 
                                          :class="isPublished ? 'bg-emerald-500' : 'bg-zinc-300'">
                                        <span class="block w-4 h-4 rounded-full bg-white shadow-sm transform transition-transform duration-200 ease-in-out" 
                                              :class="isPublished ? 'translate-x-4' : 'translate-x-0'"></span>
                                    </span>
                                    <span class="text-xs font-semibold" :class="isPublished ? 'text-emerald-700' : 'text-zinc-500'" x-text="isPublished ? 'Published' : 'Draft'"></span>
                                </button>
                            </td>
                            <td class="py-4 px-5">
                                <div class="text-xs text-zinc-500 font-medium">{{ $article->created_at->format('M d, Y h:i A') }}</div>
                            </td>
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end space-x-2">
                                    <a href="{{ route('blog.show', $article->slug) }}" target="_blank" class="p-1.5 text-zinc-400 hover:text-zinc-650 hover:bg-zinc-50 rounded-lg transition-all" title="View Frontend">
                                        <i data-lucide="external-link" class="w-4.5 h-4.5"></i>
                                    </a>
                                    <a href="{{ route('admin.articles.edit', $article->id) }}" class="p-1.5 text-zinc-400 hover:text-zinc-800 hover:bg-zinc-150 rounded-lg transition-all" title="Edit Article">
                                        <i data-lucide="edit-3" class="w-4.5 h-4.5"></i>
                                    </a>
                                    <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this article?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-zinc-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all" title="Delete Article">
                                            <i data-lucide="trash-2" class="w-4.5 h-4.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-zinc-400">
                                <i data-lucide="book-open" class="w-10 h-10 mx-auto stroke-1.5 mb-2.5"></i>
                                <p class="text-sm font-semibold">No articles found</p>
                                <p class="text-xs text-zinc-400 mt-1">Get started by writing your first blog article.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($articles->hasPages())
            <div class="px-5 py-4 border-t border-zinc-200">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
