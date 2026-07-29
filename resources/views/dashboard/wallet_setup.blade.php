@extends('layouts.app')

@section('title', 'Setup Wallet')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
  <!-- Left Sidebar Navigation (No shadows, flat active state) -->
 <div class="w-full md:w-1/4 shrink-0 space-y-6">
 <h2 class="text-2xl font-bold text-zinc-900">Wallet</h2>
 <nav class="flex flex-col gap-3">
 <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider px-1.5 mt-2">My Wallet</div>
 <a href="/dashboard/wallet" class="text-base font-medium text-zinc-400 px-1.5 py-0.5">
 Wallet
 </a>
 <a href="/dashboard/wallet/setup" class="text-lg font-bold text-zinc-955 px-1.5 py-0.5">
 Setup
 </a>
 <div class="text-xs font-bold text-zinc-400 uppercase tracking-wider px-1.5 mt-4">Transactions</div>
 <a href="/dashboard/wallet/history" class="text-base font-medium text-zinc-400 px-1.5 py-0.5">
 Payment history
 </a>
 <a href="/dashboard/wallet/invoices" class="text-base font-medium text-zinc-400 px-1.5 py-0.5">
 Invoices
 </a>
 <a href="/dashboard/wallet/income" class="text-base font-medium text-zinc-400 px-1.5 py-0.5">
 Income
 </a>
 </nav>
 </div>

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 @if($errors->any())
 <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-sm text-sm">
 <ul class="list-disc pl-5 space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <form action="{{ route('dashboard.wallet.setup.save') }}" method="POST" class="space-y-6">
 @csrf

 <!-- Personal Info Card (Flat, no shadows) -->
 <div class="bg-white border border-zinc-200 rounded-sm p-6">
 <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Personal Info</h3>

 <!-- First Name -->
 <div class="flex flex-col md:flex-row md:items-center justify-between py-4 border-b border-zinc-200">
 <label for="first_name" class="text-[15px] font-bold text-zinc-800 w-56 shrink-0">First name</label>
 <input type="text" id="first_name" name="first_name" required  placeholder="e.g. Jane"  value="{{ old('first_name', explode(' ', $user->name)[0] ?? '') }}"
 class="flex-1 text-[15px] text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350">
 </div>

 <!-- Last Name -->
 <div class="flex flex-col md:flex-row md:items-center justify-between py-4 border-b border-zinc-200">
 <label for="last_name" class="text-[15px] font-bold text-zinc-800 w-56 shrink-0">Last name</label>
 <input type="text" id="last_name" name="last_name" required  placeholder="e.g. Doe"  value="{{ old('last_name', explode(' ', $user->name)[1] ?? '') }}"
 class="flex-1 text-[15px] text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350">
 </div>

 <!-- Date of Birth -->
 <div class="flex flex-col md:flex-row md:items-center justify-between py-4 border-b border-zinc-200">
 <span class="text-[15px] font-bold text-zinc-800 w-56 shrink-0">Date of birth</span>
 <div class="flex-1 flex gap-4 items-center">
 <input type="number" name="dob_day" min="1" max="31" placeholder="Day" required
 class="w-16 text-[15px] text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350">
  <div class="w-32 relative flex items-center">
 <select name="dob_month" required
 class="w-full text-[15px] text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium cursor-pointer appearance-none pr-6">
 <option value="" disabled selected>Month</option>
 @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $month)
 <option value="{{ $month }}">{{ $month }}</option>
 @endforeach
 </select>
 <div class="absolute right-2 pointer-events-none text-zinc-400">
 <i data-lucide="chevron-down" class="w-4 h-4"></i>
 </div>
 </div>

 <input type="number" name="dob_year" min="1900" max="{{ date('Y') }}" placeholder="Year" required
 class="w-20 text-[15px] text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350">
 </div>
 </div>

 <!-- SSN Last 4 -->
 <div class="flex flex-col md:flex-row md:items-start justify-between py-4 border-b border-zinc-200">
 <label for="ssn_last_four" class="text-[15px] font-bold text-zinc-800 w-56 shrink-0 pt-0.5">Social Security number (last 4 digits)</label>
 <div class="flex-1 space-y-2">
 <input type="password" id="ssn_last_four" name="ssn_last_four" required maxlength="4"
 placeholder="e.g. 6789"  class="w-full text-[15px] text-zinc-800 bg-transparent border-none outline-none focus:ring-0 p-0 font-medium placeholder-zinc-350">
 <p class="text-xs text-zinc-400 leading-normal font-medium">
 We don't store this information. Your Social Security number (SSN) is only required by our payment service provider for identity verification purposes.
 </p>
 </div>
 </div>
 </div>

 <!-- Billing Address Card (Flat, no shadows) -->
 <div class="bg-white border border-zinc-200 rounded-sm p-6">
 <h3 class="text-xs font-bold text-zinc-400 uppercase tracking-wider mb-2">Billing address</h3>
 <div class="flex flex-col md:flex-row md:items-start justify-between py-4">
 <label for="billing_address" class="text-[15px] font-bold text-zinc-800 w-56 shrink-0 pt-0.5">Billing address</label>
 <div class="flex-1 w-full">
 <textarea id="billing_address" name="billing_address" required rows="2"
 placeholder="Enter your billing address details"  class="w-full text-[15px] text-zinc-800 bg-transparent border-b border-zinc-200 focus:border-[#e6c019] outline-none resize-none placeholder-zinc-350 pb-2 text-left focus:ring-0">{{ old('billing_address', $user->address) }}</textarea>
 </div>
 </div>
 </div>

 <!-- Disclaimer & Submit -->
 <div class="space-y-6 pt-2">
 <p class="text-xs text-zinc-400 leading-relaxed font-medium">
 Your X-Buy Wallet is managed by Adyen, N.V., a licensed payment service provider (PSP). By activating your X-Buy Wallet, you accept the PSP's <a href="#" class="text-zinc-600 font-semibold underline">terms</a> and acknowledge that your data will be transferred to the PSP. You may also need to complete the PSP's "Know Your Customer" identity verification.
 </p>
  <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
 <a href="#" class="text-xs text-zinc-500 hover:text-zinc-700 font-semibold underline">For more information, visit our Help Center.</a>
 <button type="submit" class="px-8 py-3.5 bg-[#e6c019] text-black text-sm font-bold rounded-sm uppercase tracking-wider block text-center">
 Activate Wallet
 </button>
 </div>
 </div>

 </form>
 </div>
 </div>
 </div>
</div>
@endsection
