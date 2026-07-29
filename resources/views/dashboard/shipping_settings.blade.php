@extends('layouts.app')

@section('title', 'Shipping Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'shipping'])

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 <!-- Address Section -->
 <div class="space-y-2">
 <h3 class="text-lg font-bold text-zinc-900">Your address</h3>
  @if(session('success'))
 <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
 {{ session('success') }}
 </div>
 @endif

 <form action="/dashboard/settings/shipping" method="POST" class="bg-white border border-zinc-200 rounded-sm p-5 space-y-4">
 @csrf
 <div class="flex items-center justify-between">
 <span class="text-sm font-bold text-zinc-800">Add or update your address</span>
 @if($user->address)
 <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
 <i data-lucide="check" class="w-3.5 h-3.5"></i> Saved
 </span>
 @else
 <span class="text-xs font-bold text-zinc-400">No address saved</span>
 @endif
 </div>
  <div class="border-b border-zinc-200 focus-within:border-[#e6c019] pb-1">
 <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Enter your full street address, city, and postal code" class="w-full text-sm text-zinc-700 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium">
 </div>

 <div class="flex justify-end">
 <button type="submit" class="px-5 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm cursor-pointer hover:bg-[#fdd835] transition-colors">
 Save Address
 </button>
 </div>
 </form>

 <p class="text-xs text-zinc-400 px-1">
 Where couriers will collect or deliver orders, and what we'll use to process returns.
 </p>
 </div>

 <!-- Alert Information Box -->
 <div class="bg-zinc-50 border border-zinc-200 rounded-sm p-4 flex gap-3.5 items-start">
 <div class="w-5 h-5 rounded-full bg-[#e6c019] text-black flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold font-serif">
 i
 </div>
 <p class="text-sm text-zinc-650 leading-relaxed">
 Disabling shipping options may reduce sales. If a member can only buy from you with a disabled option, we may still offer it.  <a href="#" class="text-[#e6c019] hover:underline font-semibold">Learn more about disabled options.</a>
 </p>
 </div>

 <!-- Shipping as Seller Section -->
 <div class="space-y-4 pt-2" x-data="{
 activeAccordion: null,
 saving: false,
 savingAddress: false,
 addressSavedMessage: '',
 pickupAddress: '{{ old('address', $user->address) }}',
 ship_bluedart_pickup: {{ $user->ship_bluedart_pickup ? 'true' : 'false' }},
 ship_delhivery_pickup: {{ $user->ship_delhivery_pickup ? 'true' : 'false' }},
 ship_dtdc_pickup: {{ $user->ship_dtdc_pickup ? 'true' : 'false' }},
 ship_delhivery_dropoff: {{ $user->ship_delhivery_dropoff ? 'true' : 'false' }},
 ship_bluedart_dropoff: {{ $user->ship_bluedart_dropoff ? 'true' : 'false' }},
 ship_dtdc_dropoff: {{ $user->ship_dtdc_dropoff ? 'true' : 'false' }},
 async toggleOption(key) {
 this[key] = !this[key];
 this.saving = true;
 try {
 const payload = {
 ship_bluedart_pickup: this.ship_bluedart_pickup,
 ship_delhivery_pickup: this.ship_delhivery_pickup,
 ship_dtdc_pickup: this.ship_dtdc_pickup,
 ship_delhivery_dropoff: this.ship_delhivery_dropoff,
 ship_bluedart_dropoff: this.ship_bluedart_dropoff,
 ship_dtdc_dropoff: this.ship_dtdc_dropoff,
 };
  const formData = new FormData();
 for (const [k, v] of Object.entries(payload)) {
 if (v) formData.append(k, '1');
 }

 await fetch('/dashboard/settings/shipping-options', {
 method: 'POST',
 headers: {
 'X-CSRF-TOKEN': '{{ csrf_token() }}'
 },
 body: formData
 });
 } catch (err) {
 console.error(err);
 } finally {
 setTimeout(() => { this.saving = false; }, 300);
 }
 },
 async savePickupAddress() {
 if (!this.pickupAddress.trim()) {
 return;
 }
 this.savingAddress = true;
 this.addressSavedMessage = '';
 try {
 const formData = new FormData();
 formData.append('address', this.pickupAddress);

 const response = await fetch('/dashboard/settings/shipping', {
 method: 'POST',
 headers: {
 'X-CSRF-TOKEN': '{{ csrf_token() }}'
 },
 body: formData
 });
 if (response.ok) {
 this.addressSavedMessage = 'Pickup address updated successfully!';
 setTimeout(() => { this.addressSavedMessage = ''; }, 3000);
  // Update main address input text if visible on page
 const mainAddrInput = document.querySelector('input[name=\'address\']');
 if (mainAddrInput) {
 mainAddrInput.value = this.pickupAddress;
 }
 }
 } catch (err) {
 console.error(err);
 } finally {
 this.savingAddress = false;
 }
 }
 }">
 <div class="flex items-center justify-between gap-4">
 <div class="space-y-1">
 <h3 class="text-lg font-bold text-zinc-900">Shipping as a seller</h3>
 <p class="text-sm text-zinc-400">
 Choose which options you'd like to use for each shipping type.
 </p>
 </div>
 <div class="text-xs font-semibold text-zinc-400 select-none flex items-center gap-1.5 h-6">
 <template x-if="saving">
 <span class="flex items-center gap-1 text-zinc-500">
 <svg class="animate-spin h-3.5 w-3.5 text-[#e6c019]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 Saving...
 </span>
 </template>
 <template x-if="!saving">
 <span class="text-emerald-600 flex items-center gap-1">
 <i data-lucide="check" class="w-3.5 h-3.5"></i> Saved
 </span>
 </template>
 </div>
 </div>

 <!-- Accordion Options -->
 <div class="space-y-4">
 <!-- Option 1: From your address -->
 <div class="space-y-2 border border-zinc-200 rounded-sm bg-white overflow-hidden">
 <div @click="activeAccordion = (activeAccordion === 'address' ? null : 'address')" class="p-5 flex items-center justify-between cursor-pointer hover:bg-zinc-50 transition-colors select-none">
 <div class="flex items-center gap-4">
 <i data-lucide="home" class="w-6 h-6 text-zinc-400"></i>
 <div>
 <h4 class="text-sm font-bold text-zinc-800">From your address</h4>
 <p class="text-xs text-zinc-400 mt-0.5">A courier collects the order from you.</p>
 </div>
 </div>
 <i data-lucide="chevron-down" class="w-5 h-5 text-zinc-400 transform transition-transform duration-200" :class="activeAccordion === 'address' ? 'rotate-180' : ''"></i>
 </div>
  <!-- Accordion Content -->
 <div x-show="activeAccordion === 'address'" class="border-t border-zinc-150 divide-y divide-zinc-150 bg-zinc-50/50">
 <!-- Doorstep Pickup Address Setting -->
 <div class="p-4 pl-14 bg-amber-50/30 space-y-2">
 <div>
 <span class="text-sm font-bold text-zinc-800">Doorstep Collection Address</span>
 <p class="text-xs text-zinc-400 mt-0.5">Please provide the complete address where the courier should collect packages.</p>
 </div>
 <div class="flex gap-2 max-w-xl">
 <input type="text"  x-model="pickupAddress"  placeholder="Enter doorstep pickup address"  class="flex-1 text-sm text-zinc-700 bg-white border border-zinc-200 rounded-sm px-3 py-1.5 focus:border-[#e6c019] focus:outline-none">
 <button type="button"  @click="savePickupAddress()"  class="px-4 py-1.5 bg-[#e6c019] text-black text-xs font-bold rounded-sm hover:bg-[#fdd835] transition-colors flex items-center gap-1 shrink-0">
 <template x-if="!savingAddress">
 <span>Save Address</span>
 </template>
 <template x-if="savingAddress">
 <span class="flex items-center gap-1">
 <svg class="animate-spin h-3.5 w-3.5 text-black" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
 <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
 <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
 </svg>
 Saving...
 </span>
 </template>
 </button>
 </div>
 <template x-if="addressSavedMessage">
 <p class="text-xs text-emerald-600 font-semibold" x-text="addressSavedMessage"></p>
 </template>
 </div>
 <!-- BlueDart Pickup -->
 <div class="flex items-center justify-between p-4 pl-14">
 <div>
 <span class="text-sm font-bold text-zinc-800">BlueDart (Home Collection)</span>
 <p class="text-xs text-zinc-400 mt-0.5">Ship securely via BlueDart express delivery.</p>
 </div>
 <button type="button"  @click="toggleOption('ship_bluedart_pickup')"  class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
 :class="ship_bluedart_pickup ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
 :class="ship_bluedart_pickup ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- Delhivery Pickup -->
 <div class="flex items-center justify-between p-4 pl-14">
 <div>
 <span class="text-sm font-bold text-zinc-800">Delhivery (Home Collection)</span>
 <p class="text-xs text-zinc-400 mt-0.5">Instant scheduling and pickup from your doorstep.</p>
 </div>
 <button type="button"  @click="toggleOption('ship_delhivery_pickup')"  class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
 :class="ship_delhivery_pickup ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
 :class="ship_delhivery_pickup ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- DTDC Pickup -->
 <div class="flex items-center justify-between p-4 pl-14">
 <div>
 <span class="text-sm font-bold text-zinc-800">DTDC (Home Collection)</span>
 <p class="text-xs text-zinc-400 mt-0.5">Reliable local and domestic postal delivery network.</p>
 </div>
 <button type="button"  @click="toggleOption('ship_dtdc_pickup')"  class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
 :class="ship_dtdc_pickup ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
 :class="ship_dtdc_pickup ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>
 </div>
 </div>

 <!-- Option 2: From a drop-off point -->
 <div class="space-y-2 border border-zinc-200 rounded-sm bg-white overflow-hidden">
 <div @click="activeAccordion = (activeAccordion === 'dropoff' ? null : 'dropoff')" class="p-5 flex items-center justify-between cursor-pointer hover:bg-zinc-50 transition-colors select-none">
 <div class="flex items-center gap-4">
 <i data-lucide="package" class="w-6 h-6 text-zinc-400"></i>
 <div>
 <h4 class="text-sm font-bold text-zinc-800">From a drop-off point</h4>
 <p class="text-xs text-zinc-400 mt-0.5">You take the order to a location like a locker or package shop.</p>
 </div>
 </div>
 <i data-lucide="chevron-down" class="w-5 h-5 text-zinc-400 transform transition-transform duration-200" :class="activeAccordion === 'dropoff' ? 'rotate-180' : ''"></i>
 </div>

 <!-- Accordion Content -->
 <div x-show="activeAccordion === 'dropoff'" class="border-t border-zinc-150 divide-y divide-zinc-150 bg-zinc-50/50">
 <!-- Delhivery Dropoff -->
 <div class="flex items-center justify-between p-4 pl-14">
 <div>
 <span class="text-sm font-bold text-zinc-800">Delhivery Drop-off</span>
 <p class="text-xs text-zinc-400 mt-0.5">Drop off packages at any local Delhivery store/partner.</p>
 </div>
 <button type="button"  @click="toggleOption('ship_delhivery_dropoff')"  class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
 :class="ship_delhivery_dropoff ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
 :class="ship_delhivery_dropoff ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- BlueDart Dropoff -->
 <div class="flex items-center justify-between p-4 pl-14">
 <div>
 <span class="text-sm font-bold text-zinc-800">BlueDart Drop-off</span>
 <p class="text-xs text-zinc-400 mt-0.5">Drop off packages at designated BlueDart service centers.</p>
 </div>
 <button type="button"  @click="toggleOption('ship_bluedart_dropoff')"  class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
 :class="ship_bluedart_dropoff ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
 :class="ship_bluedart_dropoff ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- DTDC Dropoff -->
 <div class="flex items-center justify-between p-4 pl-14">
 <div>
 <span class="text-sm font-bold text-zinc-800">DTDC Drop-off</span>
 <p class="text-xs text-zinc-400 mt-0.5">Drop off at DTDC counters nationwide.</p>
 </div>
 <button type="button"  @click="toggleOption('ship_dtdc_dropoff')"  class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer"
 :class="ship_dtdc_dropoff ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200"
 :class="ship_dtdc_dropoff ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Footer Information Links -->
 <div class="space-y-3 pt-4 text-xs text-zinc-400 leading-relaxed">
 <p>Some shipping options are enabled for all sellers on our platform and can't be turned off.</p>
 <p>
 <a href="#" class="text-[#e6c019] hover:underline font-semibold">See compensation information</a> for sellers using integrated shipping.
 </p>
 </div>
 </div>
 </div>
 </div>
</div>
@endsection
