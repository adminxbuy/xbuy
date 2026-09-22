@extends('layouts.app')

@section('title', 'Sell an Item on X-Buy | 3-Minute Listing Flow')

@php
    $categories = \App\Models\Category::active()->parentOnly()->with('children')->orderBy('sort_order')->get();
    $brands = \App\Models\Brand::orderBy('name')->get();
    $user = auth()->user();
    $commissionRate = 5.0; // default platform commission %
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         photos: [],
         title: '',
         category: '',
         subCategory: '',
         brand: '',
         condition: 'good',
         price: '',
         originalPrice: '',
         commissionPercent: {{ $commissionRate }},
         pickupCity: '{{ addslashes($user->city ?? 'Bangalore') }}',
         pickupState: '{{ addslashes($user->state ?? 'Karnataka') }}',
         pickupPincode: '{{ addslashes($user->pincode ?? '560001') }}',
         weightCategory: '1kg',
         specChipset: '',
         specVram: '',
         specWarranty: 'none',
         description: '',
         submitting: false,
         errorMessage: '',
         get commissionAmount() {
             const p = Number(this.price) || 0;
             return Math.round((p * this.commissionPercent) / 100);
         },
         get netPayout() {
             const p = Number(this.price) || 0;
             return Math.max(0, p - this.commissionAmount);
         },
         handleFileSelect(e) {
             const files = Array.from(e.target.files);
             if (this.photos.length + files.length > 10) {
                 alert('You can upload a maximum of 10 photos.');
                 return;
             }
             files.forEach(file => {
                 const reader = new FileReader();
                 reader.onload = (event) => {
                     this.photos.push({
                         url: event.target.result,
                         file: file
                     });
                 };
                 reader.readAsDataURL(file);
             });
         },
         removePhoto(index) {
             this.photos.splice(index, 1);
         },
         async submitListing() {
             if (!this.title.trim()) {
                 this.errorMessage = 'Please provide an item title.';
                 return;
             }
             if (!this.price || Number(this.price) <= 0) {
                 this.errorMessage = 'Please specify a valid listing price.';
                 return;
             }

             this.submitting = true;
             this.errorMessage = '';

             try {
                 const res = await fetch('/dashboard/listings', {
                     method: 'POST',
                     headers: {
                         'Content-Type': 'application/json',
                         'X-CSRF-TOKEN': '{{ csrf_token() }}',
                         'Accept': 'application/json'
                     },
                     body: JSON.stringify({
                         title: this.title,
                         price: this.price,
                         original_price: this.originalPrice || null,
                         category: this.category || 'other',
                         brand: this.brand || 'Other',
                         condition: this.condition,
                         description: this.description || this.title,
                         pickup_city: this.pickupCity,
                         pickup_state: this.pickupState,
                         pickup_pincode: this.pickupPincode,
                         shipping_charges: 0,
                         images: this.photos.map(p => p.url)
                     })
                 });

                 const data = await res.json();
                 if (!res.ok || data.error) {
                     this.errorMessage = data.message || data.error || 'Failed to publish listing.';
                     this.submitting = false;
                     return;
                 }

                 window.location.href = data.redirect || '/listings';
             } catch(err) {
                 this.errorMessage = 'Network error while publishing listing.';
                 this.submitting = false;
             }
         }
     }">

    <!-- Breadcrumb -->
    <nav class="mb-5 flex items-center space-x-2 text-xs font-semibold text-zinc-500">
        <a href="/" class="hover:text-zinc-950">Home</a>
        <span>/</span>
        <a href="/dashboard/orders?tab=selling" class="hover:text-zinc-950">Seller Dashboard</a>
        <span>/</span>
        <span class="text-zinc-900 font-bold">List an Item</span>
    </nav>

    <div class="space-y-6">
        
        <div>
            <span class="text-xs font-black uppercase tracking-wider text-amber-800 bg-amber-200/60 px-3 py-1 rounded-full inline-block mb-2">
                Fast Listing Flow • 0% Seller Fee Promotion
            </span>
            <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight">
                Sell Your PC Hardware on X-Buy
            </h1>
            <p class="text-xs text-zinc-500 mt-1">
                Upload photos, set your price, and our escrow system will handle payment protection and insured shipping.
            </p>
        </div>

        <div x-show="errorMessage" x-text="errorMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs font-bold rounded-xl" style="display: none;"></div>

        <!-- Wizard Card Container -->
        <div class="bg-white rounded-3xl border border-zinc-200 p-6 sm:p-8 shadow-xs space-y-8">
            
            <!-- Step 1: Visual Photo Dropzone -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-zinc-950 uppercase tracking-wider">
                            1. Item Photos (Up to 10)
                        </h3>
                        <p class="text-xs text-zinc-500">The first photo will be your main listing cover.</p>
                    </div>
                    <span class="text-xs font-bold text-zinc-400" x-text="photos.length + ' / 10'"></span>
                </div>

                <!-- Dropzone box -->
                <div class="border-2 border-dashed border-zinc-300 hover:border-zinc-950 rounded-2xl p-6 text-center transition-colors bg-zinc-50/50 cursor-pointer relative"
                     @click="$refs.photoInput.click()">
                    
                    <input type="file" 
                           x-ref="photoInput" 
                           @change="handleFileSelect" 
                           multiple 
                           accept="image/*" 
                           class="hidden">

                    <div class="flex flex-col items-center justify-center space-y-2">
                        <div class="w-12 h-12 rounded-full bg-white border border-zinc-200 shadow-2xs flex items-center justify-center text-zinc-600">
                            <svg class="w-6 h-6 stroke-[1.8]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/></svg>
                        </div>
                        <span class="text-xs font-extrabold text-zinc-900">Click or drag photos here</span>
                        <span class="text-[11px] text-zinc-400">Clear photos of serial numbers and physical ports increase buyer trust</span>
                    </div>
                </div>

                <!-- Preview thumbnails -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-2" x-show="photos.length > 0">
                    <template x-for="(p, index) in photos" :key="index">
                        <div class="relative aspect-square rounded-xl bg-zinc-100 border border-zinc-200 overflow-hidden group">
                            <img :src="p.url" class="w-full h-full object-cover">
                            
                            <!-- Cover badge on index 0 -->
                            <template x-if="index === 0">
                                <span class="absolute top-1.5 left-1.5 bg-[#FDD835] text-black text-[9px] font-black uppercase px-2 py-0.5 rounded shadow">
                                    Cover Photo
                                </span>
                            </template>

                            <button type="button" 
                                    @click="removePhoto(index)" 
                                    class="absolute top-1.5 right-1.5 bg-black/70 hover:bg-black text-white p-1 rounded-full transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 2: Item Information & Categorization -->
            <div class="space-y-4 pt-4 border-t border-zinc-100">
                <h3 class="text-sm font-extrabold text-zinc-950 uppercase tracking-wider">
                    2. Item Details
                </h3>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Listing Title</label>
                    <input type="text" 
                           x-model="title" 
                           placeholder="e.g. ASUS TUF Gaming GeForce RTX 4080 16GB GDDR6X" 
                           class="w-full p-3 bg-zinc-50 border border-zinc-300 rounded-xl text-xs font-bold text-zinc-900 outline-none focus:border-zinc-900 focus:bg-white transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Category Cascader -->
                    <div>
                        <label class="block font-bold text-zinc-700 mb-1">Category</label>
                        <select x-model="category" class="w-full p-3 bg-zinc-50 border border-zinc-300 rounded-xl font-bold outline-none focus:border-zinc-900">
                            <option value="">Select Department</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->slug }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Brand Autocomplete/Picker -->
                    <div>
                        <label class="block font-bold text-zinc-700 mb-1">Hardware Brand</label>
                        <select x-model="brand" class="w-full p-3 bg-zinc-50 border border-zinc-300 rounded-xl font-bold outline-none focus:border-zinc-900">
                            <option value="">Select Brand</option>
                            @foreach($brands as $b)
                                <option value="{{ $b->name }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Condition Segmented Pills -->
                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-2">Condition Grade</label>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs">
                        @php
                            $conditionOptions = [
                                'new' => ['New (Sealed)', 'Unopened box with seal'],
                                'like_new' => ['Like New', 'No signs of cosmetic wear'],
                                'good' => ['Good', 'Gently used & fully working'],
                                'fair' => ['Fair', 'Visible scratches or cosmetic wear'],
                                'for_parts' => ['For Parts', 'Non-working or missing ports'],
                            ];
                        @endphp
                        @foreach($conditionOptions as $val => [$title, $sub])
                            <button type="button" 
                                    @click="condition = '{{ $val }}'"
                                    :class="condition === '{{ $val }}' ? 'border-zinc-950 bg-zinc-950 text-white' : 'border-zinc-200 bg-white hover:border-zinc-400 text-zinc-800'"
                                    class="p-3 rounded-xl border text-left transition-all cursor-pointer">
                                <span class="block font-extrabold text-[11px]">{{ $title }}</span>
                                <span class="block text-[10px] opacity-70 mt-0.5 leading-tight">{{ $sub }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 mb-1">Description & Warranty Notes</label>
                    <textarea x-model="description" rows="4" placeholder="Detail the component's history, thermal performance, overclocking status, accessories included, and box condition..." class="w-full p-3 bg-zinc-50 border border-zinc-300 rounded-xl text-xs outline-none focus:border-zinc-900 focus:bg-white"></textarea>
                </div>
            </div>

            <!-- Step 3: Transparent Fee & Payout Calculator -->
            <div class="space-y-4 pt-4 border-t border-zinc-100">
                <h3 class="text-sm font-extrabold text-zinc-950 uppercase tracking-wider">
                    3. Pricing & Real-Time Payout Calculator
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Listing Price (₹)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-3 text-sm font-bold text-zinc-400">₹</span>
                            <input type="number" 
                                   x-model="price" 
                                   placeholder="e.g. 35000" 
                                   class="w-full pl-8 pr-4 py-3 bg-zinc-50 border border-zinc-300 rounded-xl text-base font-extrabold text-zinc-950 outline-none focus:border-zinc-900 focus:bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 mb-1">Original Retail / MSRP (Optional)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-3 text-sm font-bold text-zinc-400">₹</span>
                            <input type="number" 
                                   x-model="originalPrice" 
                                   placeholder="e.g. 52000" 
                                   class="w-full pl-8 pr-4 py-3 bg-zinc-50 border border-zinc-300 rounded-xl text-base font-medium text-zinc-950 outline-none focus:border-zinc-900 focus:bg-white">
                        </div>
                    </div>
                </div>

                <!-- Payout Breakdown Card (Mercari Section 8.4) -->
                <div class="p-5 rounded-2xl bg-zinc-50 border border-zinc-200/80 space-y-2 text-xs">
                    <div class="flex justify-between text-zinc-600">
                        <span>Listing Price:</span>
                        <span class="font-bold text-zinc-900" x-text="'₹' + (Number(price) || 0).toLocaleString('en-IN')"></span>
                    </div>
                    <div class="flex justify-between text-zinc-600">
                        <span>Platform Commission (<span x-text="commissionPercent"></span>%):</span>
                        <span class="font-bold text-amber-700" x-text="'-₹' + commissionAmount.toLocaleString('en-IN')"></span>
                    </div>
                    <div class="flex justify-between text-zinc-600">
                        <span>Shipping Fee:</span>
                        <span class="font-bold text-emerald-600">Paid by Buyer (₹0 deducted)</span>
                    </div>
                    <div class="flex justify-between text-sm font-black text-zinc-950 pt-3 border-t border-zinc-200 items-baseline">
                        <span>Net Payout to Your Bank:</span>
                        <span class="text-xl font-black text-emerald-600" x-text="'₹' + netPayout.toLocaleString('en-IN')"></span>
                    </div>
                </div>
            </div>

            <!-- Step 4: Shipping & Pickup Address -->
            <div class="space-y-4 pt-4 border-t border-zinc-100">
                <h3 class="text-sm font-extrabold text-zinc-950 uppercase tracking-wider">
                    4. Shipping & Pickup Address
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-zinc-700 mb-1">Pickup City</label>
                        <input type="text" x-model="pickupCity" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 mb-1">Pickup State</label>
                        <input type="text" x-model="pickupState" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                    </div>
                    <div>
                        <label class="block font-bold text-zinc-700 mb-1">Pincode</label>
                        <input type="text" x-model="pickupPincode" class="w-full p-2.5 bg-zinc-50 border border-zinc-300 rounded-xl outline-none focus:border-zinc-900">
                    </div>
                </div>
            </div>

            <!-- Submit CTA -->
            <div class="pt-4 border-t border-zinc-100 flex items-center justify-between">
                <a href="/listings" class="text-xs font-bold text-zinc-500 hover:text-black">Cancel</a>
                
                <button type="button" 
                        @click="submitListing"
                        :disabled="submitting"
                        class="px-8 py-3.5 bg-[#FDD835] hover:bg-[#FBC02D] text-black font-extrabold text-sm rounded-full shadow-sm transition-transform active:scale-95 disabled:opacity-50 cursor-pointer">
                    <span x-text="submitting ? 'Publishing Gear...' : 'Publish Listing Now'"></span>
                </button>
            </div>

        </div>

    </div>

</div>
@endsection
