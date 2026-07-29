@extends('layouts.app')

@section('title', 'Add Bank Account')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
 @include('dashboard.partials._sidebar', ['active' => 'payments'])

 <!-- Right Content Area (Wider Container) -->
 <div class="flex-1 space-y-8">
 <!-- Breadcrumbs / Back button (no hover effects) -->
 <div>
 <a href="/dashboard/settings/payments" class="inline-flex items-center gap-2 text-base text-zinc-500 font-semibold">
 <i data-lucide="arrow-left" class="w-5 h-5"></i> Back to Payments
 </a>
 </div>

 @if($errors->any())
 <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-sm text-sm">
 <ul class="list-disc pl-5 space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form action="/dashboard/settings/payments/bank-account" method="POST" class="space-y-8">
 @csrf

 <!-- Card 1: Account details (Larger padding, no shadows, no focus transitions) -->
 <div class="bg-white border border-zinc-200 rounded-sm p-8 md:p-10 space-y-8">
 <h3 class="text-sm font-bold text-zinc-400 uppercase tracking-wider">Account details</h3>
  <div class="space-y-2">
 <!-- Bank Name -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 py-5">
 <label for="bank_name" class="text-base font-medium text-zinc-700 md:w-4/12 select-none">Bank Name</label>
 <div class="flex-1 w-full">
 <input type="text" id="bank_name" name="bank_name" required  value="{{ old('bank_name', $user->sellerProfile->bank_name ?? '') }}"  placeholder="e.g. State Bank of India"  class="w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-300">
 </div>
 </div>

 <!-- Account holder's name -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 py-5">
 <label for="bank_account_name" class="text-base font-medium text-zinc-700 md:w-4/12 select-none">Account holder's name</label>
 <div class="flex-1 w-full">
 <input type="text" id="bank_account_name" name="bank_account_name" required  value="{{ old('bank_account_name', $user->sellerProfile->bank_account_name ?? '') }}"  placeholder="e.g. John Doe"  class="w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-300">
 </div>
 </div>

 <!-- Account No. -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 py-5">
 <label for="bank_account_number" class="text-base font-medium text-zinc-700 md:w-4/12 select-none">Account No.</label>
 <div class="flex-1 w-full">
 <input type="text" id="bank_account_number" name="bank_account_number" required  value="{{ old('bank_account_number', $user->sellerProfile->bank_account_number ?? '') }}"  placeholder="e.g. 3020041011"  class="w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-300">
 </div>
 </div>

 <!-- Routing No. / IFSC -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 py-5">
 <div class="flex items-center gap-2 md:w-4/12">
 <label for="bank_ifsc" class="text-base font-medium text-zinc-700 select-none">Routing No. (IFSC)</label>
 <div class="group relative cursor-help">
 <i data-lucide="help-circle" class="w-4 h-4 text-zinc-400"></i>
 <span class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-56 bg-zinc-900 text-white text-xs rounded p-3 opacity-0 pointer-events-none group-hover:opacity-100 transition-opacity leading-normal z-10 text-center font-normal">
 Your 11-digit IFSC code from your bank branch.
 </span>
 </div>
 </div>
 <div class="flex-1 w-full">
 <input type="text" id="bank_ifsc" name="bank_ifsc" required  value="{{ old('bank_ifsc', $user->sellerProfile->bank_ifsc ?? '') }}"  placeholder="e.g. SBIN0001234"  class="w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium uppercase placeholder-zinc-300">
 </div>
 </div>

 <!-- Account type -->
 <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 py-5">
 <label for="account_type" class="text-base font-medium text-zinc-700 md:w-4/12 select-none">Account type</label>
 <div class="flex-1 w-full relative flex items-center">
 <select id="account_type" class="w-full text-base text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 pr-8 font-medium cursor-pointer appearance-none">
 <option value="savings" selected>Savings</option>
 <option value="current">Current</option>
 </select>
 <div class="absolute right-0 pointer-events-none text-zinc-450">
 <i data-lucide="chevron-down" class="w-4 h-4"></i>
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Card 2: Billing address (Larger padding, no shadows, no hovers) -->
 <div class="bg-white border border-zinc-200 rounded-sm p-8 md:p-10 space-y-4">
 <h3 class="text-sm font-bold text-zinc-400 uppercase tracking-wider">Billing address</h3>
  @if($user->address)
 <div class="flex items-center justify-between py-2 cursor-pointer" onclick="window.location='/dashboard/settings/shipping'">
 <div class="space-y-1">
 <p class="text-base font-bold text-zinc-800">{{ $user->name }}</p>
 <p class="text-base text-zinc-550 leading-relaxed max-w-2xl">{{ $user->address }}</p>
 </div>
 <div class="text-zinc-400">
 <i data-lucide="chevron-right" class="w-6 h-6"></i>
 </div>
 </div>
 @else
 <a href="/dashboard/settings/shipping" class="flex items-center justify-between py-3">
 <span class="text-base font-bold text-zinc-800">Add address</span>
 <i data-lucide="chevron-right" class="w-6 h-6 text-zinc-400"></i>
 </a>
 @endif
 </div>

 <!-- Disclaimer -->
 <p class="text-xs text-zinc-400 leading-relaxed px-1">
 We never share your personal details with anyone other than our payment provider for your withdrawals, or unless we're legally obligated to do so (e.g. by tax authorities).
 </p>

 <!-- Submit / Save button (No hover background/shadow shifts) -->
 <div class="flex justify-end pt-2">
 <button type="submit" class="px-12 py-3.5 bg-[#e6c019] text-black text-sm font-bold rounded-sm uppercase tracking-wider">
 Save
 </button>
 </div>
 </form>
 </div>
 </div>
 </div>
</div>
@endsection
