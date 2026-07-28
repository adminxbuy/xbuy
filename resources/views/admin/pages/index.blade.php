@extends('layouts.admin')

@section('title', 'Policy Pages')
@section('page_title', 'Policy Pages')

@section('content')
    <div class="space-y-6 max-w-7xl" x-data="pageManager()">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1 w-full sm:w-auto">
                <p class="text-sm text-muted-foreground">Manage, create, and update public policy and info pages grouped by parent
                    categories.</p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="openCategoryModal()"
                    class="bg-card hover:bg-muted text-foreground font-semibold py-2.5 px-5 rounded-lg ring-0 border border-border transition-all flex items-center space-x-2 text-sm active:scale-[0.98]">
                    <i data-lucide="folder-plus" class="w-4 h-4"></i>
                    <span>Create Parent Category</span>
                </button>
                <button @click="openAddModal()"
                    class="bg-primary text-primary-foreground hover:bg-primary/90  font-semibold py-2.5 px-5 rounded-lg ring-0 border border-black/10 transition-all flex items-center space-x-2 text-sm active:scale-[0.98]">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Create New Page</span>
                </button>
            </div>
        </div>

        <!-- Load SortableJS from CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

        <!-- View Switcher Tabs -->
        <div class="flex border-b border-border mt-2 bg-muted/50 p-1.5 rounded-xl border">
            <button type="button" @click="tab = 'hierarchy'"
                :class="tab === 'hierarchy' ? 'bg-card shadow-sm border border-border text-foreground font-bold' : 'text-muted-foreground hover:text-foreground border-transparent'"
                class="flex-1 py-2 rounded-lg text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center justify-center space-x-2">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                <span>Drag & Drop Footer Builder</span>
            </button>
            <button type="button" @click="tab = 'table'"
                :class="tab === 'table' ? 'bg-card shadow-sm border border-border text-foreground font-bold' : 'text-muted-foreground hover:text-foreground border-transparent'"
                class="flex-1 py-2 rounded-lg text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center justify-center space-x-2">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span>All Pages Table</span>
            </button>
            <button type="button" @click="tab = 'trash'"
                :class="tab === 'trash' ? 'bg-card shadow-sm border border-border text-foreground font-bold' : 'text-muted-foreground hover:text-foreground border-transparent'"
                class="flex-1 py-2 rounded-lg text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center justify-center space-x-2">
                <i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>
                <span class="text-red-650">Trash Bin</span>
                <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">
                    {{ $trashedCategories->count() + $trashedPages->count() }}
                </span>
            </button>
        </div>

        <!-- Drag & Drop Footer Builder View -->
        <div x-show="tab === 'hierarchy'" class="space-y-6" x-init="initSortable()">
            <!-- Categories Drag Container (Vertical Stack) -->
            <div id="categories-sortable" class="space-y-4">
                @foreach($categories as $category)
                    <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden flex flex-col category-card transition-all hover:shadow-md"
                        data-category-id="{{ $category->id }}" x-data="{ expanded: false }">

                        <!-- Category Header (Collapsible & Draggable) -->
                        <div
                            class="bg-muted border-b border-border px-5 py-4 flex items-center justify-between select-none">
                            <div class="flex items-center space-x-3 flex-1 cursor-pointer" @click="expanded = !expanded">
                                <span
                                    class="cursor-move handle-category p-1 text-muted-foreground hover:text-foreground transition-colors">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </span>
                                <i data-lucide="chevron-right" class="w-4 h-4 text-muted-foreground transition-transform duration-200"
                                    :class="expanded ? 'rotate-90' : ''"></i>
                                <span
                                    class="font-black text-foreground text-sm tracking-wide uppercase">{{ $category->name }}</span>
                                <span
                                    class="text-[11px] bg-muted border border-border px-2.5 py-0.5 rounded-full text-muted-foreground font-bold">
                                    {{ $pages->where('category_id', $category->id)->count() }} Links
                                </span>
                            </div>
                            <form action="{{ route('admin.pages.categories.destroy', $category->id) }}" method="POST"
                                onsubmit="return confirm('Are you sure you want to delete this category? Pages under this category will be unassigned.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="p-1 text-muted-foreground hover:text-red-650 hover:bg-red-50 rounded-lg transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Draggable Pages Inside Category (Collapsible Body) -->
                        <div x-show="expanded" x-collapse class="border-t border-border bg-muted/10 p-4">
                            <div class="space-y-2.5 min-h-[50px] pages-sortable transition-all"
                                data-category-id="{{ $category->id }}">
                                @forelse($pages->where('category_id', $category->id) as $page)
                                    <div class="bg-card border border-border rounded-xl p-3.5 flex items-center justify-between shadow-sm hover:border-foreground transition-all page-item cursor-move group"
                                        data-page-id="{{ $page->id }}">
                                        <div class="flex items-center space-x-3 truncate">
                                            <i data-lucide="grip-vertical"
                                                class="w-4 h-4 text-muted-foreground group-hover:text-muted-foreground transition-colors flex-shrink-0"></i>
                                            <div class="truncate">
                                                <span
                                                    class="font-bold text-foreground text-xs block truncate leading-tight">{{ $page->title }}</span>
                                                <span
                                                    class="text-[9px] text-muted-foreground font-mono block truncate mt-1">/v1/pages/{{ $page->slug }}</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2 flex-shrink-0 relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                                            <span
                                                class="w-2.5 h-2.5 rounded-full {{ $page->is_active ? 'bg-emerald-500' : 'bg-muted' }}"
                                                title="{{ $page->is_active ? 'Active / Published' : 'Draft' }}"></span>
                                            
                                            <!-- Three dots button -->
                                            <button type="button" @click="dropdownOpen = !dropdownOpen"
                                                class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none">
                                                <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div x-show="dropdownOpen" x-transition x-cloak
                                                class="absolute right-0 top-8 w-36 bg-card border border-border rounded-xl shadow-lg py-1.5 z-50 text-left">
                                                <!-- View option -->
                                                <a href="/v1/pages/{{ $page->slug }}" target="_blank"
                                                    class="flex items-center space-x-2 px-3.5 py-2 text-xs text-foreground hover:bg-muted font-bold transition-all">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                                    <span>View Page</span>
                                                </a>

                                                <!-- Edit option -->
                                                <a href="/admin/pages/{{ $page->id }}/edit"
                                                    class="flex items-center space-x-2 px-3.5 py-2 text-xs text-foreground hover:bg-muted font-bold transition-all">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                                    <span>Edit Page</span>
                                                </a>

                                                <div class="border-t border-border my-1"></div>

                                                <!-- Delete option -->
                                                <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="block"
                                                    onsubmit="return confirm('Are you sure you want to delete this page?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-full flex items-center space-x-2 px-3.5 py-2 text-xs text-red-650 hover:bg-red-50 font-bold transition-all text-left focus:outline-none">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-550"></i>
                                                        <span>Delete Page</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div
                                        class="text-center py-8 text-muted-foreground italic text-xs border border-dashed border-border rounded-xl bg-card/40 empty-placeholder flex flex-col items-center justify-center">
                                        <i data-lucide="link-2" class="w-5 h-5 mb-1.5 text-muted-foreground"></i>
                                        <span>Drag and drop page links here</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Uncategorized Pages Section -->
            @php
                $uncategorizedPages = $pages->where('category_id', null);
            @endphp
            <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                <div class="flex items-center space-x-2 mb-1">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-muted-foreground"></i>
                    <h3 class="font-bold text-foreground text-sm">Uncategorized Pages</h3>
                </div>
                <p class="text-xs text-muted-foreground mb-4 font-medium">These pages are not in the footer columns. Drag them into
                    any of the expanded Parent Category boxes above to add them to that column.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 pages-sortable min-h-[80px] p-2 bg-muted border border-dashed border-border rounded-xl"
                    data-category-id="">
                    @forelse($uncategorizedPages as $page)
                        <div class="bg-card border border-border rounded-xl p-3 flex items-center justify-between shadow-sm hover:border-foreground transition-all page-item cursor-move group"
                            data-page-id="{{ $page->id }}">
                            <div class="flex items-center space-x-2 truncate">
                                <i data-lucide="grip-vertical"
                                    class="w-3.5 h-3.5 text-muted-foreground group-hover:text-muted-foreground transition-colors flex-shrink-0"></i>
                                <div class="truncate">
                                    <span
                                        class="font-bold text-foreground text-[11px] block truncate leading-tight">{{ $page->title }}</span>
                                    <span
                                        class="text-[9px] text-muted-foreground font-mono block truncate mt-0.5">/v1/pages/{{ $page->slug }}</span>
                                </div>
                            </div>
                            <div class="flex items-center space-x-0.5 flex-shrink-0 relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
                                <span
                                    class="w-2 h-2 rounded-full mr-2 {{ $page->is_active ? 'bg-emerald-500' : 'bg-muted' }}"
                                    title="{{ $page->is_active ? 'Active' : 'Draft' }}"></span>
                                
                                <!-- Three dots button -->
                                <button type="button" @click="dropdownOpen = !dropdownOpen"
                                    class="p-1 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all focus:outline-none">
                                    <i data-lucide="more-vertical" class="w-3.5 h-3.5"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="dropdownOpen" x-transition x-cloak
                                    class="absolute right-0 top-7 w-36 bg-card border border-border rounded-xl shadow-lg py-1.5 z-50 text-left">
                                    <!-- View option -->
                                    <a href="/v1/pages/{{ $page->slug }}" target="_blank"
                                        class="flex items-center space-x-2 px-3.5 py-2 text-xs text-foreground hover:bg-muted font-bold transition-all">
                                        <i data-lucide="eye" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                        <span>View Page</span>
                                    </a>

                                    <!-- Edit option -->
                                    <a href="/admin/pages/{{ $page->id }}/edit"
                                        class="flex items-center space-x-2 px-3.5 py-2 text-xs text-foreground hover:bg-muted font-bold transition-all">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                        <span>Edit Page</span>
                                    </a>

                                    <div class="border-t border-border my-1"></div>

                                    <!-- Delete option -->
                                    <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="block"
                                        onsubmit="return confirm('Are you sure you want to delete this page?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full flex items-center space-x-2 px-3.5 py-2 text-xs text-red-650 hover:bg-red-50 font-bold transition-all text-left focus:outline-none">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-550"></i>
                                            <span>Delete Page</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="col-span-full py-8 text-center text-muted-foreground italic text-xs flex flex-col items-center justify-center bg-card/50 rounded-lg border border-border">
                            <i data-lucide="check-circle" class="w-6 h-6 mb-1 text-emerald-500"></i>
                            <span>All pages are categorized and will show in the footer!</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- All Pages Table View -->
        <div x-show="tab === 'table'" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Left Side: Pages Table (Col Span 8) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Filter & Search Bar -->
                <div
                    class="bg-muted border border-border rounded-xl p-4 flex flex-col sm:flex-row gap-4 justify-between items-center">
                    <div class="relative w-full sm:w-80">
                        <input type="text" x-model="searchQuery" placeholder="Search pages by title or slug..."
                            class="w-full pl-9 pr-4 py-2 border border-border rounded-xl bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none text-sm transition-all">
                        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                            <i data-lucide="search" class="w-4 h-4"></i>
                        </div>
                    </div>
                    <div class="text-xs text-muted-foreground font-medium" x-text="filteredPages().length + ' pages found'"></div>
                </div>

                <!-- Main Pages Table -->
                <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-muted border-b border-border text-[11px] font-bold text-muted-foreground uppercase tracking-wider">
                                    <th class="p-4 pl-6">Title & Slug</th>
                                    <th class="p-4">Category</th>
                                    <th class="p-4">Last Edited</th>
                                    <th class="p-4 text-center">Status</th>
                                    <th class="p-4 pr-6 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <template x-for="page in filteredPages()" :key="page.id">
                                    <tr class="hover:bg-muted/30 transition-colors text-sm text-foreground">
                                        <!-- Title & Slug -->
                                        <td class="p-4 pl-6">
                                            <span class="font-bold text-foreground" x-text="page.title"></span>
                                            <div class="text-[11px] text-muted-foreground font-mono mt-0.5"
                                                x-text="'/v1/pages/' + page.slug"></div>
                                        </td>
                                        <!-- Category -->
                                        <td class="p-4">
                                            <span x-show="page.category"
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100"
                                                x-text="page.category ? page.category.name : ''"></span>
                                            <span x-show="!page.category" class="text-xs text-muted-foreground italic">None</span>
                                        </td>
                                        <!-- Last Edited -->
                                        <td class="p-4">
                                            <div class="text-xs font-semibold text-foreground"
                                                x-text="page.last_edited_by ? (page.last_edited_by.name || 'Admin') : 'System'">
                                            </div>
                                            <div class="text-[10px] text-muted-foreground mt-0.5"
                                                x-text="formatDate(page.last_edited_at || page.updated_at)"></div>
                                        </td>
                                        <!-- Active Status -->
                                        <td class="p-4 text-center">
                                            <span
                                                :class="page.is_active ? 'bg-emerald-50 text-emerald-800 border border-emerald-250' : 'bg-muted text-muted-foreground border border-border'"
                                                class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                                                x-text="page.is_active ? 'Active' : 'Draft'"></span>
                                        </td>
                                        <!-- Actions -->
                                        <td class="p-4 pr-6 text-right relative" x-data="{ dropdownOpen: false }"
                                            @click.outside="dropdownOpen = false"
                                            x-init="setTimeout(() => lucide.createIcons(), 50)">
                                            <button type="button" @click="dropdownOpen = !dropdownOpen"
                                                class="p-2 text-muted-foreground hover:text-foreground hover:bg-muted rounded-xl transition-all inline-flex items-center focus:outline-none">
                                                <i data-lucide="more-vertical" class="w-4.5 h-4.5"></i>
                                            </button>

                                            <div x-show="dropdownOpen" x-transition
                                                class="absolute right-6 mt-1 w-40 bg-card border border-border rounded-xl shadow-lg py-1.5 z-20 text-left">
                                                <!-- View option -->
                                                <a :href="'/v1/pages/' + page.slug" target="_blank"
                                                    class="flex items-center space-x-2 px-4 py-2 text-xs text-foreground hover:bg-muted font-semibold transition-all">
                                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                                    <span>View</span>
                                                </a>

                                                <!-- Edit option -->
                                                <a :href="'/admin/pages/' + page.id + '/edit'"
                                                    class="flex items-center space-x-2 px-4 py-2 text-xs text-foreground hover:bg-muted font-semibold transition-all">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                                    <span>Edit</span>
                                                </a>

                                                <div class="border-t border-border my-1"></div>

                                                <!-- Delete option -->
                                                <template x-if="!page.is_protected">
                                                    <form :action="'/admin/pages/' + page.id" method="POST" class="block"
                                                        @submit="confirmDelete($event)">
                                                        @csrf
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit"
                                                            class="w-full flex items-center space-x-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50 font-semibold transition-all text-left focus:outline-none">
                                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
                                                            <span>Delete</span>
                                                        </button>
                                                    </form>
                                                </template>
                                                <template x-if="page.is_protected">
                                                    <button disabled
                                                        class="w-full flex items-center space-x-2 px-4 py-2 text-xs text-muted-foreground cursor-not-allowed font-semibold text-left focus:outline-none">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-muted-foreground"></i>
                                                        <span>Delete</span>
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="filteredPages().length === 0">
                                    <td colspan="5" class="p-12 text-center text-muted-foreground italic">
                                        No policy pages found matching your search.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Side: Parent Categories Manager (Col Span 4) -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-card border border-border rounded-xl p-6 shadow-sm">
                    <h3 class="font-bold text-foreground text-sm mb-1">Create Parent Category</h3>
                    <p class="text-xs text-muted-foreground mb-4">Add a category (e.g. SHOP, SUPPORT) to group policy pages in the
                        footer.</p>

                    <form action="{{ route('admin.pages.categories.store') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label for="category_name"
                                class="block text-[11px] font-bold text-muted-foreground uppercase tracking-wider mb-1.5">Category
                                Name</label>
                            <input type="text" id="category_name" name="name" required placeholder="e.g. SHOP"
                                class="w-full px-3.5 py-2 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none text-xs transition-all">
                        </div>
                        <button type="submit"
                            class="w-full py-2 bg-primary text-primary-foreground hover:bg-primary/90  font-semibold rounded-xl text-xs shadow-sm border border-black/10 transition-all active:scale-[0.98]">
                            Create Parent Category
                        </button>
                    </form>

                    <div class="border-t border-border my-6"></div>

                    <h3 class="font-bold text-foreground text-sm mb-3">Existing Categories</h3>
                    @if($categories->isEmpty())
                        <p class="text-xs text-muted-foreground italic">No parent categories created yet.</p>
                    @else
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            @foreach($categories as $category)
                                <div
                                    class="flex items-center justify-between p-2.5 bg-muted border border-border rounded-xl hover:bg-muted/50 transition-colors">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-bold text-foreground">{{ $category->name }}</span>
                                        <span class="text-[10px] text-muted-foreground font-mono">{{ $category->slug }}</span>
                                    </div>
                                    <form action="{{ route('admin.pages.categories.destroy', $category->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this category? Pages under this category will be unassigned.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 text-muted-foreground hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Trash Bin View -->
        <div x-show="tab === 'trash'" class="space-y-8">
            <div
                class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl flex items-start space-x-3 text-xs font-medium">
                <i data-lucide="alert-triangle" class="w-4.5 h-4.5 mt-0.5 flex-shrink-0 text-red-650"></i>
                <div>
                    <span class="font-bold">Trash Policy:</span> Deleted categories and pages are kept in the trash bin for
                    30 days. After 30 days, they will be automatically and permanently pruned from the database.
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Trashed Parent Categories -->
                <div class="bg-card border border-border rounded-xl p-6 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-border">
                        <h3 class="font-bold text-foreground text-sm flex items-center">
                            <i data-lucide="folder-archive" class="w-4 h-4 mr-2 text-muted-foreground"></i>
                            Trashed Parent Categories
                        </h3>
                        <span
                            class="text-[10px] font-bold bg-muted text-muted-foreground px-2.5 py-0.5 rounded-full border border-border">
                            {{ $trashedCategories->count() }} Categories
                        </span>
                    </div>

                    @if($trashedCategories->isEmpty())
                        <div
                            class="py-12 text-center text-muted-foreground italic text-xs flex flex-col items-center justify-center flex-1">
                            <i data-lucide="check-circle-2" class="w-8 h-8 mb-2 text-muted-foreground"></i>
                            <span>No deleted parent categories.</span>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1 flex-1">
                            @foreach($trashedCategories as $tCat)
                                <div
                                    class="p-3 bg-red-50/20 border border-border rounded-xl flex items-center justify-between hover:bg-red-50/40 transition-colors">
                                    <div class="truncate">
                                        <span
                                            class="font-bold text-foreground text-xs block truncate uppercase">{{ $tCat->name }}</span>
                                        <span class="text-[9px] text-muted-foreground font-mono block truncate mt-0.5">Deleted at:
                                            {{ $tCat->deleted_at->format('d M Y, h:i a') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <!-- Restore -->
                                        <form action="{{ route('admin.pages.categories.restore', $tCat->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-[10px] font-bold border border-emerald-200 transition-colors flex items-center space-x-1">
                                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                        <!-- Permanent Delete -->
                                        <form action="{{ route('admin.pages.categories.force-delete', $tCat->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to permanently delete this category? All its pages inside the trash will also be deleted forever. This cannot be undone!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1.5 bg-red-50 text-red-650 hover:bg-red-100 rounded-lg text-[10px] font-bold border border-red-200 transition-colors flex items-center space-x-1">
                                                <i data-lucide="trash" class="w-3 h-3"></i>
                                                <span>Delete Forever</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Trashed Pages -->
                <div class="bg-card border border-border rounded-xl p-6 shadow-sm flex flex-col">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-border">
                        <h3 class="font-bold text-foreground text-sm flex items-center">
                            <i data-lucide="file-archive" class="w-4 h-4 mr-2 text-muted-foreground"></i>
                            Trashed Pages
                        </h3>
                        <span
                            class="text-[10px] font-bold bg-muted text-muted-foreground px-2.5 py-0.5 rounded-full border border-border">
                            {{ $trashedPages->count() }} Pages
                        </span>
                    </div>

                    @if($trashedPages->isEmpty())
                        <div
                            class="py-12 text-center text-muted-foreground italic text-xs flex flex-col items-center justify-center flex-1">
                            <i data-lucide="check-circle-2" class="w-8 h-8 mb-2 text-muted-foreground"></i>
                            <span>No deleted pages.</span>
                        </div>
                    @else
                        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1 flex-1">
                            @foreach($trashedPages as $tPage)
                                <div
                                    class="p-3 bg-red-50/20 border border-border rounded-xl flex items-center justify-between hover:bg-red-50/40 transition-colors">
                                    <div class="truncate">
                                        <div class="flex items-center space-x-1.5 truncate">
                                            <span class="font-bold text-foreground text-xs truncate">{{ $tPage->title }}</span>
                                            @if($tPage->category)
                                                <span
                                                    class="text-[8px] font-bold bg-muted border border-border rounded px-1 text-muted-foreground uppercase">{{ $tPage->category->name }}</span>
                                            @endif
                                        </div>
                                        <span class="text-[9px] text-muted-foreground font-mono block truncate mt-0.5">Deleted:
                                            {{ $tPage->deleted_at->format('d M Y, h:i a') }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2 flex-shrink-0">
                                        <!-- Restore -->
                                        <form action="{{ route('admin.pages.restore', $tPage->id) }}" method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="px-2.5 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-[10px] font-bold border border-emerald-200 transition-colors flex items-center space-x-1">
                                                <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                        <!-- Permanent Delete -->
                                        <form action="{{ route('admin.pages.force-delete', $tPage->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to permanently delete this page forever? This cannot be undone!')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-2.5 py-1.5 bg-red-50 text-red-650 hover:bg-red-100 rounded-lg text-[10px] font-bold border border-red-200 transition-colors flex items-center space-x-1">
                                                <i data-lucide="trash" class="w-3 h-3"></i>
                                                <span>Delete Forever</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Create Page Modal -->
        <div x-show="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none"
            x-cloak>
            <div class="fixed inset-0 bg-background/65 backdrop-blur-md transition-opacity" @click="closeModal()"></div>

            <div class="relative w-full max-w-2xl mx-auto bg-card rounded-[28px] shadow-2xl border border-border/80 z-10 overflow-hidden"
                x-show="modalOpen" x-transition:enter="transition ease-out duration-350"
                x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-6">

                <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-muted/50">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90/15 flex items-center justify-center text-foreground">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-foreground text-base">Create New Custom Page</h4>
                    </div>
                    <button @click="closeModal()"
                        class="w-8 h-8 rounded-full hover:bg-muted flex items-center justify-center text-muted-foreground hover:text-foreground transition-all focus:outline-none">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.pages.store') }}" method="POST" class="p-6 space-y-4 text-sm">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5 col-span-2 sm:col-span-1">
                            <label for="title" class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Page
                                Title</label>
                            <input type="text" id="title" name="title" required placeholder="e.g. Terms of Use"
                                class="w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all">
                        </div>
                        <div class="space-y-1.5 col-span-2 sm:col-span-1">
                            <label for="slug" class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">URL
                                Slug (Optional)</label>
                            <input type="text" id="slug" name="slug" placeholder="e.g. terms-of-use"
                                class="w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="category_id"
                            class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Parent Category</label>
                        <select id="category_id" name="category_id"
                            class="w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all">
                            <option value="">-- No Category (Will not show in categorised columns) --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="content" class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">HTML
                            Content</label>
                        <textarea id="content" name="content" rows="6"
                            placeholder="<h1>Heading</h1><p>Your content here...</p>"
                            class="tinymce-editor w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all font-mono text-xs"></textarea>
                    </div>

                    <div class="border-t border-border pt-4 space-y-4">
                        <h5 class="text-xs font-bold text-foreground uppercase tracking-wider">SEO Metadata</h5>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5 col-span-2 sm:col-span-1">
                                <label for="meta_title"
                                    class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Meta
                                    Title</label>
                                <input type="text" id="meta_title" name="meta_title" placeholder="Meta title tag"
                                    class="w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all">
                            </div>
                            <div class="space-y-1.5 col-span-2 sm:col-span-1">
                                <label for="meta_description"
                                    class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Meta
                                    Description</label>
                                <input type="text" id="meta_description" name="meta_description"
                                    placeholder="Meta description tag"
                                    class="w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                            class="rounded text-[#09090b] focus:ring-ring border-border">
                        <label for="is_active"
                            class="text-xs font-semibold text-foreground select-none cursor-pointer">Publish Immediately
                            (Active)</label>
                    </div>

                    <div class="pt-4 border-t border-border flex justify-end space-x-2">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2.5 border border-border text-foreground hover:bg-muted font-semibold rounded-xl text-xs transition-all active:scale-[0.98]">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-primary text-primary-foreground hover:bg-primary/90  font-semibold rounded-xl text-xs shadow-sm border border-black/10 transition-all active:scale-[0.98]">
                            Create Page
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Create Category Modal -->
        <div x-show="categoryModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none"
            x-cloak>
            <div class="fixed inset-0 bg-background/65 backdrop-blur-md transition-opacity" @click="closeCategoryModal()">
            </div>

            <div class="relative w-full max-w-md mx-auto bg-card rounded-[28px] shadow-2xl border border-border/80 z-10 overflow-hidden"
                x-show="categoryModalOpen" x-transition:enter="transition ease-out duration-350"
                x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-6">

                <div class="px-6 py-5 border-b border-border flex items-center justify-between bg-muted/50">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground hover:bg-primary/90/15 flex items-center justify-center text-foreground">
                            <i data-lucide="folder-plus" class="w-4.5 h-4.5"></i>
                        </div>
                        <h4 class="font-bold text-foreground text-base">Create Parent Category</h4>
                    </div>
                    <button @click="closeCategoryModal()"
                        class="w-8 h-8 rounded-full hover:bg-muted flex items-center justify-center text-muted-foreground hover:text-foreground transition-all focus:outline-none">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>

                <form action="{{ route('admin.pages.categories.store') }}" method="POST" class="p-6 space-y-4 text-sm">
                    @csrf
                    <div class="space-y-1.5">
                        <label for="modal_category_name"
                            class="block text-xs font-bold text-muted-foreground uppercase tracking-wider">Category Name</label>
                        <input type="text" id="modal_category_name" name="name" required placeholder="e.g. SHOP"
                            class="w-full p-3 border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:ring-4 focus:ring-ring/15 focus:outline-none transition-all">
                    </div>

                    <div class="pt-4 border-t border-border flex justify-end space-x-2">
                        <button type="button" @click="closeCategoryModal()"
                            class="px-5 py-2.5 border border-border text-foreground hover:bg-muted font-semibold rounded-xl text-xs transition-all active:scale-[0.98]">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-primary text-primary-foreground hover:bg-primary/90  font-semibold rounded-xl text-xs shadow-sm border border-black/10 transition-all active:scale-[0.98]">
                            Create Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function pageManager() {
            return {
                pages: @json($pages),
                searchQuery: '',
                modalOpen: false,
                categoryModalOpen: false,
                tab: 'hierarchy',

                init() {
                    this.$watch('modalOpen', val => {
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                    this.$watch('categoryModalOpen', val => {
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                    this.$watch('tab', val => {
                        if (val === 'hierarchy') {
                            this.initSortable();
                        }
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                    setTimeout(() => lucide.createIcons(), 100);
                },

                initSortable() {
                    this.$nextTick(() => {
                        // Initialize Category Card Sortable (Horizontal/Vertical Grid Sorting)
                        let catEl = document.getElementById('categories-sortable');
                        if (catEl) {
                            new Sortable(catEl, {
                                animation: 150,
                                handle: '.handle-category',
                                ghostClass: 'opacity-40',
                                async onEnd(evt) {
                                    let ids = Array.from(catEl.children).map(child => child.getAttribute('data-category-id'));
                                    try {
                                        await fetch('/admin/page-categories/reorder', {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify({ ids: ids })
                                        });
                                    } catch (e) {
                                        console.error('Failed to sort categories', e);
                                    }
                                }
                            });
                        }

                        // Initialize Pages Sortable for each category (allows moving between columns)
                        document.querySelectorAll('.pages-sortable').forEach(el => {
                            new Sortable(el, {
                                group: 'pages',
                                animation: 150,
                                handle: '.page-item',
                                ghostClass: 'bg-muted',
                                async onEnd(evt) {
                                    let pageId = evt.item.getAttribute('data-page-id');
                                    let targetCategoryId = evt.to.getAttribute('data-category-id');

                                    // Get page IDs inside target container
                                    let ids = Array.from(evt.to.children)
                                        .filter(child => child.hasAttribute('data-page-id'))
                                        .map(child => child.getAttribute('data-page-id'));

                                    try {
                                        await fetch('/admin/pages/reorder', {
                                            method: 'PUT',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify({
                                                ids: ids,
                                                page_id: pageId,
                                                category_id: targetCategoryId || null
                                            })
                                        });
                                    } catch (e) {
                                        console.error('Failed to sort pages', e);
                                    }
                                }
                            });
                        });
                    });
                },

                filteredPages() {
                    return this.pages.filter(page => {
                        return page.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            page.slug.toLowerCase().includes(this.searchQuery.toLowerCase());
                    });
                },

                openAddModal() {
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                openCategoryModal() {
                    this.categoryModalOpen = true;
                },

                closeCategoryModal() {
                    this.categoryModalOpen = false;
                },

                formatDate(dateStr) {
                    if (!dateStr) return '-';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('en-IN', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                },

                confirmDelete(event) {
                    if (!confirm('Are you sure you want to delete this page permanently? This action cannot be undone.')) {
                        event.preventDefault();
                    }
                }
            };
        }
    </script>
@endsection