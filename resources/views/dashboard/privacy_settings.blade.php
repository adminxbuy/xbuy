@extends('layouts.app')

@section('title', 'Privacy Settings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'privacy'])

 <!-- Right Content Area -->
 <div class="flex-1 space-y-8">
 <!-- Session messages -->
 @if(session('success'))
 <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
 {{ session('success') }}
 </div>
 @endif

 <form action="/dashboard/settings/privacy" method="POST" class="space-y-6" x-data="{
 featureMarketing: {{ $user->privacy_feature_marketing ? 'true' : 'false' }},
 notifyFavorites: {{ $user->privacy_notify_favorites ? 'true' : 'false' }},
 personalizeFeed: {{ $user->privacy_personalize_feed ? 'true' : 'false' }},
 recentlyViewed: {{ $user->privacy_recently_viewed ? 'true' : 'false' }}
 }">
 @csrf
  <div class="space-y-4">
 <span class="text-xs text-zinc-400 font-bold uppercase tracking-wider block mb-2">Privacy settings</span>
  <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
 <!-- Marketing Campaigns -->
 <div class="flex items-center justify-between p-5 gap-4">
 <div class="space-y-0.5">
 <h4 class="text-sm font-bold text-zinc-800">Feature my items in marketing campaigns for a chance to sell faster</h4>
 <p class="text-xs text-zinc-400 leading-relaxed">This allows Vinted to showcase my items on social media and other websites. The increased visibility could lead to quicker sales.</p>
 </div>
 <input type="hidden" name="privacy_feature_marketing" :value="featureMarketing ? 1 : 0">
 <button type="button" @click="featureMarketing = !featureMarketing" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer shrink-0" :class="featureMarketing ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="featureMarketing ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- Notify Owners -->
 <div class="flex items-center justify-between p-5 gap-4">
 <div>
 <h4 class="text-sm font-bold text-zinc-800">Notify owners when I favorite their items</h4>
 </div>
 <input type="hidden" name="privacy_notify_favorites" :value="notifyFavorites ? 1 : 0">
 <button type="button" @click="notifyFavorites = !notifyFavorites" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer shrink-0" :class="notifyFavorites ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="notifyFavorites ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- Personalize Feed -->
 <div class="flex items-center justify-between p-5 gap-4">
 <div>
 <h4 class="text-sm font-bold text-zinc-800 leading-relaxed">Allow Vinted to personalize my feed and search results by evaluating my preferences, settings, previous purchases and usage of Vinted website and app</h4>
 </div>
 <input type="hidden" name="privacy_personalize_feed" :value="personalizeFeed ? 1 : 0">
 <button type="button" @click="personalizeFeed = !personalizeFeed" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer shrink-0" :class="personalizeFeed ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="personalizeFeed ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- Recently Viewed -->
 <div class="flex items-center justify-between p-5 gap-4">
 <div class="space-y-0.5">
 <h4 class="text-sm font-bold text-zinc-800">Allow Vinted to display my recently viewed items on my Homepage.</h4>
 <p class="text-xs text-zinc-400 leading-relaxed">If you turn this option off but allow personalized content, these items will still be used to personalize your Feed.</p>
 </div>
 <input type="hidden" name="privacy_recently_viewed" :value="recentlyViewed ? 1 : 0">
 <button type="button" @click="recentlyViewed = !recentlyViewed" class="w-12 h-6 rounded-full p-0.5 focus:outline-none cursor-pointer shrink-0" :class="recentlyViewed ? 'bg-[#e6c019]' : 'bg-zinc-200'">
 <div class="w-5 h-5 bg-white rounded-full transform transition-transform duration-200" :class="recentlyViewed ? 'translate-x-6' : 'translate-x-0'"></div>
 </button>
 </div>

 <!-- Manage account data -->
 <a href="/dashboard/settings/privacy/download-data" class="p-5 flex items-center justify-between cursor-pointer hover:bg-zinc-50 transition-colors block">
 <div class="space-y-0.5">
 <span class="text-sm font-bold text-zinc-800">Manage account data</span>
 <p class="text-xs text-zinc-400">Request and download a copy of your Vinted account data.</p>
 </div>
 <i data-lucide="chevron-right" class="w-5 h-5 text-zinc-400"></i>
 </a>
 </div>
 </div>

 <!-- Submit Button -->
 <div class="flex justify-end pt-4">
 <button type="submit" class="px-8 py-3 bg-[#e6c019] text-black font-bold text-sm rounded-sm cursor-pointer">
 Save
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
</div>
@endsection
