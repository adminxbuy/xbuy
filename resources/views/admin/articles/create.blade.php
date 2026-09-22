@extends('layouts.admin')

@section('title', 'Write Article')
@section('page_title', 'Write New Article')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{  title: '',  slug: '',  generateSlug() {  this.slug = this.title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');  },
 activeTab: 'write'
}">
 <!-- Back Button -->
 <div class="mb-6">
 <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition-colors">
 <i data-lucide="arrow-left" class="size-4"></i>
 <span>Back to Articles</span>
 </a>
 </div>

 <!-- Form -->
 <form action="{{ route('admin.articles.store') }}" method="POST" enctype="multipart/form-data">
 @csrf
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
 <!-- Left 2 Cols: Title, Subtitle, Content Builder -->
 <div class="lg:col-span-2 space-y-6">
 <!-- Core Details Block -->
 <div class="bg-card border border-border rounded-xl p-6 space-y-4 ">
 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Article Title *</label>
 <input type="text" name="title" x-model="title" @input="generateSlug" required placeholder="e.g. 5 Tips for Building Your First PC"
 class="w-full px-4 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:border-transparent font-medium">
 @error('title') <p class="text-muted-foreground text-xs mt-1">{{ $message }}</p> @enderror
 </div>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Custom Slug</label>
 <input type="text" name="slug" x-model="slug" placeholder="custom-url-path"
 class="w-full px-4 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:border-transparent text-sm">
 @error('slug') <p class="text-muted-foreground text-xs mt-1">{{ $message }}</p> @enderror
 </div>
 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Category</label>
 <input type="text" name="category" placeholder="e.g. Guides, Hardware, Deals"
 class="w-full px-4 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:border-transparent text-sm">
 </div>
 </div>

 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Subtitle / Brief Excerpt</label>
 <input type="text" name="subtitle" placeholder="A short, catchy description to show in listings..."
 class="w-full px-4 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:border-transparent text-sm">
 </div>
 </div>

 <!-- Rich Text Editor Container -->
 <div class="bg-card border border-border rounded-xl overflow-hidden flex flex-col min-h-[400px]">
 <!-- Editor Tabs Toolbar -->
 <div class="bg-muted border-b border-border px-4 py-2 flex items-center justify-between">
 <div class="flex space-x-1">
 <button type="button" @click="activeTab = 'write'"  class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
 :class="activeTab === 'write' ? 'bg-card border border-border text-foreground' : 'text-muted-foreground hover:text-foreground'">
 Write (HTML/Standard)
 </button>
 <button type="button" @click="activeTab = 'preview'"  class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all"
 :class="activeTab === 'preview' ? 'bg-card border border-border text-foreground' : 'text-muted-foreground hover:text-foreground'">
 Live HTML Preview
 </button>
 </div>
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">HTML supported</span>
 </div>

 <!-- Editor Body -->
 <div class="flex-1 flex flex-col p-6" x-show="activeTab === 'write'">
 <textarea name="content" id="article-content" x-ref="contentArea" class="tinymce-editor w-full flex-1 min-h-[300px] p-4 border border-border rounded-lg focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring focus:border-transparent text-sm font-normal" placeholder="Start writing your article content here..."></textarea>
 </div>

 <!-- Live Preview Panel -->
 <div class="flex-1 p-6 overflow-y-auto max-h-[400px]" x-show="activeTab === 'preview'">
 <div class="prose max-w-none text-foreground text-sm" x-html="$refs.contentArea ? $refs.contentArea.value : '<p class=\'text-muted-foreground\'>Nothing to preview yet...</p>'">
 </div>
 </div>
 </div>
 </div>

 <!-- Right 1 Col: Cover Image, SEO settings, Publish Toggle -->
 <div class="space-y-6">
 <!-- Cover Image Block -->
 <div class="bg-card border border-border rounded-xl p-6 space-y-4 " x-data="{ imageType: 'file' }">
 <h3 class="text-sm font-bold text-foreground uppercase tracking-wider pb-2 border-b border-border">Cover Image</h3>
  <div class="flex space-x-3 mb-2">
 <label class="flex items-center space-x-1.5 cursor-pointer">
 <input type="radio" x-model="imageType" value="file" class="text-primary focus:ring-ring">
 <span class="text-xs font-semibold text-foreground">Upload File</span>
 </label>
 <label class="flex items-center space-x-1.5 cursor-pointer">
 <input type="radio" x-model="imageType" value="url" class="text-primary focus:ring-ring">
 <span class="text-xs font-semibold text-foreground">Image URL</span>
 </label>
 </div>

 <div x-show="imageType === 'file'">
 <input type="file" name="cover_image_file" class="w-full text-xs text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-muted file:text-foreground hover:file:bg-muted">
 <p class="text-[10px] text-muted-foreground mt-1">Recommended size: 800x450px (WebP/JPEG/PNG)</p>
 </div>

 <div x-show="imageType === 'url'">
 <input type="text" name="cover_image_url" placeholder="https://example.com/image.jpg"
 class="w-full px-3 py-2 border border-border rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
 </div>
 </div>

 <!-- SEO Optimization Block -->
 <div class="bg-card border border-border rounded-xl p-6 space-y-4 ">
 <h3 class="text-sm font-bold text-foreground uppercase tracking-wider pb-2 border-b border-border">SEO Settings (Optional)</h3>
 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Meta Title</label>
 <input type="text" name="seo_title" placeholder="Google Search Title"
 class="w-full px-3 py-2 border border-border rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring">
 </div>
 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Meta Description</label>
 <textarea name="seo_description" rows="3" placeholder="Brief search snippet description..."
 class="w-full px-3 py-2 border border-border rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring resize-none"></textarea>
 </div>
 </div>

 <!-- Publish & Status Block -->
 <div class="bg-card border border-border rounded-xl p-6 space-y-4 ">
 <h3 class="text-sm font-bold text-foreground uppercase tracking-wider pb-2 border-b border-border">Publish Options</h3>
  <div class="flex items-center justify-between" x-data="{ isPublished: false }">
 <span class="text-xs font-semibold text-foreground">Publish Instantly</span>
 <input type="hidden" name="is_published" value="0">
 <button type="button" @click="isPublished = !isPublished" class="focus:outline-none">
 <span class="w-9 h-5 rounded-full p-0.5 transition-colors duration-200 ease-in-out shrink-0 flex items-center"  :class="isPublished ? 'bg-emerald-500' : 'bg-muted'">
 <span class="block w-4 h-4 rounded-full bg-card transform transition-transform duration-200 ease-in-out"  :class="isPublished ? 'translate-x-4' : 'translate-x-0'"></span>
 </span>
 <input type="checkbox" name="is_published" value="1" x-model="isPublished" class="hidden">
 </button>
 </div>

 <div>
 <label class="block text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Scheduled Date & Time (Optional)</label>
 <input type="text" name="published_at"  class="flatpickr-datetime w-full px-3 py-2 border border-border rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring" placeholder="Select date & time">
 </div>

 <button type="submit" class="w-full bg-primary text-primary-foreground hover:bg-primary/90 font-medium py-2.5 rounded-lg text-sm transition-all shadow-md">
 Save Article
 </button>
 </div>
 </div>
 </div>
 </form>
</div>
@endsection
