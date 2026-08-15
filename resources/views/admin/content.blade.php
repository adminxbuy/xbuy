@extends('layouts.admin')

@section('title', 'File Manager')
@section('page_title', 'File Manager')

@section('header_actions')
 @php
 $totalTrashedFiles = count($trashedContent['images']) + count($trashedContent['pdfs']) + count($trashedContent['videos']);
 $totalFiles = count($content['images']) + count($content['pdfs']) + count($content['videos']);
 @endphp
 <a href="{{ route('admin.trash.index', 'content') }}" class="inline-flex items-center gap-2 h-9 px-4 rounded-lg text-xs font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors">
 <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
 <span>Trash ({{ $totalTrashedFiles }})</span>
 </a>
@endsection

@section('content')
<div x-data="{  tab: 'images',
 viewMode: 'grid',
 searchQuery: '',
 confirmDelete: false,
 deletePath: '',
 deleteName: '',
 copiedName: '',
 selectedFiles: [],
 selectAll: false,
 copyToClipboard: function(text, name) {
 navigator.clipboard.writeText(window.location.origin + text);
 this.copiedName = name;
 setTimeout(() => { this.copiedName = ''; }, 2000);
 },
 triggerDelete: function(path, name) {
 this.deletePath = path;
 this.deleteName = name || '';
 this.confirmDelete = true;
 },
 toggleSelect: function(fileName) {
 const idx = this.selectedFiles.indexOf(fileName);
 if (idx === -1) this.selectedFiles.push(fileName);
 else this.selectedFiles.splice(idx, 1);
 },
 get filteredFiles() {
 const files = this.content[this.tab] || [];
 if (!this.searchQuery) return files;
 return files.filter(f => f.name.toLowerCase().includes(this.searchQuery.toLowerCase()));
 },
 content: @json($content)
}" class="space-y-4">

 <!-- Stats Cards -->
 <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
 <div class="rounded-xl border border-border bg-card p-4 ">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-xs font-medium text-muted-foreground">Total Files</p>
 <p class="text-2xl font-bold text-foreground mt-1">{{ $totalFiles }}</p>
 </div>
 <div class="p-2.5 rounded-lg bg-muted text-muted-foreground">
 <i data-lucide="files" class="w-5 h-5"></i>
 </div>
 </div>
 </div>
 <div class="rounded-xl border border-border bg-card p-4 ">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-xs font-medium text-muted-foreground">Images</p>
 <p class="text-2xl font-bold text-foreground mt-1">{{ count($content['images']) }}</p>
 </div>
 <div class="p-2.5 rounded-lg bg-muted text-muted-foreground">
 <i data-lucide="image" class="w-5 h-5"></i>
 </div>
 </div>
 </div>
 <div class="rounded-xl border border-border bg-card p-4 ">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-xs font-medium text-muted-foreground">PDFs</p>
 <p class="text-2xl font-bold text-foreground mt-1">{{ count($content['pdfs']) }}</p>
 </div>
 <div class="p-2.5 rounded-lg bg-muted text-muted-foreground">
 <i data-lucide="file-text" class="w-5 h-5"></i>
 </div>
 </div>
 </div>
 <div class="rounded-xl border border-border bg-card p-4 ">
 <div class="flex items-center justify-between">
 <div>
 <p class="text-xs font-medium text-muted-foreground">Videos</p>
 <p class="text-2xl font-bold text-foreground mt-1">{{ count($content['videos']) }}</p>
 </div>
 <div class="p-2.5 rounded-lg bg-muted text-muted-foreground">
 <i data-lucide="video" class="w-5 h-5"></i>
 </div>
 </div>
 </div>
 </div>

 <!-- Tabs + Search + View Toggle + Upload -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
 <!-- Tabs -->
 <div class="flex items-center gap-1 p-1 bg-muted rounded-lg w-fit">
 <button @click="tab = 'images'; selectedFiles = []"  :class="tab === 'images' ? 'bg-background text-foreground ' : 'text-muted-foreground hover:text-foreground'"
 class="px-4 py-2 text-xs font-medium rounded-md transition-all flex items-center gap-2">
 <i data-lucide="image" class="w-3.5 h-3.5"></i>
 Images
 <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-muted text-muted-foreground">{{ count($content['images']) }}</span>
 </button>
 <button @click="tab = 'pdfs'; selectedFiles = []"
 :class="tab === 'pdfs' ? 'bg-background text-foreground ' : 'text-muted-foreground hover:text-foreground'"
 class="px-4 py-2 text-xs font-medium rounded-md transition-all flex items-center gap-2">
 <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
 PDFs
 <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-muted text-muted-foreground">{{ count($content['pdfs']) }}</span>
 </button>
 <button @click="tab = 'videos'; selectedFiles = []"
 :class="tab === 'videos' ? 'bg-background text-foreground ' : 'text-muted-foreground hover:text-foreground'"
 class="px-4 py-2 text-xs font-medium rounded-md transition-all flex items-center gap-2">
 <i data-lucide="video" class="w-3.5 h-3.5"></i>
 Videos
 <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-muted text-muted-foreground">{{ count($content['videos']) }}</span>
 </button>
 </div>

 <!-- Search + View Toggle + Upload -->
 <div class="flex items-center gap-2">
 <div class="relative">
 <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-model="searchQuery" placeholder="Search files..."  class="h-9 pl-9 pr-4 rounded-lg border border-input bg-transparent px-3 py-1 text-sm focus:border-ring focus:ring-2 focus:ring-ring/50 outline-none w-48">
 </div>
 <div class="flex items-center gap-1 p-1 bg-muted rounded-lg">
 <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-background text-foreground ' : 'text-muted-foreground'" class="p-1.5 rounded-md transition-all">
 <i data-lucide="grid-3x3" class="w-4 h-4"></i>
 </button>
 <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-background text-foreground ' : 'text-muted-foreground'" class="p-1.5 rounded-md transition-all">
 <i data-lucide="list" class="w-4 h-4"></i>
 </button>
 </div>
 <form action="{{ route('admin.content.upload') }}" method="POST" enctype="multipart/form-data" id="upload-form">
 @csrf
 <input type="hidden" name="type" x-model="tab" value="images">
 <label class="inline-flex items-center gap-2 h-9 px-4 rounded-lg text-xs font-medium bg-primary text-primary-foreground hover:bg-primary/90 cursor-pointer transition-colors">
 <i data-lucide="upload" class="w-3.5 h-3.5"></i>
 <span>Upload</span>
 <input type="file" name="files[]" multiple required class="hidden" @change="$el.form.querySelector('input[name=type]').value = tab; $el.form.submit()">
 </label>
 </form>
 </div>
 </div>

 <!-- Bulk Actions (when files selected) -->
 <div x-show="selectedFiles.length > 0" x-cloak
 x-transition:enter="transition ease-out duration-200"
 x-transition:enter-start="opacity-0 -translate-y-2"
 x-transition:enter-end="opacity-100 translate-y-0"
 class="flex items-center gap-3 p-3 rounded-xl border border-border bg-card ">
 <span class="text-xs font-medium text-foreground">
 <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-primary text-primary-foreground text-[10px] font-bold" x-text="selectedFiles.length"></span>
 files selected
 </span>
 <button @click="selectedFiles = []; selectAll = false" class="text-xs text-muted-foreground hover:text-foreground transition-colors">Clear</button>
 </div>

 <!-- File Grid/List View -->
 <div class="rounded-xl border border-border bg-card overflow-hidden">
 <div class="p-4 border-b border-border flex items-center justify-between">
 <h3 class="text-sm font-semibold text-foreground flex items-center gap-2">
 <i data-lucide="folder" class="w-4 h-4 text-muted-foreground"></i>
 <span class="capitalize" x-text="tab"></span>
 <span class="text-xs font-medium text-muted-foreground" x-text="'(' + filteredFiles.length + ' files)'"></span>
 </h3>
 </div>

 @foreach(['images', 'pdfs', 'videos'] as $cType)
 <div x-show="tab === '{{ $cType }}'" x-cloak>
 @if(count($content[$cType]) === 0)
 <div class="flex flex-col items-center justify-center py-16 text-center">
 <div class="rounded-full bg-muted p-4 mb-4">
 <i data-lucide="upload-cloud" class="w-8 h-8 text-muted-foreground"></i>
 </div>
 <h3 class="text-sm font-semibold text-foreground mb-1">No {{ $cType }} uploaded</h3>
 <p class="text-xs text-muted-foreground max-w-sm">Upload files using the button above to get started.</p>
 </div>
 @else
 <!-- Grid View -->
 <div x-show="viewMode === 'grid'" class="p-4">
 <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
 @foreach($content[$cType] as $file)
 <div class="group relative rounded-xl border border-border bg-background hover:border-ring/50 overflow-hidden transition-all hover:shadow-md"
 x-show="!searchQuery || '{{ strtolower($file['name']) }}'.includes(searchQuery.toLowerCase())">
 <!-- Preview -->
 <div class="bg-muted flex items-center justify-center h-32 overflow-hidden">
 @if($cType === 'images')
 <img src="{{ $file['url'] }}" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-200">
 @elseif($cType === 'pdfs')
 <div class="flex flex-col items-center justify-center text-muted-foreground">
 <i data-lucide="file-text" class="w-10 h-10"></i>
 </div>
 @else
 <div class="flex flex-col items-center justify-center text-muted-foreground">
 <i data-lucide="video" class="w-10 h-10"></i>
 </div>
 @endif
 </div>
 <!-- Info -->
 <div class="p-3 space-y-2">
 <h5 class="text-xs font-medium text-foreground truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</h5>
 <div class="flex items-center justify-between text-[10px] text-muted-foreground">
 <span class="px-1.5 py-0.5 rounded bg-muted text-muted-foreground">{{ $file['size'] }}</span>
 <span>{{ date('d M', strtotime($file['updated_at'])) }}</span>
 </div>
 <div class="flex items-center gap-1.5 pt-2 border-t border-border">
 <button type="button" @click="copyToClipboard('{{ $file['url'] }}', '{{ $file['name'] }}')"
 class="flex-1 h-7 inline-flex items-center justify-center gap-1 rounded-md text-[10px] font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors">
 <template x-if="copiedName === '{{ $file['name'] }}'">
 <span class="flex items-center gap-1 text-muted-foreground"><i data-lucide="check" class="w-3 h-3"></i> Copied</span>
 </template>
 <template x-if="copiedName !== '{{ $file['name'] }}'">
 <span class="flex items-center gap-1"><i data-lucide="copy" class="w-3 h-3"></i> Copy</span>
 </template>
 </button>
 <button type="button" @click="triggerDelete('{{ $file['url'] }}', '{{ $file['name'] }}')"
 class="h-7 w-7 inline-flex items-center justify-center rounded-md text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors">
 <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
 </button>
 </div>
 </div>
 </div>
 @endforeach
 </div>
 </div>

 <!-- List View -->
 <div x-show="viewMode === 'list'" class="overflow-x-auto">
 <table class="w-full text-left">
 <thead>
 <tr class="border-b border-border bg-muted">
 <th class="h-10 px-4 text-xs font-medium text-muted-foreground uppercase tracking-wider">Name</th>
 <th class="h-10 px-4 text-xs font-medium text-muted-foreground uppercase tracking-wider">Size</th>
 <th class="h-10 px-4 text-xs font-medium text-muted-foreground uppercase tracking-wider">Modified</th>
 <th class="h-10 px-4 text-xs font-medium text-muted-foreground uppercase tracking-wider text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-border">
 @foreach($content[$cType] as $file)
 <tr class="hover:bg-muted/50 transition-colors"
 x-show="!searchQuery || '{{ strtolower($file['name']) }}'.includes(searchQuery.toLowerCase())">
 <td class="px-4 py-3">
 <div class="flex items-center gap-3">
 <div class="w-8 h-8 rounded-lg bg-muted flex items-center justify-center flex-shrink-0">
 @if($cType === 'images')
 <img src="{{ $file['url'] }}" class="w-full h-full object-cover rounded-lg">
 @elseif($cType === 'pdfs')
 <i data-lucide="file-text" class="w-4 h-4 text-muted-foreground"></i>
 @else
 <i data-lucide="video" class="w-4 h-4 text-muted-foreground"></i>
 @endif
 </div>
 <span class="text-xs font-medium text-foreground truncate max-w-[200px]" title="{{ $file['name'] }}">{{ $file['name'] }}</span>
 </div>
 </td>
 <td class="px-4 py-3"><span class="text-xs text-muted-foreground">{{ $file['size'] }}</span></td>
 <td class="px-4 py-3"><span class="text-xs text-muted-foreground">{{ date('d M Y, H:i', strtotime($file['updated_at'])) }}</span></td>
 <td class="px-4 py-3 text-right">
 <div class="flex items-center justify-end gap-1">
 <button type="button" @click="copyToClipboard('{{ $file['url'] }}', '{{ $file['name'] }}')"
 class="h-7 w-7 inline-flex items-center justify-center rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors">
 <i data-lucide="copy" class="w-3.5 h-3.5"></i>
 </button>
 <button type="button" @click="triggerDelete('{{ $file['url'] }}', '{{ $file['name'] }}')"
 class="h-7 w-7 inline-flex items-center justify-center rounded-md text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors">
 <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
 </button>
 </div>
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 @endif
 </div>
 @endforeach
 </div>

 <!-- Delete Confirmation Modal -->
 <div x-show="confirmDelete" x-cloak>
 <div class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm" @click="confirmDelete = false"></div>
 <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
 <div class="w-full max-w-sm rounded-xl border border-border bg-background p-6 shadow-lg" @click.outside="confirmDelete = false"
 x-transition:enter="transition ease-out duration-200"
 x-transition:enter-start="opacity-0 scale-95"
 x-transition:enter-end="opacity-100 scale-100">
 <div class="flex flex-col items-center text-center space-y-4">
 <div class="w-12 h-12 rounded-full bg-destructive/10 flex items-center justify-center">
 <i data-lucide="alert-triangle" class="w-6 h-6 text-destructive"></i>
 </div>
 <div>
 <h3 class="text-sm font-semibold text-foreground">Delete File</h3>
 <p class="text-xs text-muted-foreground mt-1">
 Are you sure you want to delete <span class="font-medium text-foreground" x-text="deleteName"></span>? This action cannot be undone.
 </p>
 </div>
 </div>
 <form action="{{ route('admin.content.delete') }}" method="POST" class="flex gap-2 mt-6">
 @csrf
 @method('DELETE')
 <input type="hidden" name="path" :value="deletePath">
 <button type="button" @click="confirmDelete = false" class="flex-1 h-9 inline-flex items-center justify-center rounded-lg text-xs font-medium border border-border bg-background hover:bg-muted text-foreground transition-colors">
 Cancel
 </button>
 <button type="submit" class="flex-1 h-9 inline-flex items-center justify-center rounded-lg text-xs font-medium bg-destructive text-destructive-foreground hover:bg-destructive/90 transition-colors">
 Delete
 </button>
 </form>
 </div>
 </div>
 </div>
</div>
@endsection
