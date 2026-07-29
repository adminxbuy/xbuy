@extends('layouts.app')

@section('title', 'Manage Account Data')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'privacy'])

 <!-- Right Content Area -->
 <div class="flex-1 space-y-8">
  <!-- Header with Back Button and Request Button -->
 <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-6 border-b border-zinc-200">
 <div class="flex items-start gap-3">
 <a href="/dashboard/settings/privacy" class="text-zinc-650 hover:text-zinc-950 transition-colors mt-1 shrink-0">
 <i data-lucide="chevron-left" class="w-6 h-6"></i>
 </a>
 <div class="space-y-2">
 <h2 class="text-2xl font-bold text-zinc-900 leading-tight">Manage account data</h2>
 <p class="text-sm text-zinc-500 leading-relaxed max-w-xl">
 Request and download a copy of your account data.  <a href="#" class="text-[#007f87] hover:underline font-semibold">Learn more about account data</a>
 </p>
 </div>
 </div>
  <div>
 <form action="/dashboard/settings/privacy/download-data/export" method="POST">
 @csrf
 <button type="submit" class="px-6 py-3 bg-[#e6c019] text-black text-sm font-bold rounded-sm transition-colors whitespace-nowrap">
 Request data
 </button>
 </form>
 </div>
 </div>

 <!-- Explanation Cards -->
 <div class="space-y-6">
  <!-- Card 1: What's included -->
 <div class="bg-white border border-zinc-200 rounded-sm p-8 space-y-5">
 <div class="w-10 h-10 rounded-full bg-zinc-50 flex items-center justify-center text-[#007f87]">
 <i data-lucide="search" class="w-5 h-5"></i>
 </div>
  <div class="space-y-3">
 <h3 class="text-lg font-bold text-zinc-900">What's included in the data export?</h3>
 <p class="text-sm text-zinc-500 leading-relaxed">
 It includes your profile details, items, messages, and a broad range of other data related to your account.
 </p>
 <p class="text-sm text-zinc-500 leading-relaxed">
 Want more information on why we collect and use your data, who we share it with, or how long we keep it?  <a href="#" class="text-[#007f87] hover:underline font-semibold">Contact us</a>
 </p>
 </div>
 </div>

 <!-- Card 2: Formatting -->
 <div class="bg-white border border-zinc-200 rounded-sm p-8 space-y-5">
 <div class="w-10 h-10 rounded-full bg-zinc-50 flex items-center justify-center text-[#007f87]">
 <i data-lucide="package" class="w-5 h-5"></i>
 </div>
  <div class="space-y-3">
 <h3 class="text-lg font-bold text-zinc-900">How is your data formatted?</h3>
 <p class="text-sm text-zinc-500 leading-relaxed">
 A copy of your data will be provided in a JSON file format containing your account information, registered details, and order history.
 </p>
 </div>
 </div>

 </div>

 </div>
 </div>
 </div>
</div>
@endsection
