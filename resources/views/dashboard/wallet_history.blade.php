@extends('layouts.app')

@section('title', 'Payment History')

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
 <a href="/dashboard/wallet/history" class="px-2 py-2 rounded-sm text-sm font-bold text-zinc-900 bg-zinc-100">
 Payment history
 </a>
 <a href="/dashboard/wallet/invoices" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Invoices
 </a>
 <a href="/dashboard/wallet/income" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Income
 </a>
 </nav>
 </div>

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 <!-- Balance Box (Starting & Ending Balance) -->
 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
 <div class="flex items-center justify-between p-6">
 <span class="text-zinc-550 text-sm font-medium">Ending balance</span>
 <span class="font-bold text-zinc-800 text-base">₹{{ number_format($wallet->balance, 2) }}</span>
 </div>
 <div class="flex items-center justify-between p-6">
 <span class="text-zinc-550 text-sm font-medium">Starting balance</span>
 <span class="font-bold text-zinc-800 text-base">₹{{ number_format($startingBalance, 2) }}</span>
 </div>
 </div>

 <!-- Orders (Transaction Details) -->
 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden p-6 space-y-4">
 <h3 class="text-xs font-bold text-zinc-450 uppercase tracking-wider">Orders</h3>

 @if($transactions->count() === 0)
 <div class="py-8 text-center text-zinc-500 font-medium text-sm">
 0 transactions
 </div>
 @else
 <div class="divide-y divide-zinc-150">
 @foreach($transactions as $tx)
 <div class="py-4 flex items-center justify-between gap-4 first:pt-0 last:pb-0">
 <div class="space-y-0.5">
 <p class="text-sm font-bold text-zinc-800 capitalize">
 {{ str_replace('_', ' ', $tx->source) }}
 </p>
 <p class="text-xs text-zinc-400 font-medium">
 {{ $tx->created_at->format('d M Y, h:i A') }}
 </p>
 @if($tx->description)
 <p class="text-[11px] text-zinc-400 leading-normal">{{ $tx->description }}</p>
 @endif
 </div>
 <div class="text-right font-bold text-sm {{ $tx->type === 'credit' ? 'text-emerald-600' : 'text-zinc-800' }}">
 {{ $tx->type === 'credit' ? '+' : '-' }} ₹{{ number_format($tx->amount, 2) }}
 </div>
 </div>
 @endforeach
 </div>
 @endif
 </div>

 </div>
 </div>
 </div>
</div>
@endsection
