@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'profile'])

 <!-- Right Content Area -->
 <div class="flex-1 space-y-8">
 @if(session('success'))
 <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-sm">
 {{ session('success') }}
 </div>
 @endif

 @if($errors->any())
 <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-xl text-sm">
 <ul class="list-disc pl-5 space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form action="/dashboard/settings" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ avatarPreview: '{{ $user->display_avatar_url }}', editingUsername: false, username: '{{ old('name', $user->name) }}', showStateDropdown: false, selectedState: '{{ old('state', $user->state) ?: '' }}', typedText: '', typedTimeout: null, statesList: ['Andaman & Nicobar', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh', 'Chhattisgarh', 'Dadra & Nagar Haveli', 'Delhi', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu & Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'], getMatchingStates() { return this.statesList.filter(s => s.toLowerCase().startsWith(this.typedText) || s.toLowerCase().includes(this.typedText)); }, handleKeydown(e) { if (e.key === 'Escape') { this.closeDropdown(); return; } if (e.key === 'Enter') { e.preventDefault(); this.closeDropdown(); return; } if (e.key === 'Backspace') { e.preventDefault(); this.typedText = this.typedText.slice(0, -1); return; } if (e.key.length === 1 && /^[a-zA-Z\s&]$/.test(e.key)) { e.preventDefault(); this.typedText += e.key.toLowerCase(); clearTimeout(this.typedTimeout); this.typedTimeout = setTimeout(() => { this.typedText = ''; }, 3000); } }, closeDropdown() { if (this.showStateDropdown) { const matches = this.getMatchingStates(); if (this.typedText && matches.length > 0) { this.selectedState = matches[0]; } this.showStateDropdown = false; this.typedText = ''; } } }">
 @csrf
  <!-- Profile Card -->
 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden">
 <!-- Photo Row -->
 <div class="flex items-center justify-between p-6 border-b border-zinc-200 gap-4">
 <div>
 <h4 class="font-bold text-[15px] text-zinc-800">Your photo</h4>
 </div>
 <div class="flex items-center gap-6">
 <input type="file" id="avatar-input" name="avatar" class="hidden" accept="image/*" @change="const file = $event.target.files[0]; if (file) { avatarPreview = URL.createObjectURL(file); }">
 <img :src="avatarPreview" alt="{{ $user->name }}" class="w-16 h-16 rounded-full object-cover border border-zinc-150">
 <button type="button" @click="document.getElementById('avatar-input').click()" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors">
 Choose photo
 </button>
 </div>
 </div>

 <!-- Username Row -->
 <div class="flex items-center justify-between p-6 border-b border-zinc-200 gap-4">
 <div>
 <h4 class="font-bold text-[15px] text-zinc-800">Username</h4>
 </div>
 <div class="flex items-center gap-4 flex-1 justify-end">
 <!-- Toggle edit view for better UX -->
 <template x-if="!editingUsername">
 <div class="flex items-center gap-4">
 <span class="text-sm font-semibold text-zinc-800" x-text="username"></span>
 <input type="hidden" name="name" x-model="username">
 <button type="button" @click="editingUsername = true" class="px-4 py-2 border border-[#e6c019] text-[#e6c019] text-xs font-bold rounded-sm bg-white cursor-pointer hover:bg-zinc-50 transition-colors">
 Change username
 </button>
 </div>
 </template>
 <template x-if="editingUsername">
 <div class="flex items-center gap-2">
 <input type="text" name="name" x-model="username" class="text-sm text-zinc-800 border border-zinc-300 rounded-sm px-3 py-1.5 focus:border-[#e6c019] focus:outline-none w-48">
 <button type="button" @click="editingUsername = false" class="px-3 py-1.5 bg-[#e6c019] text-black text-xs font-bold rounded-sm cursor-pointer hover:bg-[#fdd835] transition-colors">
 Done
 </button>
 </div>
 </template>
 </div>
 </div>

 <!-- About Row -->
 <div class="flex flex-col md:flex-row md:items-center justify-between p-6 gap-2">
 <div class="w-32 shrink-0">
 <h4 class="font-bold text-[15px] text-zinc-800">About you</h4>
 </div>
 <div class="flex-1">
 <textarea name="about" rows="2" placeholder="Tell us more about yourself and your style" class="w-full text-sm text-zinc-700 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none resize-none placeholder-zinc-400 pb-2 focus:ring-0 text-left">{{ old('about', $user->about) }}</textarea>
 </div>
 </div>
 </div>

 <!-- Location Section -->
 <div class="space-y-3">
 <h3 class="text-xs text-zinc-400 font-bold uppercase tracking-wider">My location</h3>
 <div class="bg-white border border-zinc-200 rounded-sm divide-y divide-zinc-200">
 <!-- Country -->
 <div class="flex items-center p-5 gap-4">
 <label for="country" class="w-32 shrink-0 font-bold text-[15px] text-zinc-800">Country</label>
 <div class="flex items-center gap-1 border-b border-zinc-150 focus-within:border-[#e6c019]">
 <select id="country" name="country" class="text-sm text-zinc-700 bg-transparent border-none outline-none cursor-pointer pr-8 focus:ring-0 font-medium pb-0.5 appearance-none">
 <option value="India" {{ old('country', $user->country) === 'India' ? 'selected' : '' }}>India</option>
 <option value="United States" {{ old('country', $user->country) === 'United States' ? 'selected' : '' }}>United States</option>
 </select>
 <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 mb-0.5 pointer-events-none -ml-5"></i>
 </div>
 </div>

 <!-- City -->
 <div class="flex flex-col sm:flex-row sm:items-center p-5 gap-4">
 <label for="city" class="w-32 shrink-0 font-bold text-[15px] text-zinc-800">Town/City</label>
 <div class="flex flex-col sm:flex-row sm:items-center gap-4 flex-1 w-full max-w-md">
 <input type="text" name="city" placeholder="City" value="{{ old('city', $user->city) }}" class="text-sm text-zinc-700 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none placeholder-zinc-350 pr-1 focus:ring-0 font-medium pb-0.5 flex-1 w-full">
 <div class="relative flex items-center border-b border-zinc-200 focus-within:border-[#e6c019] pb-0.5 w-full sm:w-40" @click.away="closeDropdown()">
 <input type="hidden" name="state" :value="selectedState">
 <button type="button" @click="showStateDropdown = !showStateDropdown" class="w-full flex items-center justify-between text-sm text-zinc-700 bg-transparent border-none outline-none focus:ring-0 font-medium cursor-pointer text-left focus:outline-none pr-1">
 <span x-text="typedText ? typedText : (selectedState || 'Select State')" :class="typedText ? 'text-zinc-800 font-bold bg-amber-50 px-1 border-b-2 border-[#e6c019]' : (selectedState ? 'text-zinc-700' : 'text-zinc-400')"></span>
 <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 shrink-0 pointer-events-none"></i>
 </button>
  <!-- Custom Dropdown with search-by-typing -->
 <div x-show="showStateDropdown"  x-transition:enter="transition ease-out duration-100"
 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
 x-transition:leave="transition ease-in duration-75"
 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
 x-transition:leave-end="opacity-0 translate-y-1 scale-95"
 class="absolute left-0 right-0 sm:right-auto sm:w-60 top-full mt-1.5 max-h-60 overflow-y-auto bg-white border border-zinc-200 rounded-sm shadow-lg py-1 text-sm text-zinc-750 z-50 focus:outline-none"
 style="display: none;"
 @keydown.window="if (showStateDropdown) { handleKeydown($event) }">
  <div class="py-1">
 <button type="button" @click="selectedState = ''; showStateDropdown = false; typedText = ''" class="w-full text-left px-3 py-1.5 text-xs font-semibold text-zinc-400 hover:bg-zinc-50 transition-colors">
 Select State
 </button>
  <template x-for="stateOpt in ['Andaman & Nicobar', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh', 'Chhattisgarh', 'Dadra & Nagar Haveli', 'Delhi', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu & Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal']" :key="stateOpt">
 <button type="button"  x-show="typedText === '' || stateOpt.toLowerCase().startsWith(typedText) || stateOpt.toLowerCase().includes(typedText)"
 @click="selectedState = stateOpt; showStateDropdown = false; typedText = ''"  class="w-full text-left px-3 py-1.5 text-xs hover:bg-zinc-50 hover:text-zinc-950 transition-colors flex items-center justify-between"
 :class="selectedState === stateOpt ? 'bg-amber-50/50 text-zinc-900 font-bold' : 'text-zinc-700'">
 <span x-text="stateOpt"></span>
 <span x-show="selectedState === stateOpt">
 <svg class="w-3.5 h-3.5 text-[#e6c019] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
 </span>
 </button>
 </template>

 <!-- No Match State -->
 <div x-show="typedText !== '' && !['Andaman & Nicobar', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh', 'Chhattisgarh', 'Dadra & Nagar Haveli', 'Delhi', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu & Kashmir', 'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh', 'Uttarakhand', 'West Bengal'].some(s => s.toLowerCase().startsWith(typedText) || s.toLowerCase().includes(typedText))"
 class="px-3 py-3 text-xs text-zinc-400 text-center font-medium">
 No match found
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Language Section -->
 <div class="space-y-3">
 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden">
 <div class="flex items-center justify-between p-5 gap-4">
 <label for="language" class="font-bold text-[15px] text-zinc-800">Language</label>
 <div class="flex items-center gap-1 border-b border-zinc-150 focus-within:border-[#e6c019]">
 <select id="language" name="language" class="text-sm text-zinc-700 bg-transparent border-none outline-none cursor-pointer pr-8 focus:ring-0 font-medium pb-0.5 appearance-none">
 <option value="en" {{ old('language', $user->language ?? 'en') === 'en' ? 'selected' : '' }}>English, US (English)</option>
 <option value="hi" {{ old('language', $user->language) === 'hi' ? 'selected' : '' }}>Hindi (हिन्दी)</option>
 </select>
 <i data-lucide="chevron-down" class="w-4 h-4 text-zinc-400 mb-0.5 pointer-events-none -ml-5"></i>
 </div>
 </div>
 </div>
 </div>

 <!-- Submit Button -->
 <div class="flex justify-end pt-4">
 <button type="submit" class="px-8 py-3 bg-[#e6c019] text-black font-bold text-sm rounded-sm cursor-pointer">
 Update profile
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
</div>
@endsection
