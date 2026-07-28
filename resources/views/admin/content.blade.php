@extends('layouts.admin')

@section('title', 'Content Manager')
@section('page_title', 'Asset & Content Manager')

@section('header_actions')
    @php
        $totalTrashedFiles = count($trashedContent['images']) + count($trashedContent['pdfs']) + count($trashedContent['videos']);
    @endphp
    <a href="{{ route('admin.trash.index', 'content') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
        <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
        <span>Trash ({{ $totalTrashedFiles }})</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    tab: 'images',
    confirmDelete: false,
    deletePath: '',
    copiedName: '',
    copyToClipboard: function(text, name) {
        navigator.clipboard.writeText(window.location.origin + text);
        this.copiedName = name;
        setTimeout(() => { this.copiedName = ''; }, 2000);
    },
    triggerDelete: function(path) {
        this.deletePath = path;
        this.confirmDelete = true;
    }
}">
    <!-- Header Summary -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-muted border border-border rounded-xl p-5">
        <div>
            <p class="text-xs text-muted-foreground uppercase tracking-wider font-bold mb-1">Public Assets Directory</p>
            <p class="text-sm text-muted-foreground leading-relaxed">
                Directly manage uploaded files (PDFs, Images, and Videos) in the public workspace to clear unused assets or reference them on settings/pages.
            </p>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center space-x-2">
            <i data-lucide="check-circle" class="w-4.5 h-4.5 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center space-x-2">
            <i data-lucide="alert-octagon" class="w-4.5 h-4.5 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Main Navigation Tabs -->
    <div class="flex border-b border-border overflow-x-auto pb-1 gap-1">
        <button type="button" @click="tab = 'images'" 
                :class="tab === 'images' ? 'border-foreground font-semibold text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground'" 
                class="px-4 py-3 border-b-2 text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center space-x-2 whitespace-nowrap">
            <i data-lucide="image" class="w-4 h-4"></i>
            <span>Images</span>
            <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-muted border border-border text-muted-foreground rounded-full font-bold">
                {{ count($content['images']) }}
            </span>
        </button>
        <button type="button" @click="tab = 'pdfs'" 
                :class="tab === 'pdfs' ? 'border-foreground font-semibold text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground'" 
                class="px-4 py-3 border-b-2 text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center space-x-2 whitespace-nowrap">
            <i data-lucide="file-text" class="w-4 h-4"></i>
            <span>PDFs</span>
            <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-muted border border-border text-muted-foreground rounded-full font-bold">
                {{ count($content['pdfs']) }}
            </span>
        </button>
        <button type="button" @click="tab = 'videos'" 
                :class="tab === 'videos' ? 'border-foreground font-semibold text-foreground' : 'border-transparent text-muted-foreground hover:text-foreground'" 
                class="px-4 py-3 border-b-2 text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center space-x-2 whitespace-nowrap">
            <i data-lucide="video" class="w-4 h-4"></i>
            <span>Videos</span>
            <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-muted border border-border text-muted-foreground rounded-full font-bold">
                {{ count($content['videos']) }}
            </span>
        </button>
    </div>

    <!-- Active Tab Container -->
    <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
        <!-- Direct File Upload Form -->
        <form action="{{ route('admin.content.upload') }}" method="POST" enctype="multipart/form-data" class="mb-8 p-5 bg-muted border border-border rounded-xl space-y-4">
            @csrf
            <input type="hidden" name="type" :value="tab">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="space-y-1">
                    <h4 class="font-bold text-foreground text-xs uppercase tracking-wider flex items-center">
                        <i data-lucide="upload-cloud" class="w-4 h-4 mr-2 text-muted-foreground"></i>
                        Upload to Category: <span class="text-[#ffd747] capitalize ml-1" x-text="tab"></span>
                    </h4>
                    <p class="text-[10px] text-muted-foreground">File size must be under 20MB.</p>
                </div>

                <div class="flex items-center space-x-3 w-full sm:w-auto">
                    <label class="flex-1 sm:flex-none px-4 py-2 border border-border bg-card hover:bg-muted text-foreground rounded-xl text-xs font-semibold cursor-pointer transition-all flex items-center justify-center space-x-1.5 shadow-sm">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Choose File</span>
                        <input type="file" name="file" required class="hidden" onchange="this.form.submit()">
                    </label>
                </div>
            </div>
        </form>

        <!-- Dynamic Grid Content -->
        @foreach(['images', 'pdfs', 'videos'] as $cType)
            <div x-show="tab === '{{ $cType }}'" x-cloak>
                @if(count($content[$cType]) === 0)
                    <div class="text-center py-16 text-muted-foreground border-2 border-dashed border-border rounded-xl">
                        <i data-lucide="folder-x" class="w-12 h-12 mx-auto mb-3 text-muted-foreground"></i>
                        <p class="text-sm font-semibold">No assets found in <span class="capitalize">{{ $cType }}</span></p>
                        <p class="text-[11px] text-muted-foreground mt-1">Upload a file above to add content.</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach($content[$cType] as $file)
                            <div class="group bg-card border border-border hover:border-border rounded-xl overflow-hidden shadow-sm flex flex-col justify-between transition-all hover:shadow-md">
                                <!-- Preview Header -->
                                <div class="bg-muted flex items-center justify-center p-4 border-b border-border h-36 overflow-hidden relative">
                                    @if($cType === 'images')
                                        <img src="{{ $file['url'] }}" class="max-h-full max-w-full object-contain group-hover:scale-[1.02] transition-transform duration-250">
                                    @elseif($cType === 'pdfs')
                                        <div class="flex flex-col items-center justify-center text-red-500">
                                            <i data-lucide="file-text" class="w-12 h-12"></i>
                                            <span class="text-[9px] uppercase font-bold tracking-wider text-red-600 bg-red-50 px-2 py-0.5 rounded-full mt-2 border border-red-200">PDF Document</span>
                                        </div>
                                    @else
                                        <div class="flex flex-col items-center justify-center text-indigo-500">
                                            <i data-lucide="video" class="w-12 h-12"></i>
                                            <span class="text-[9px] uppercase font-bold tracking-wider text-indigo-650 bg-indigo-50 px-2 py-0.5 rounded-full mt-2 border border-indigo-200">Video File</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- File Metadata -->
                                <div class="p-3.5 space-y-2.5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <h5 class="text-xs font-semibold text-foreground truncate" title="{{ $file['name'] }}">{{ $file['name'] }}</h5>
                                        <div class="flex items-center justify-between text-[9px] text-muted-foreground mt-1.5">
                                            <span class="font-medium bg-muted border border-border px-1.5 py-0.5 rounded-full" x-text="'{{ $file['size'] }}'"></span>
                                            <span>{{ date('d M Y, H:i', strtotime($file['updated_at'])) }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-2.5 border-t border-border">
                                        <!-- Copy URL Button -->
                                        <button type="button" @click="copyToClipboard('{{ $file['url'] }}', '{{ $file['name'] }}')"
                                                class="flex-1 py-1.5 border border-border hover:border-border text-foreground bg-card hover:bg-muted rounded-lg text-[10px] font-bold transition-all flex items-center justify-center space-x-1 shadow-sm">
                                            <template x-if="copiedName === '{{ $file['name'] }}'">
                                                <span class="text-emerald-600 flex items-center space-x-1">
                                                    <i data-lucide="check" class="w-3 h-3"></i>
                                                    <span>Copied!</span>
                                                </span>
                                            </template>
                                            <template x-if="copiedName !== '{{ $file['name'] }}'">
                                                <span class="flex items-center space-x-1">
                                                    <i data-lucide="copy" class="w-3 h-3"></i>
                                                    <span>Copy URL</span>
                                                </span>
                                            </template>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" @click="triggerDelete('{{ $file['url'] }}')"
                                                class="px-2 py-1.5 bg-red-50 hover:bg-red-100 text-red-650 border border-red-100 hover:border-red-200 rounded-lg text-[10px] font-bold transition-all flex items-center justify-center">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="confirmDelete" x-transition x-cloak class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
        <div class="bg-card rounded-xl max-w-sm w-full shadow-2xl border border-border" @click.away="confirmDelete = false">
            <div class="p-6 text-center space-y-4">
                <div class="w-12 h-12 bg-red-50 border border-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto shadow-sm">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-sm font-bold text-foreground uppercase tracking-wider">Confirm File Deletion</h3>
                    <p class="text-xs text-muted-foreground leading-relaxed">
                        Are you sure you want to permanently delete this file? This action is irreversible.
                    </p>
                </div>
            </div>
            
            <form action="{{ route('admin.content.delete') }}" method="POST" class="p-4 border-t border-border flex justify-end space-x-2 bg-muted rounded-b-2xl">
                @csrf
                @method('DELETE')
                <input type="hidden" name="path" :value="deletePath">
                
                <button type="button" @click="confirmDelete = false" class="px-4 py-2 border border-border bg-card hover:bg-muted text-foreground rounded-xl text-xs font-semibold shadow-sm transition-all">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-750 text-primary-foreground rounded-xl text-xs font-semibold shadow-sm border border-black/10 transition-all active:scale-[0.98]">
                    Delete File
                </button>
            </form>
        </div>
    </div>
    </div>

</div>
@endsection
