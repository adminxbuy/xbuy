@extends('layouts.admin')

@section('title', 'Brands Management')
@section('page_title', 'Brands Management')

@section('content')
    <div class="space-y-6" x-data="brandManager()">
        <!-- Header Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex-1 w-full sm:w-auto">
                <p class="text-sm text-zinc-500">Add, edit, delete and organize brands and map them to parent categories.
                </p>
            </div>
            <button @click="openAddModal()"
                class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-[#ffd747] text-black font-semibold py-2.5 px-5 rounded-lg ring-1 ring-zinc-950/5 border border-black/10 transition-all flex items-center space-x-2 text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add New Brand</span>
            </button>
        </div>

        <!-- Filter & Search Bar -->
        <div
            class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="relative w-full md:w-72">
                <input type="text" x-model="searchQuery" placeholder="Search brands..."
                    class="w-full pl-9 pr-4 py-2 border border-zinc-200 rounded-xl bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none text-sm transition-all">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
            </div>

            <div class="flex items-center space-x-2 w-full md:w-auto">
                <span class="text-xs font-semibold text-zinc-500 uppercase tracking-wider">Filter Category:</span>
                <select x-model="categoryFilter"
                    class="p-2 border border-zinc-200 rounded-xl bg-white text-sm focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
            <div class="p-5 border-b border-zinc-150 bg-zinc-50/50 flex justify-between items-center">
                <h3 class="font-bold text-zinc-800 text-sm flex items-center">
                    <i data-lucide="tag" class="w-4 h-4 mr-2 text-zinc-650"></i>
                    All Brands List
                </h3>
                <span class="text-xs text-zinc-400 font-medium" x-text="filteredBrandsCount() + ' brands found'"></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-zinc-50 border-b border-zinc-150 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                            <th class="p-4 pl-6">Logo</th>
                            <th class="p-4">Brand Name</th>
                            <th class="p-4">Associated Categories</th>
                            <th class="p-4">Listing Count</th>
                            <th class="p-4 text-center">Active</th>
                            <th class="p-4 pr-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        <template x-for="brand in filteredBrands()" :key="brand.id">
                            <tr class="hover:bg-zinc-50/30 transition-colors text-sm text-zinc-700">
                                <!-- Logo -->
                                <td class="p-4 pl-6">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-zinc-100 border border-zinc-200 overflow-hidden flex items-center justify-center">
                                        <template x-if="brand.logo">
                                            <img :src="brand.logo" class="object-contain w-full h-full p-1.5">
                                        </template>
                                        <template x-if="!brand.logo">
                                            <div class="text-zinc-400">
                                                <i data-lucide="image" class="w-5 h-5"></i>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <!-- Brand Name -->
                                <td class="p-4">
                                    <span class="font-bold text-zinc-850" x-text="brand.name"></span>
                                    <p class="text-[10px] text-zinc-400" x-text="brand.slug"></p>
                                </td>
                                <!-- Associated Categories -->
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-1">
                                        <template x-if="brand.categories.length === 0">
                                            <span class="text-xs text-zinc-400 italic">None</span>
                                        </template>
                                        <template x-for="cat in brand.categories" :key="cat.id">
                                            <span
                                                class="text-[10px] bg-zinc-100 border border-zinc-200 text-zinc-650 font-semibold px-2 py-0.5 rounded-full"
                                                x-text="cat.name"></span>
                                        </template>
                                    </div>
                                </td>
                                <!-- Listing Count -->
                                <td class="p-4 font-semibold text-zinc-500" x-text="brand.listings_count ?? 0"></td>
                                <!-- Active Toggle -->
                                <td class="p-4 text-center">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" :checked="brand.is_active" @change="toggleActive(brand)"
                                            class="sr-only peer">
                                        <div
                                            class="w-8 h-4.5 bg-zinc-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-zinc-300 after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500">
                                        </div>
                                    </label>
                                </td>
                                <!-- Actions -->
                                <td class="p-4 pr-6 text-right space-x-1">
                                    <button @click="openEditModal(brand)"
                                        class="p-2 text-zinc-500 hover:text-zinc-800 hover:bg-zinc-100 rounded-xl transition-all"
                                        title="Edit Brand">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    <template x-if="brand.listings_count === 0 || !brand.listings_count">
                                        <form :action="'/admin/brands/' + brand.id" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                            @csrf
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit"
                                                class="p-2 text-zinc-400 hover:text-red-650 hover:bg-red-50 rounded-xl transition-all"
                                                title="Delete Brand">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </template>
                                    <template x-if="brand.listings_count > 0">
                                        <button disabled class="p-2 text-zinc-200 cursor-not-allowed"
                                            title="Cannot delete: contains active listings">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="filteredBrands().length === 0">
                            <td colspan="6" class="p-12 text-center text-zinc-450 italic">
                                No brands found matching your criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Form (Add/Edit Brand) -->
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
                            <i data-lucide="tag" class="w-4 h-4"></i>
                        </div>
                        <h4 class="font-bold text-zinc-900 text-base" x-text="isEdit ? 'Edit Brand' : 'Add New Brand'"></h4>
                    </div>
                    <button @click="closeModal()"
                        class="w-8 h-8 rounded-full hover:bg-zinc-100 flex items-center justify-center text-zinc-450 hover:text-zinc-800 transition-all focus:outline-none">
                        <i data-lucide="x" class="w-4.5 h-4.5"></i>
                    </button>
                </div>

                <form :action="isEdit ? '/admin/brands/' + form.id : '{{ route('admin.brands.store') }}'" method="POST"
                    enctype="multipart/form-data" class="p-6 space-y-5 text-sm">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <!-- Brand Name -->
                    <div class="space-y-1.5">
                        <label for="modal_name" class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Brand
                            Name</label>
                        <input type="text" id="modal_name" name="name" x-model="form.name" required
                            placeholder="e.g. Corsair, G.Skill"
                            class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:ring-4 focus:ring-zinc-950/15 focus:outline-none transition-all">
                    </div>

                    <!-- Brand Logo -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Brand Logo
                            Image</label>
                        <input type="hidden" name="selected_logo_path" id="selected_brand_logo_path" value="">
                        <div class="flex items-center space-x-4">
                            <div
                                class="w-14 h-14 rounded-xl border border-zinc-250 flex items-center justify-center bg-zinc-50 overflow-hidden flex-shrink-0">
                                <template x-if="form.logo">
                                    <img :src="form.logo" class="object-contain w-full h-full p-1.5">
                                </template>
                                <template x-if="!form.logo">
                                    <div class="text-zinc-400">
                                        <i data-lucide="image" class="w-6 h-6"></i>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @click="openLibrary('brand_logo')"
                                        class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 rounded-lg text-xs font-semibold border border-zinc-200 transition-all flex items-center space-x-1">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                        <span>Choose from Library</span>
                                    </button>
                                    <label
                                        class="px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center space-x-1">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                        <span>Upload File</span>
                                        <input type="file" name="logo" accept="image/*" class="hidden"
                                            @change="form.logo = URL.createObjectURL($event.target.files[0]); document.getElementById('selected_brand_logo_path').value = ''">
                                    </label>
                                    <template x-if="form.logo">
                                        <button type="button" @click="removeImage()"
                                            class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-650 rounded-lg text-xs font-semibold transition-all flex items-center space-x-1">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Remove</span>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[10px] text-zinc-400">PNG or SVG format with transparent or white background
                                    is ideal.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Associated Categories Checkboxes -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Map to
                            Categories</label>
                        <div
                            class="grid grid-cols-2 gap-2 bg-zinc-50 border border-zinc-150 rounded-xl p-3.5 max-h-48 overflow-y-auto">
                            @foreach($categories as $cat)
                                <label class="flex items-center space-x-2 py-1 cursor-pointer">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                        :checked="form.category_ids.includes({{ $cat->id }})"
                                        class="rounded text-[#09090b] focus:ring-zinc-950 border-zinc-300">
                                    <span class="text-xs font-medium text-zinc-700">{{ $cat->name }}</span>
                                </label>
                            @endforeach
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
        function brandManager() {
            return {
                brands: @json($brands),
                searchQuery: '',
                categoryFilter: '',
                modalOpen: false,
                isEdit: false,
                libraryOpen: false,
                libraryImages: [],
                libraryTarget: 'brand_logo',
                async openLibrary(target = 'brand_logo') {
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
                    this.form.logo = url;
                    document.getElementById('selected_brand_logo_path').value = url;
                    this.libraryOpen = false;
                },
                removeImage() {
                    this.form.logo = '';
                    document.getElementById('selected_brand_logo_path').value = 'remove';
                },
                form: {
                    id: '',
                    name: '',
                    logo: '',
                    category_ids: []
                },

                init() {
                    this.$watch('modalOpen', val => {
                        setTimeout(() => lucide.createIcons(), 50);
                    });
                    setTimeout(() => lucide.createIcons(), 100);
                },

                filteredBrands() {
                    return this.brands.filter(brand => {
                        const matchesSearch = brand.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            brand.slug.toLowerCase().includes(this.searchQuery.toLowerCase());

                        let matchesCategory = true;
                        if (this.categoryFilter !== '') {
                            matchesCategory = brand.categories.some(cat => cat.id == this.categoryFilter);
                        }

                        return matchesSearch && matchesCategory;
                    });
                },

                filteredBrandsCount() {
                    return this.filteredBrands().length;
                },

                openAddModal() {
                    this.isEdit = false;
                    this.form = {
                        id: '',
                        name: '',
                        logo: '',
                        category_ids: []
                    };
                    this.modalOpen = true;
                },

                openEditModal(brand) {
                    this.isEdit = true;
                    this.form = {
                        id: brand.id,
                        name: brand.name,
                        logo: brand.logo || '',
                        category_ids: brand.categories.map(c => c.id)
                    };
                    this.modalOpen = true;
                },

                closeModal() {
                    this.modalOpen = false;
                },

                async toggleActive(brand) {
                    try {
                        let response = await fetch(`/admin/brands/${brand.id}/toggle-active`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        let result = await response.json();
                        if (result.success) {
                            brand.is_active = result.is_active;
                        }
                    } catch (err) {
                        console.error('Failed to toggle active state', err);
                    }
                }
            };
        }
    </script>
@endsection