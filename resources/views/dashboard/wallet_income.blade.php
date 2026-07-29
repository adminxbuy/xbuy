@extends('layouts.app')

@section('title', 'Earnings')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
  <!-- Left Sidebar Navigation -->
 <div class="w-full md:w-1/4 shrink-0 space-y-4">
 <div class="pb-3 border-b border-zinc-200">
 <h2 class="text-xl font-bold text-zinc-900 tracking-tight">My Wallet</h2>
 </div>
 <nav class="flex flex-col gap-0.5">
 <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider px-2 pt-1 pb-0.5">Overview</div>
 <a href="/dashboard/wallet" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Wallet
 </a>
 @if(!$wallet->is_activated)
 <a href="/dashboard/wallet/setup" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Setup
 </a>
 @endif
 <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider px-2 pt-3 pb-0.5">Transactions</div>
 <a href="/dashboard/wallet/history" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Payment history
 </a>
 <a href="/dashboard/wallet/invoices" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Invoices
 </a>
 <a href="/dashboard/wallet/income" class="px-2 py-2 rounded-sm text-sm font-bold text-zinc-900 bg-zinc-100">
 Income
 </a>
 </nav>
 </div>

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 <!-- Earnings Card -->
 <div class="bg-white border border-zinc-200 rounded-sm p-6 space-y-4">
 <h3 class="text-xs font-bold text-zinc-450 uppercase tracking-wider">Earnings</h3>

 <div class="divide-y divide-zinc-150">
 @foreach($months as $m)
 <div class="py-4 flex items-center justify-between gap-4 first:pt-0 last:pb-0">
 <span class="text-sm font-bold text-zinc-800">
 {{ $m['name'] }} Income Report
 </span>
 <a href="{{ route('dashboard.wallet.income.download', ['year' => $m['year'], 'month' => $m['month']]) }}"
 class="px-4 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm inline-block">
 Download
 </a>
 </div>
 @endforeach
 </div>
 </div>

 </div>
 </div>
 </div>
</div>
@endsection
