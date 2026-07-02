@extends('layouts.admin')

@section('title', 'Edit Article')

@section('content')
<div class="p-6 max-w-6xl mx-auto" x-data="{ 
    title: '{{ addslashes($article->title) }}', 
    slug: '{{ $article->slug }}', 
    generateSlug() { 
        this.slug = this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''); 
    },
    activeTab: 'write'
}">
    <!-- Header -->
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('admin.articles.index') }}" class="p-2 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-lg transition-all">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Edit Article</h1>
            <p class="text-zinc-500 text-sm">Update content, fine-tune SEO parameters, and republish.</p>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.articles.update', $article->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Title, Subtitle, Content Builder -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Core Details Block -->
                <div class="bg-white border border-zinc-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Article Title *</label>
                        <input type="text" name="title" x-model="title" required placeholder="e.g. 5 Tips for Building Your PC"
                               class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent font-medium">
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Custom Slug</label>
                            <input type="text" name="slug" x-model="slug" required placeholder="custom-url-path"
                                   class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent text-sm">
                            @error('slug') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Category</label>
                            <input type="text" name="category" value="{{ $article->category }}" placeholder="e.g. Guides, Hardware, Deals"
                                   class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Subtitle / Brief Excerpt</label>
                        <input type="text" name="subtitle" value="{{ $article->subtitle }}" placeholder="A short, catchy description..."
                               class="w-full px-4 py-2.5 border border-zinc-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent text-sm">
                    </div>
                </div>

                <!-- Rich Text Editor Container -->
                <div class="bg-white border border-zinc-200 rounded-xl overflow-hidden shadow-sm flex flex-col min-h-[400px]">
                    <!-- Editor Tabs Toolbar -->
                    <div class="bg-zinc-50 border-b border-zinc-200 px-4 py-2 flex items-center justify-between">
                        <div class="flex space-x-1">
                            <button type="button" @click="activeTab = 'write'" 
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
                                    :class="activeTab === 'write' ? 'bg-white shadow-sm border border-zinc-200 text-zinc-900' : 'text-zinc-500 hover:text-zinc-800'">
                                Write (HTML/Standard)
                            </button>
                            <button type="button" @click="activeTab = 'preview'" 
                                    class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
                                    :class="activeTab === 'preview' ? 'bg-white shadow-sm border border-zinc-200 text-zinc-900' : 'text-zinc-500 hover:text-zinc-800'">
                                Live HTML Preview
                            </button>
                        </div>
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">HTML supported</span>
                    </div>

                    <!-- Editor Body -->
                    <div class="flex-1 flex flex-col p-6" x-show="activeTab === 'write'">
                        <textarea name="content" id="article-content" x-ref="contentArea" class="tinymce-editor w-full flex-1 min-h-[300px] p-4 border border-zinc-200 rounded-xl focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:border-transparent text-sm font-normal" placeholder="Start writing article content...">{{ $article->content }}</textarea>
                    </div>

                    <!-- Live Preview Panel -->
                    <div class="flex-1 p-6 overflow-y-auto max-h-[400px]" x-show="activeTab === 'preview'">
                        <div class="prose max-w-none text-zinc-800 text-sm" x-html="$refs.contentArea ? $refs.contentArea.value : '<p class=\'text-zinc-400\'>Nothing to preview yet...</p>'">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Cover Image, SEO settings, Publish Toggle -->
            <div class="space-y-6">
                <!-- Cover Image Block -->
                <div class="bg-white border border-zinc-200 rounded-xl p-6 space-y-4 shadow-sm" x-data="{ imageType: '{{ filter_var($article->cover_image, FILTER_VALIDATE_URL) ? 'url' : 'file' }}' }">
                    <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider pb-2 border-b border-zinc-100">Cover Image</h3>
                    
                    <div class="flex space-x-3 mb-2">
                        <label class="flex items-center space-x-1.5 cursor-pointer">
                            <input type="radio" x-model="imageType" value="file" class="text-yellow-500 focus:ring-yellow-400">
                            <span class="text-xs font-semibold text-zinc-700">Upload File</span>
                        </label>
                        <label class="flex items-center space-x-1.5 cursor-pointer">
                            <input type="radio" x-model="imageType" value="url" class="text-yellow-500 focus:ring-yellow-400">
                            <span class="text-xs font-semibold text-zinc-700">Image URL</span>
                        </label>
                    </div>

                    @if($article->cover_image)
                        <div class="w-full aspect-[16/9] border border-zinc-200 rounded-xl overflow-hidden mb-2 bg-zinc-50">
                            <img src="{{ $article->cover_image }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div x-show="imageType === 'file'">
                        <input type="file" name="cover_image_file" class="w-full text-xs text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-zinc-50 file:text-zinc-800 hover:file:bg-zinc-100">
                    </div>

                    <div x-show="imageType === 'url'">
                        <input type="text" name="cover_image_url" value="{{ filter_var($article->cover_image, FILTER_VALIDATE_URL) ? $article->cover_image : '' }}" placeholder="https://example.com/image.jpg"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950">
                    </div>
                </div>

                <!-- SEO Optimization Block -->
                <div class="bg-white border border-zinc-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider pb-2 border-b border-zinc-100">SEO Settings</h3>
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Meta Title</label>
                        <input type="text" name="seo_title" value="{{ $article->seo_title }}" placeholder="Google Search Title"
                               class="w-full px-3 py-2 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1">Meta Description</label>
                        <textarea name="seo_description" rows="3" placeholder="Brief search snippet description..."
                                  class="w-full px-3 py-2 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 resize-none">{{ $article->seo_description }}</textarea>
                    </div>
                </div>

                <!-- Publish & Status Block -->
                <div class="bg-white border border-zinc-200 rounded-xl p-6 space-y-4 shadow-sm">
                    <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider pb-2 border-b border-zinc-100">Publish Options</h3>
                    
                    <div class="flex items-center justify-between" x-data="{ isPublished: {{ $article->is_published ? 'true' : 'false' }} }">
                        <span class="text-xs font-semibold text-zinc-700">Published Status</span>
                        <input type="hidden" name="is_published" value="0">
                        <button type="button" @click="isPublished = !isPublished" class="focus:outline-none">
                            <span class="w-9 h-5 rounded-full p-0.5 transition-colors duration-200 ease-in-out shrink-0 flex items-center" 
                                  :class="isPublished ? 'bg-emerald-500' : 'bg-zinc-300'">
                                <span class="block w-4 h-4 rounded-full bg-white shadow-sm transform transition-transform duration-200 ease-in-out" 
                                      :class="isPublished ? 'translate-x-4' : 'translate-x-0'"></span>
                            </span>
                            <input type="checkbox" name="is_published" value="1" x-model="isPublished" class="hidden">
                        </button>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider mb-1.5">Scheduled Date & Time</label>
                        <input type="text" name="published_at" value="{{ $article->published_at ? $article->published_at->format('Y-m-d H:i') : '' }}"
                               class="flatpickr-datetime w-full px-3 py-2 border border-zinc-200 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950" placeholder="Select date & time">
                    </div>

                    <button type="submit" class="w-full bg-black hover:bg-zinc-900 text-white font-bold py-2.5 rounded-xl text-sm transition-all shadow-md active:scale-95">
                        Update Article
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
