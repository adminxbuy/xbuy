@extends('layouts.admin')

@section('title', 'Brands Management')
@section('page_title', 'Brands Management')

@section('content')
 <div class="space-y-6" x-data="brandManager()">
 <!-- Header Controls -->
 <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
 <div class="flex-1 w-full sm:w-auto">
 <p class="text-muted-foreground text-sm">Add, edit, delete and organize brands and map them to parent categories.
 </p>
 </div>
 <button @click="openAddModal()"
 class="bg-primary text-primary-foreground hover:bg-primary/90 font-semibold py-2.5 px-5 rounded-lg ring-0 border border-black/10 transition-all flex items-center space-x-2 text-sm">
 <i data-lucide="plus" class="size-4"></i>
 <span>Add New Brand</span>
 </button>
 </div>

 <!-- Filter & Search Bar -->
 <div
 class="rounded-xl p-4 flex flex-col md:flex-row gap-4 justify-between items-center bg-muted border border-border">
 <div class="relative w-full md:w-72">
 <input type="text" x-model="searchQuery" placeholder="Search brands..."
 class="w-full pl-9 pr-4 py-2 rounded-lg focus:ring-1 focus:ring-ring focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none text-sm transition-all bg-card border border-border text-foreground">
 <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
 <i data-lucide="search" class="size-4"></i>
 </div>
 </div>

 <div class="flex items-center space-x-2 w-full md:w-auto">
 <span class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Filter Category:</span>
 <select x-model="categoryFilter"
 class="p-2 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-ring focus:border-ring bg-card border border-border text-foreground">
 <option value="">All Categories</option>
 @foreach($categories as $cat)
 <option value="{{ $cat->id }}">{{ $cat->name }}</option>
 @endforeach
 </select>
 </div>
 </div>

 <!-- Main Table Card -->
 <div class="rounded-xl ring-0 overflow-hidden bg-card border border-border">
 <div class="p-5 flex justify-between items-center bg-muted border-b border-border">
 <h3 class="font-bold text-sm flex items-center text-foreground">
 <i data-lucide="tag" class="w-4 h-4 mr-2 text-muted-foreground"></i>
 All Brands List
 </h3>
 <span class="text-xs font-medium text-muted-foreground" x-text="filteredBrandsCount() + ' brands found'"></span>
 </div>

 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead>
 <tr
 class="border-b text-[11px] font-bold uppercase tracking-wider bg-muted border-border text-muted-foreground">
 <th class="p-4 pl-6">Logo</th>
 <th class="p-4">Brand Name</th>
 <th class="p-4">Associated Categories</th>
 <th class="p-4">Listing Count</th>
 <th class="p-4 text-center">Active</th>
 <th class="p-4 pr-6 text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y border-border">
 <template x-for="brand in filteredBrands()" :key="brand.id">
 <tr class="hover:opacity-80 transition-colors text-sm text-foreground">
 <!-- Logo -->
 <td class="p-4 pl-6">
 <div
 class="w-10 h-10 rounded-xl flex items-center justify-center overflow-hidden bg-muted border border-border">
 <template x-if="brand.logo">
 <img :src="brand.logo" class="object-contain w-full h-full p-1.5">
 </template>
 <template x-if="!brand.logo">
 <div class="text-muted-foreground">
 <i data-lucide="image" class="size-5"></i>
 </div>
 </template>
 </div>
 </td>
 <!-- Brand Name -->
 <td class="p-4">
 <span class="font-bold text-foreground" x-text="brand.name"></span>
 <p class="text-[10px] text-muted-foreground" x-text="brand.slug"></p>
 </td>
 <!-- Associated Categories -->
 <td class="p-4">
 <div class="flex flex-wrap gap-1">
 <template x-if="brand.categories.length === 0">
 <span class="text-xs italic text-muted-foreground">None</span>
 </template>
 <template x-for="cat in brand.categories" :key="cat.id">
 <span
 class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-muted border border-border text-muted-foreground"
 x-text="cat.name"></span>
 </template>
 </div>
 </td>
 <!-- Listing Count -->
 <td class="p-4 font-semibold text-muted-foreground" x-text="brand.listings_count ?? 0"></td>
 <!-- Active Toggle -->
 <td class="p-4 text-center">
 <label class="relative inline-flex items-center cursor-pointer">
 <input type="checkbox" :checked="brand.is_active" @change="toggleActive(brand)"
 class="sr-only peer">
 <div
 class="w-8 h-4.5 bg-muted peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-card after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-card after:border-border after:border after:rounded-full after:h-3.5 after:w-3.5 after:transition-all peer-checked:bg-emerald-500">
 </div>
 </label>
 </td>
 <!-- Actions -->
 <td class="p-4 pr-6 text-right space-x-1">
 <button @click="openEditModal(brand)"
 class="p-2 hover:opacity-70 rounded-xl transition-all text-muted-foreground" title="Edit Brand">
 <i data-lucide="edit-3" class="size-4"></i>
 </button>

 <template x-if="brand.listings_count === 0 || !brand.listings_count">
 <form :action="'/admin/brands/' + brand.id" method="POST" class="inline"
 onsubmit="return confirm('Are you sure you want to delete this brand?');">
 @csrf
 <input type="hidden" name="_method" value="DELETE">
 <button type="submit"
 class="p-2 text-muted-foreground hover:text-destructive hover:bg-muted rounded-xl transition-all"
 title="Delete Brand">
 <i data-lucide="trash-2" class="size-4"></i>
 </button>
 </form>
 </template>
 <template x-if="brand.listings_count > 0">
 <button disabled class="p-2 text-muted-foreground cursor-not-allowed"
 title="Cannot delete: contains active listings">
 <i data-lucide="trash-2" class="size-4"></i>
 </button>
 </template>
 </td>
 </tr>
 </template>
 <tr x-show="filteredBrands().length === 0">
 <td colspan="6" class="p-12 text-center italic text-muted-foreground">
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
 <div class="fixed inset-0 bg-background/65 backdrop-blur-md transition-opacity" @click="closeModal()"></div>

 <!-- Modal Content Card -->
 <div class="relative w-full max-w-lg mx-auto rounded-xl shadow-2xl z-10 overflow-hidden bg-card border border-border"
 x-show="modalOpen" x-transition:enter="transition ease-out duration-350"
 x-transition:enter-start="opacity-0 scale-95 translate-y-6"
 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
 x-transition:leave="transition ease-in duration-200"
 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
 x-transition:leave-end="opacity-0 scale-95 translate-y-6">

 <!-- Header -->
 <div class="px-6 py-5 flex items-center justify-between bg-muted border-b border-border">
 <div class="flex items-center space-x-2.5">
 <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-primary text-primary-foreground">
 <i data-lucide="tag" class="size-4"></i>
 </div>
 <h4 class="font-bold text-base text-foreground" x-text="isEdit ? 'Edit Brand' : 'Add New Brand'"></h4>
 </div>
 <button @click="closeModal()"
 class="w-8 h-8 rounded-full flex items-center justify-center transition-all focus:outline-none text-muted-foreground">
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
 <label for="modal_name" class="block text-xs font-bold uppercase tracking-wider text-muted-foreground">Brand
 Name</label>
 <input type="text" id="modal_name" name="name" x-model="form.name" required
 placeholder="e.g. Corsair, G.Skill"
 class="w-full p-3 rounded-lg focus:ring-1 focus:ring-ring focus:border-ring focus:ring-2 focus:ring-ring/50 focus:outline-none transition-all bg-muted border border-border text-foreground">
 </div>

 <!-- Brand Logo -->
 <div class="space-y-1.5">
 <label class="block text-xs font-bold uppercase tracking-wider text-muted-foreground">Brand Logo
 Image</label>
 <input type="hidden" name="selected_logo_path" id="selected_brand_logo_path" value="">
 <div class="flex items-center space-x-4">
 <div
 class="w-14 h-14 rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0 bg-muted border border-border">
 <template x-if="form.logo">
 <img :src="form.logo" class="object-contain w-full h-full p-1.5">
 </template>
 <template x-if="!form.logo">
 <div class="text-muted-foreground">
 <i data-lucide="image" class="size-6"></i>
 </div>
 </template>
 </div>
 <div class="flex-1 space-y-2">
 <div class="flex flex-wrap gap-2">
 <button type="button" @click="openLibrary('brand_logo')"
 class="px-3 py-1.5 hover:opacity-80 rounded-lg text-xs font-semibold transition-all flex items-center space-x-1 bg-muted border border-border text-muted-foreground">
 <i data-lucide="image" class="size-3.5"></i>
 <span>Choose from Library</span>
 </button>
 <label
 class="px-3 py-1.5 bg-primary text-primary-foreground hover:bg-primary/90 rounded-lg text-xs font-semibold cursor-pointer transition-all flex items-center space-x-1">
 <i data-lucide="upload" class="size-3.5"></i>
 <span>Upload File</span>
 <input type="file" name="logo" accept="image/*" class="hidden"
 @change="form.logo = URL.createObjectURL($event.target.files[0]); document.getElementById('selected_brand_logo_path').value = ''">
 </label>
 <template x-if="form.logo">
 <button type="button" @click="removeImage()"
 class="px-3 py-1.5 bg-muted hover:bg-muted text-destructive rounded-lg text-xs font-semibold transition-all flex items-center space-x-1">
 <i data-lucide="trash-2" class="size-3.5"></i>
 <span>Remove</span>
 </button>
 </template>
 </div>
 <p class="text-[10px] text-muted-foreground">PNG or SVG format with transparent or white background
 is ideal.</p>
 </div>
 </div>
 </div>

 <!-- Associated Categories Checkboxes -->
 <div class="space-y-2">
 <label class="block text-xs font-bold uppercase tracking-wider text-muted-foreground">Map to
 Categories</label>
 <div
 class="grid grid-cols-2 gap-2 rounded-xl p-3.5 max-h-48 overflow-y-auto bg-muted border border-border">
 @foreach($categories as $cat)
 <label class="flex items-center space-x-2 py-1 cursor-pointer">
 <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
 :checked="form.category_ids.includes({{ $cat->id }})"
 class="rounded text-foreground focus:ring-ring border-border">
 <span class="text-xs font-medium text-foreground">{{ $cat->name }}</span>
 </label>
 @endforeach
 </div>
 </div>

 <!-- Submit / Cancel -->
 <div class="pt-5 flex justify-end space-x-2 border-t border-border">
 <button type="button" @click="closeModal()"
 class="px-5 py-2.5 hover:opacity-80 font-semibold rounded-xl text-xs transition-all border border-border text-muted-foreground">
 Cancel
 </button>
 <button type="submit"
 class="px-6 py-2.5 font-semibold rounded-xl text-xs transition-all hover:scale-[1.02] bg-primary text-primary-foreground">
 Save Changes
 </button>
 </div>
 </form>
 </div>
 </div>

 <!-- Asset Library Modal -->
 <div x-show="libraryOpen" x-transition x-cloak
 class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
 <div class="rounded-xl max-w-2xl w-full max-h-[85vh] flex flex-col shadow-2xl bg-card border border-border"
 @click.away="libraryOpen = false">
 <!-- Modal Header -->
 <div class="p-4 flex items-center justify-between border-b border-border">
 <div class="flex items-center space-x-2">
 <i data-lucide="image" class="w-5 h-5 text-muted-foreground"></i>
 <h3 class="text-sm font-bold uppercase tracking-wider text-foreground">Select from Asset Library</h3>
 </div>
 <button type="button" @click="libraryOpen = false"
 class="focus:outline-none text-muted-foreground">
 <i data-lucide="x" class="size-5"></i>
 </button>
 </div>
 <!-- Modal Body -->
 <div class="p-6 overflow-y-auto flex-1 bg-muted">
 <template x-if="libraryImages.length === 0">
 <div class="text-center py-12 text-muted-foreground">
 <i data-lucide="image-off" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
 <p class="text-xs font-semibold">No assets found in library</p>
 <p class="text-[10px] mt-1 opacity-60">Upload images to public/website_assets/images
 directory</p>
 </div>
 </template>
 <template x-if="libraryImages.length > 0">
 <div class="grid grid-cols-3 sm:grid-cols-4 gap-4">
 <template x-for="img in libraryImages" :key="img.url">
 <button type="button" @click="selectLibraryImage(img.url)"
 class="group p-2 rounded-xl hover:ring-2 transition-all text-left flex flex-col items-center justify-between aspect-square bg-card border border-border">
 <div
 class="w-full flex-1 flex items-center justify-center overflow-hidden rounded-lg bg-muted">
 <img :src="img.url"
 class="max-h-24 max-w-full object-contain p-1 group-hover:scale-105 transition-all">
 </div>
 <div class="w-full mt-2 text-center">
 <p class="text-[9px] font-semibold truncate text-foreground" x-text="img.name"></p>
 <p class="text-[8px] text-muted-foreground" x-text="img.size"></p>
 </div>
 </button>
 </template>
 </div>
 </template>
 </div>
 <!-- Modal Footer -->
 <div class="p-4 flex justify-end rounded-b-2xl border-t border-border bg-muted">
 <button type="button" @click="libraryOpen = false"
 class="px-4 py-2 bg-card hover:opacity-80 rounded-xl text-xs font-semibold transition-all border border-border text-foreground">
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
