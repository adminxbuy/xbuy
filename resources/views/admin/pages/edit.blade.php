@extends('layouts.admin')

@section('title', 'Edit Page')
@section('page_title', 'Edit Policy Page: ' . $page->title)

@section('header_actions')
    <div class="flex items-center space-x-2">
        <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="inline"
            onsubmit="return confirm('Are you sure you want to delete this page?')">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="px-3.5 py-2 bg-red-50 hover:bg-red-100 text-red-650 font-semibold rounded-xl text-xs transition-all flex items-center space-x-1.5 shadow-sm border border-red-200 active:scale-[0.98]">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete Page</span>
            </button>
        </form>
        <a href="/v1/pages/{{ $page->slug }}" target="_blank"
            class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-semibold rounded-xl text-xs transition-all flex items-center space-x-1.5 shadow-sm border border-zinc-200">
            <i data-lucide="eye" class="w-3.5 h-3.5 text-zinc-550"></i>
            <span>View Live Page</span>
        </a>
        <a href="{{ route('admin.pages.index') }}"
            class="px-3.5 py-2 border border-zinc-200 text-zinc-700 hover:bg-zinc-50 font-semibold rounded-xl text-xs transition-all flex items-center justify-center">
            Cancel
        </a>
        <button type="submit" form="edit-page-form"
            class="px-4.5 py-2 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold rounded-xl text-xs shadow-sm border border-black/10 transition-all flex items-center justify-center active:scale-[0.98]">
            Save Changes
        </button>
    </div>
@endsection

@section('content')
    <div class="max-w-6xl mx-auto">
        <form id="edit-page-form" action="{{ route('admin.pages.update', $page->id) }}" method="POST"
            class="space-y-6 text-sm">
            @csrf
            @method('PUT')

            <!-- Header / Metadata Form -->
            <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-150">
                    <h5 class="text-xs font-bold text-zinc-700 uppercase tracking-wider flex items-center">
                        <i data-lucide="info" class="w-4 h-4 mr-1.5 text-zinc-500"></i> Page Settings & Details
                    </h5>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-1.5 col-span-2 sm:col-span-1">
                        <label for="title" class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Page
                            Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $page->title) }}" required
                            placeholder="e.g. Terms of Use"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 text-zinc-800 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>

                    <div class="space-y-1.5 col-span-2 sm:col-span-1">
                        <label for="slug" class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">URL
                            Slug</label>
                        <input type="text" id="slug" name="slug" value="{{ old('slug', $page->slug) }}" required
                            placeholder="e.g. terms-of-use"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>

                    <div class="space-y-1.5 col-span-2">
                        <label for="category_id"
                            class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Parent Category</label>
                        <select id="category_id" name="category_id"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                            <option value="">-- No Category (Will not show in categorised columns) --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $page->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Policy Editor Container -->
            <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 p-6 space-y-4">
                <label for="content" class="block text-xs font-bold text-zinc-550 uppercase tracking-wider">Page HTML
                    Content</label>
                <textarea id="content" name="content"
                    class="tinymce-editor w-full p-3 border border-zinc-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent text-sm font-normal min-h-[450px]">{{ old('content', $page->content) }}</textarea>
            </div>

            <!-- SEO Metadata Settings Section -->
            <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 p-6 space-y-4">
                <h5 class="text-xs font-bold text-zinc-700 uppercase tracking-wider flex items-center">
                    <i data-lucide="search" class="w-4 h-4 mr-1.5 text-zinc-500"></i> SEO Metadata Config
                </h5>
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-1.5 col-span-2 sm:col-span-1">
                        <label for="meta_title" class="block text-xs font-bold text-zinc-400 uppercase tracking-wider">Meta
                            Title Tag</label>
                        <input type="text" id="meta_title" name="meta_title"
                            value="{{ old('meta_title', $page->meta_title) }}" placeholder="Meta title tag"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>
                    <div class="space-y-1.5 col-span-2 sm:col-span-1">
                        <label for="meta_description"
                            class="block text-xs font-bold text-zinc-400 uppercase tracking-wider">Meta Description
                            Tag</label>
                        <input type="text" id="meta_description" name="meta_description"
                            value="{{ old('meta_description', $page->meta_description) }}"
                            placeholder="Meta description tag"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Publishing Status Checkbox -->
            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $page->is_active) ? 'checked' : '' }} class="rounded text-[#09090b] focus:ring-zinc-950 border-zinc-300">
                <label for="is_active" class="text-xs font-semibold text-zinc-700 select-none cursor-pointer">Published /
                    Active Status</label>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
<script>
    (function() {
        // Check if the page was loaded via back/forward navigation
        const isBackNavigation = (window.performance && window.performance.navigation && window.performance.navigation.type === 2) ||
            (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward");
        
        if (isBackNavigation) {
            window.location.href = "{{ route('admin.pages.index') }}";
        }
    })();
</script>
@endpush