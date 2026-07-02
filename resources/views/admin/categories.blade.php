@extends('layouts.admin')

@section('title', 'Categories Management')
@section('page_title', 'Categories & Subcategories')

@section('content')
    <div class="space-y-6" x-data="categoryManager()">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <p class="text-sm text-zinc-500">Organize and manage the listing categories and subcategories on the
                    marketplace.</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.trash.index', 'categories') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
                    <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
                    <span>Trash ({{ $trashedCategories->count() }})</span>
                </a>
                <button @click="openAddModal()"
                    class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all flex items-center space-x-2 text-sm">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add New Category</span>
                </button>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
            <div class="p-5 border-b border-zinc-150 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="font-bold text-zinc-800 text-sm flex items-center">
                    <i data-lucide="folder-tree" class="w-4 h-4 mr-2 text-zinc-600"></i>
                    Category Tree Directory
                </h3>
                <span class="text-xs text-zinc-400 font-medium">Use arrows to adjust display priority</span>
            </div>

            <div class="divide-y divide-zinc-200">
                @forelse($categories as $category)
                    <!-- Parent Row -->
                    <div class="p-4 hover:bg-zinc-50/30 transition-colors" data-id="{{ $category->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <!-- Toggle Collapse Button -->
                                <button @click="toggleCollapse({{ $category->id }})"
                                    class="p-1 text-zinc-400 hover:text-zinc-700 hover:bg-zinc-150 rounded-lg transition-all focus:outline-none">
                                    <i data-lucide="chevron-right" class="w-4 h-4 transition-transform duration-200"
                                        :class="isExpanded({{ $category->id }}) ? 'rotate-90' : ''"></i>
                                </button>

                                <!-- Category Icon / Image -->
                                <div
                                    class="w-9 h-9 rounded-xl bg-zinc-100 flex items-center justify-center text-zinc-650 border border-zinc-200 overflow-hidden">
                                    @if($category->image)
                                        <img src="{{ $category->image }}" class="object-cover w-full h-full">
                                    @else
                                        <i data-lucide="{{ $category->icon ?: 'folder' }}" class="w-4 h-4"></i>
                                    @endif
                                </div>

                                <div>
                                    <span class="font-bold text-zinc-800 text-sm">{{ $category->name }}</span>
                                    <span
                                        class="text-[11px] bg-zinc-100 border border-zinc-200 text-zinc-500 font-semibold px-2 py-0.5 rounded-full ml-2">
                                        {{ $category->listing_count }} Listings
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
                                <!-- Reorder Arrows -->
                                <div class="flex items-center bg-zinc-100 rounded-lg p-0.5 border border-zinc-200">
                                    <button @click="moveUp('parent', {{ $category->id }})"
                                        class="p-1 text-zinc-500 hover:text-black hover:bg-white rounded transition-all focus:outline-none">
                                        <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <button @click="moveDown('parent', {{ $category->id }})"
                                        class="p-1 text-zinc-500 hover:text-black hover:bg-white rounded transition-all focus:outline-none">
                                        <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>

                                <!-- Active Toggle Switch -->
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" @change="toggleActive({{ $category->id }})" class="sr-only peer" {{ $category->is_active ? 'checked' : '' }}>
                                    <div
                                        class="w-9 h-5 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500">
                                    </div>
                                </label>

                                 <!-- Dropdown Menu for Actions -->
                                 <div x-data="{ openMenu: false }" class="relative inline-block text-left">
                                     <button @click="openMenu = !openMenu" @click.away="openMenu = false"
                                         class="p-2 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-xl transition-all focus:outline-none"
                                         title="More Actions">
                                         <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                     </button>

                                     <div x-show="openMenu" x-cloak
                                         class="absolute right-0 mt-2 w-48 bg-white border border-zinc-200 rounded-xl shadow-lg z-20 py-1.5 focus:outline-none"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="transform opacity-0 scale-95"
                                         x-transition:enter-end="transform opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="transform opacity-100 scale-100"
                                         x-transition:leave-end="transform opacity-0 scale-95">
                                         <div class="px-1 py-1 space-y-0.5">
                                             <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}/categories/{{ $category->slug }}"
                                                 target="_blank"
                                                 class="flex items-center px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 hover:text-black rounded-lg transition-colors">
                                                 <i data-lucide="external-link" class="w-3.5 h-3.5 mr-2 text-zinc-400"></i>
                                                 <span>View Category</span>
                                             </a>

                                             <button @click="openEditModal({{ json_encode($category) }})"
                                                 class="w-full flex items-center px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 hover:text-black rounded-lg transition-colors text-left">
                                                 <i data-lucide="edit-3" class="w-3.5 h-3.5 mr-2 text-zinc-400"></i>
                                                 <span>Edit Category</span>
                                             </button>

                                             <div class="border-t border-zinc-100 my-1"></div>

                                             <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST"
                                                 onsubmit="return confirm('Are you sure you want to delete this category?');"
                                                 class="block">
                                                 @csrf
                                                 @method('DELETE')
                                                 <button type="submit"
                                                     class="w-full flex items-center px-3 py-2 text-xs font-bold text-red-655 hover:bg-red-50 rounded-lg transition-colors text-left">
                                                     <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-2 text-red-400"></i>
                                                     <span>Delete Category</span>
                                                 </button>
                                             </form>
                                         </div>
                                     </div>
                                 </div>
                            </div>
                        </div>

                        <!-- Subcategories list -->
                        <div x-show="isExpanded({{ $category->id }})" x-cloak
                            class="mt-3 ml-12 space-y-2 border-l-2 border-zinc-150 pl-4">
                            @forelse($category->children as $sub)
                                <div class="flex items-center justify-between py-1.5 hover:bg-zinc-50/50 rounded-lg px-2"
                                    data-id="{{ $sub->id }}">
                                    <div class="flex items-center space-x-2">
                                        <i data-lucide="corner-down-right" class="w-3.5 h-3.5 text-zinc-400"></i>
                                        @if($sub->image)
                                            <div
                                                class="w-6 h-6 rounded-lg bg-zinc-100 flex items-center justify-center border border-zinc-200 overflow-hidden flex-shrink-0">
                                                <img src="{{ $sub->image }}" class="object-cover w-full h-full">
                                            </div>
                                        @endif
                                        <span class="text-sm font-medium text-zinc-700">{{ $sub->name }}</span>
                                        <span
                                            class="text-[10px] bg-zinc-100 border border-zinc-200 text-zinc-450 font-medium px-2 py-0.5 rounded-full">
                                            {{ $sub->listing_count }} Listings
                                        </span>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <!-- Reorder Arrows -->
                                        <div class="flex items-center bg-zinc-100 rounded-lg p-0.5 border border-zinc-200">
                                            <button @click="moveUp('child', {{ $sub->id }}, {{ $category->id }})"
                                                class="p-0.5 text-zinc-500 hover:text-black hover:bg-white rounded transition-all focus:outline-none">
                                                <i data-lucide="arrow-up" class="w-3 h-3"></i>
                                            </button>
                                            <button @click="moveDown('child', {{ $sub->id }}, {{ $category->id }})"
                                                class="p-0.5 text-zinc-500 hover:text-black hover:bg-white rounded transition-all focus:outline-none">
                                                <i data-lucide="arrow-down" class="w-3 h-3"></i>
                                            </button>
                                        </div>

                                        <!-- Active Toggle Switch -->
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" @change="toggleActive({{ $sub->id }})" class="sr-only peer" {{ $sub->is_active ? 'checked' : '' }}>
                                            <div
                                                class="w-8 h-4.5 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500">
                                            </div>
                                        </label>

                                         <!-- Dropdown Menu for Actions -->
                                         <div x-data="{ openMenu: false }" class="relative inline-block text-left">
                                             <button @click="openMenu = !openMenu" @click.away="openMenu = false"
                                                 class="p-1.5 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-lg transition-all focus:outline-none"
                                                 title="More Actions">
                                                 <i data-lucide="more-vertical" class="w-3.5 h-3.5"></i>
                                             </button>

                                             <div x-show="openMenu" x-cloak
                                                 class="absolute right-0 mt-2 w-48 bg-white border border-zinc-200 rounded-xl shadow-lg z-20 py-1.5 focus:outline-none"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="transform opacity-0 scale-95"
                                                 x-transition:enter-end="transform opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="transform opacity-100 scale-100"
                                                 x-transition:leave-end="transform opacity-0 scale-95">
                                                 <div class="px-1 py-1 space-y-0.5">
                                                     <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}/categories/{{ $sub->slug }}"
                                                         target="_blank"
                                                         class="flex items-center px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 hover:text-black rounded-lg transition-colors">
                                                         <i data-lucide="external-link" class="w-3.5 h-3.5 mr-2 text-zinc-400"></i>
                                                         <span>View Subcategory</span>
                                                     </a>

                                                     <button @click="openEditModal({{ json_encode($sub) }})"
                                                         class="w-full flex items-center px-3 py-2 text-xs font-semibold text-zinc-700 hover:bg-zinc-100 hover:text-black rounded-lg transition-colors text-left">
                                                         <i data-lucide="edit-3" class="w-3.5 h-3.5 mr-2 text-zinc-400"></i>
                                                         <span>Edit Subcategory</span>
                                                     </button>

                                                     <div class="border-t border-zinc-100 my-1"></div>

                                                     <form action="{{ route('admin.categories.destroy', $sub->id) }}" method="POST"
                                                         onsubmit="return confirm('Are you sure you want to delete this subcategory?');"
                                                         class="block">
                                                         @csrf
                                                         @method('DELETE')
                                                         <button type="submit"
                                                             class="w-full flex items-center px-3 py-2 text-xs font-bold text-red-655 hover:bg-red-50 rounded-lg transition-colors text-left">
                                                             <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-2 text-red-400"></i>
                                                             <span>Delete Subcategory</span>
                                                         </button>
                                                     </form>
                                                 </div>
                                             </div>
                                         </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-zinc-450 italic py-1 pl-6">No subcategories created yet.</p>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div
                            class="w-12 h-12 rounded-full bg-zinc-100 flex items-center justify-center mx-auto mb-3 text-zinc-400">
                            <i data-lucide="folder-x" class="w-6 h-6"></i>
                        </div>
                        <p class="text-sm font-semibold text-zinc-700">No categories found</p>
                        <p class="text-xs text-zinc-450 mt-1">Get started by creating your first category.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Modal Form (Add/Edit Category) -->
        <div x-show="modalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none"
            x-cloak>
            <!-- Glassmorphism backdrop -->
            <div class="fixed inset-0 bg-zinc-950/65 backdrop-blur-md transition-opacity" @click="closeModal()"></div>

            <!-- Modal Content Card -->
            <div class="relative w-full max-w-lg mx-auto bg-white rounded-[28px] shadow-2xl border border-zinc-200/80 z-10 overflow-hidden"
                x-show="modalOpen" x-transition:enter="transition ease-out duration-350"
                x-transition:enter-start="opacity-0 scale-95 translate-y-6"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-6">

                <!-- Header -->
                <div class="px-6 py-5 border-b border-zinc-150 flex items-center justify-between bg-zinc-50/50">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800/15 flex items-center justify-center text-zinc-800">
                            <i data-lucide="folder-tree" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-zinc-900 text-base"
                            x-text="isEdit ? 'Edit Category' : 'Add New Category'"></h4>
                    </div>
                    <button @click="closeModal()"
                        class="w-8 h-8 rounded-full hover:bg-zinc-100 flex items-center justify-center text-zinc-450 hover:text-zinc-800 transition-all focus:outline-none">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>

                <!-- Tab Headers -->
                <div class="flex border-b border-zinc-150 bg-zinc-50/30 px-6">
                    <button type="button" @click="modalTab = 'basic'"
                        :class="modalTab === 'basic' ? 'border-[#09090b] font-semibold text-black' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="px-4 py-3 border-b-2 text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center space-x-2">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span>General Info</span>
                    </button>
                    <button type="button" @click="modalTab = 'seo'"
                        :class="modalTab === 'seo' ? 'border-[#09090b] font-semibold text-black' : 'border-transparent text-zinc-500 hover:text-zinc-800'"
                        class="px-4 py-3 border-b-2 text-xs uppercase tracking-wider transition-all focus:outline-none flex items-center space-x-2">
                        <i data-lucide="globe" class="w-3.5 h-3.5"></i>
                        <span>SEO Meta Settings</span>
                    </button>
                </div>

                <form :action="isEdit ? '/admin/categories/' + form.id : '{{ route('admin.categories.store') }}'"
                    method="POST" enctype="multipart/form-data" class="p-6 space-y-5 text-sm">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Tab 1: Basic Info -->
                    <div x-show="modalTab === 'basic'" class="space-y-4">
                        <!-- Name Field -->
                        <div class="space-y-1.5">
                            <label for="modal_name"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Category Name</label>
                            <input type="text" id="modal_name" name="name" x-model="form.name" required
                                placeholder="e.g. DDR5 RAM or Graphics Card"
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                        </div>

                        <!-- Parent Category -->
                        <div class="space-y-1.5">
                            <label for="modal_parent"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Parent Category
                                (Optional)</label>
                            <select id="modal_parent" name="parent_id" x-model="form.parent_id"
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                                <option value="">None (Make it a Parent Category)</option>
                                @foreach($categories as $parent)
                                    <option value="{{ $parent->id }}" :disabled="form.id == {{ $parent->id }}">
                                        {{ $parent->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category Image Upload -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Category Image
                                (Frontend display)</label>
                            <input type="hidden" name="selected_image_path" id="selected_category_image_path" value="">
                            <div class="flex items-center space-x-4">
                                <div
                                    class="w-14 h-14 rounded-xl border border-zinc-250 flex items-center justify-center bg-zinc-50 overflow-hidden flex-shrink-0">
                                    <template x-if="form.image">
                                        <img :src="form.image" class="object-cover w-full h-full">
                                    </template>
                                    <template x-if="!form.image">
                                        <div class="text-zinc-400">
                                            <i data-lucide="image" class="w-6 h-6"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" @click="openLibrary('category_image')"
                                            class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg text-xs font-semibold border border-zinc-200 transition-all flex items-center space-x-1">
                                            <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                            <span>Choose from Library</span>
                                        </button>
                                        <label
                                            class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center space-x-1">
                                            <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                            <span>Upload File</span>
                                            <input type="file" name="image" accept="image/*" class="hidden"
                                                @change="form.image = URL.createObjectURL($event.target.files[0]); document.getElementById('selected_category_image_path').value = ''">
                                        </label>
                                        <template x-if="form.image">
                                            <button type="button" @click="removeImage()"
                                                class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-650 rounded-lg text-xs font-semibold transition-all flex items-center space-x-1">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Remove</span>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-[10px] text-zinc-400">PNG or JPG. Best on white/transparent background.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Icon selector & sort order -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label for="modal_icon"
                                    class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Lucide Icon
                                    Name</label>
                                <div class="relative">
                                    <input type="text" id="modal_icon" name="icon" x-model="form.icon"
                                        placeholder="cpu, keyboard, zap"
                                        class="w-full pl-9 pr-3 p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                                        <i data-lucide="tag" class="w-4 h-4"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Display
                                    Order</label>
                                <input type="number" name="sort_order" x-model="form.sort_order" readonly
                                    class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-100 text-zinc-500 cursor-not-allowed">
                            </div>
                        </div>

                        <!-- Predefined Icon Quick Picker -->
                        <div class="space-y-1.5 p-3.5 bg-zinc-50 border border-zinc-150 rounded-xl">
                            <span class="block text-[11px] font-bold text-zinc-500 uppercase tracking-wider mb-2">Quick Icon
                                Selection Helper</span>
                            <div class="grid grid-cols-6 gap-2">
                                <template
                                    x-for="icName in ['cpu', 'keyboard', 'monitor', 'wifi', 'zap', 'box', 'hard-drive', 'link', 'database', 'layers', 'shield', 'folder']">
                                    <button type="button" @click="form.icon = icName"
                                        :class="form.icon === icName ? 'bg-zinc-900 text-white hover:bg-zinc-800 text-black border-black/10' : 'bg-white hover:bg-zinc-100 text-zinc-650 border-zinc-200'"
                                        class="py-1.5 border rounded-lg flex flex-col items-center justify-center transition-all focus:outline-none">
                                        <span class="text-[10px] font-medium" x-text="icName"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-1.5">
                            <label for="modal_description"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Description</label>
                            <textarea id="modal_description" name="description" x-model="form.description" rows="2"
                                placeholder="Describe the category..."
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all"></textarea>
                        </div>
                    </div>

                    <!-- Tab 2: SEO Meta Settings -->
                    <div x-show="modalTab === 'seo'" class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="modal_meta_title"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Meta Page
                                Title</label>
                            <input type="text" id="modal_meta_title" name="meta_title" x-model="form.meta_title"
                                placeholder="SEO optimized title (ideal: <60 characters)"
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label for="modal_meta_desc"
                                class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Meta Page
                                Description</label>
                            <textarea id="modal_meta_desc" name="meta_description" x-model="form.meta_description" rows="4"
                                placeholder="SEO search engine description snippet (ideal: <160 characters)..."
                                class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all"></textarea>
                        </div>
                    </div>

                    <!-- Submit / Cancel -->
                    <div class="pt-5 border-t border-zinc-150 flex justify-end space-x-2">
                        <button type="button" @click="closeModal()"
                            class="px-5 py-2.5 border border-zinc-200 text-zinc-700 hover:bg-zinc-50 font-semibold rounded-xl text-xs transition-all active:scale-[0.98]">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold rounded-xl text-xs shadow-sm border border-black/10 transition-all active:scale-[0.98] hover:scale-[1.02]">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Asset Library Modal -->
        <div x-show="libraryOpen" x-transition x-cloak
            class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl border border-zinc-200"
                @click.away="libraryOpen = false">
                <!-- Modal Header -->
                <div class="p-4 border-b border-zinc-150 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="image" class="w-5 h-5 text-zinc-550"></i>
                        <h3 class="text-sm font-bold text-zinc-800 uppercase tracking-wider">Select from Asset Library</h3>
                    </div>
                    <button type="button" @click="libraryOpen = false"
                        class="text-zinc-400 hover:text-zinc-600 focus:outline-none">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <!-- Modal Body -->
                <div class="p-6 overflow-y-auto flex-1 bg-zinc-50">
                    <template x-if="libraryImages.length === 0">
                        <div class="text-center py-12 text-zinc-450">
                            <i data-lucide="image-off" class="w-10 h-10 mx-auto mb-3 text-zinc-300"></i>
                            <p class="text-xs font-semibold">No assets found in library</p>
                            <p class="text-[10px] text-zinc-400 mt-1">Upload images to public/website_assets/images
                                directory</p>
                        </div>
                    </template>
                    <template x-if="libraryImages.length > 0">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
                            <template x-for="img in libraryImages" :key="img.url">
                                <button type="button" @click="selectLibraryImage(img.url)"
                                    class="group p-2 bg-white rounded-xl border border-zinc-200 hover:border-[#09090b] hover:ring-2 hover:ring-[#09090b]/20 transition-all text-left flex flex-col items-center justify-between aspect-square">
                                    <div
                                        class="w-full flex-1 flex items-center justify-center overflow-hidden rounded-lg bg-zinc-50">
                                        <img :src="img.url"
                                            class="max-h-24 max-w-full object-contain p-1 group-hover:scale-105 transition-all">
                                    </div>
                                    <div class="w-full mt-2 text-center">
                                        <p class="text-[9px] font-semibold text-zinc-700 truncate" x-text="img.name"></p>
                                        <p class="text-[8px] text-zinc-400" x-text="img.size"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
                <!-- Modal Footer -->
                <div class="p-4 border-t border-zinc-150 flex justify-end bg-zinc-50 rounded-b-2xl">
                    <button type="button" @click="libraryOpen = false"
                        class="px-4 py-2 border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 rounded-xl text-xs font-semibold shadow-sm transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function categoryManager() {
            return {
                modalOpen: false,
                modalTab: 'basic',
                isEdit: false,
                collapsedCategories: [], // Deprecated: keep for compatibility/no-op
                expandedCategories: [],
                libraryOpen: false,
                libraryImages: [],
                libraryTarget: 'category_image',
                async openLibrary(target = 'category_image') {
                    this.libraryTarget = target;
                    this.libraryOpen = true;
                    try {
                        let res = await fetch('{{ route('admin.asset-library.images') }}');
                        this.libraryImages = await res.json();
                        setTimeout(() => { if (window.lucide) { window.lucide.createIcons(); } }, 100);
                    } catch (e) {
                        console.error('Failed to load asset library', e);
                    }
                },
                selectLibraryImage(url) {
                    this.form.image = url;
                    document.getElementById('selected_category_image_path').value = url;
                    this.libraryOpen = false;
                },
                removeImage() {
                    this.form.image = '';
                    document.getElementById('selected_category_image_path').value = 'remove';
                },

                form: {
                    id: '',
                    name: '',
                    parent_id: '',
                    icon: '',
                    image: '',
                    description: '',
                    sort_order: 0,
                    meta_title: '',
                    meta_description: ''
                },

                init() {
                    this.expandedCategories = [];
                    this.$watch('modalOpen', val => {
                        if (val) {
                            this.modalTab = 'basic';
                        }
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                },

                toggleCollapse(id) {
                    if (this.expandedCategories.includes(id)) {
                        this.expandedCategories = this.expandedCategories.filter(x => x !== id);
                    } else {
                        this.expandedCategories.push(id);
                    }
                },

                isExpanded(id) {
                    return this.expandedCategories.includes(id);
                },

                isCollapsed(id) {
                    return !this.isExpanded(id);
                },

                openAddModal() {
                    this.isEdit = false;
                    this.form = {
                        id: '',
                        name: '',
                        parent_id: '',
                        icon: 'folder',
                        image: '',
                        description: '',
                        sort_order: 0,
                        meta_title: '',
                        meta_description: ''
                    };
                    this.modalOpen = true;
                },

                openEditModal(category) {
                    this.isEdit = true;
                    this.form = {
                        id: category.id,
                        name: category.name,
                        parent_id: category.parent_id || '',
                        icon: category.icon || 'folder',
                        image: category.image || '',
                        description: category.description || '',
                        sort_order: category.sort_order || 0,
                        meta_title: category.meta_title || '',
                        meta_description: category.meta_description || ''
                    };
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                async toggleActive(id) {
                    try {
                        let response = await fetch(`/admin/categories/${id}/toggle-active`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        let result = await response.json();
                        if (result.success) {
                            // Success callback
                        }
                    } catch (err) {
                        console.error('Failed to toggle active state', err);
                    }
                },

                async moveUp(type, id, parentId = null) {
                    let list = this.getSiblings(type, parentId);
                    let index = list.indexOf(id);
                    if (index > 0) {
                        list.splice(index - 1, 0, list.splice(index, 1)[0]);
                        await this.saveReorder(list);
                    }
                },

                async moveDown(type, id, parentId = null) {
                    let list = this.getSiblings(type, parentId);
                    let index = list.indexOf(id);
                    if (index >= 0 && index < list.length - 1) {
                        list.splice(index + 1, 0, list.splice(index, 1)[0]);
                        await this.saveReorder(list);
                    }
                },

                getSiblings(type, parentId) {
                    if (type === 'parent') {
                        let elements = document.querySelectorAll('.divide-y > div[data-id]');
                        return Array.from(elements).map(el => parseInt(el.getAttribute('data-id')));
                    } else {
                        let elements = document.querySelectorAll(`div[data-id="${parentId}"] div[data-id]`);
                        return Array.from(elements).map(el => parseInt(el.getAttribute('data-id')));
                    }
                },

                async saveReorder(ids) {
                    try {
                        let response = await fetch('{{ route('admin.categories.reorder') }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ ids: ids })
                        });
                        let result = await response.json();
                        if (result.success) {
                            window.location.reload();
                        }
                    } catch (err) {
                        console.error('Failed to save category ordering', err);
                    }
                }
            };
        }
    </script>
@endsection