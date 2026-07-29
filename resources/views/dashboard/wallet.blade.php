@extends('layouts.app')

@section('title', 'My Wallet')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10" x-data="{ showFundModal: false, fundAction: 'add', fundAmount: '' }">
 <div class="max-w-7xl mx-auto px-5">
 <div class="flex flex-col md:flex-row gap-10">
  <!-- Left Sidebar Navigation -->
 <div class="w-full md:w-1/4 shrink-0 space-y-4">
 <div class="pb-3 border-b border-zinc-200">
 <h2 class="text-xl font-bold text-zinc-900 tracking-tight">My Wallet</h2>
 </div>
 <nav class="flex flex-col gap-0.5">
 <div class="text-[11px] font-bold text-zinc-400 uppercase tracking-wider px-2 pt-1 pb-0.5">Overview</div>
 <a href="/dashboard/wallet" class="px-2 py-2 rounded-sm text-sm font-bold text-zinc-900 bg-zinc-100">
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
 <a href="/dashboard/wallet/income" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
 Income
 </a>
 </nav>
 </div>

 <!-- Right Content Area -->
 <div class="flex-1 space-y-6">
 @if(session('success'))
 <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-sm text-sm">
 {{ session('success') }}
 </div>
 @endif

 @if($errors->any())
 <div class="p-4 bg-rose-50 text-rose-800 border border-rose-200 rounded-sm text-sm">
 <ul class="list-disc pl-5 space-y-1">
 @foreach($errors->all() as $error)
 <li>{{ $error }}</li>
 @endforeach
 </ul>
 </div>
 @endif

 <!-- Wallet Main Card -->
 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
 <!-- Pending Balance Row -->
 <div class="flex items-center justify-between p-6">
 <span class="text-zinc-550 text-sm font-medium">Pending balance</span>
 <div class="flex items-center gap-1.5 text-zinc-800">
 <span class="font-bold text-base">₹0.00</span>
 <i data-lucide="info" class="w-4 h-4 text-zinc-400 cursor-pointer" title="Pending balance contains funds from ongoing sales that are not yet cleared."></i>
 </div>
 </div>

 <!-- Available Balance Row -->
 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-6 gap-4">
 <div class="space-y-1">
 <div class="text-3xl font-bold text-zinc-900">
 ₹{{ number_format($wallet->balance, 2) }}
 </div>
 <span class="text-xs text-zinc-400 font-semibold uppercase tracking-wider block">Available balance</span>
 </div>
 <div class="flex items-center gap-3">
 @if(!$wallet->is_activated)
 <a href="/dashboard/wallet/setup" class="px-6 py-2.5 bg-[#e6c019] hover:bg-[#d4b017] text-black text-sm font-bold rounded-sm transition-colors cursor-pointer inline-block text-center">
 Activate Wallet
 </a>
 @else
 <button type="button" @click="fundAction = 'add'; showFundModal = true" class="px-5 py-2.5 bg-[#e6c019] hover:bg-[#d4b017] text-black text-sm font-bold rounded-sm transition-colors cursor-pointer">
 Add Money
 </button>
 <button type="button" @click="fundAction = 'withdraw'; showFundModal = true" class="px-5 py-2.5 border border-zinc-300 text-zinc-700 hover:bg-zinc-50 text-sm font-bold rounded-sm transition-colors cursor-pointer">
 Withdraw
 </button>
 @endif
 </div>
 </div>
 </div>

 <!-- Transaction History Table -->
 @if($transactions->count() > 0)
 <div class="space-y-3 pt-4">
 <h3 class="text-lg font-bold text-zinc-800">Recent Transactions</h3>
 <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden">
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead>
 <tr class="bg-zinc-50 text-xs font-bold text-zinc-450 uppercase border-b border-zinc-200">
 <th class="p-4">Date</th>
 <th class="p-4">Type</th>
 <th class="p-4">Source / Description</th>
 <th class="p-4 text-right">Amount</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-zinc-150 text-sm">
 @foreach($transactions as $tx)
 <tr>
 <td class="p-4 text-zinc-500 font-medium text-xs">
 {{ $tx->created_at->format('d M Y, h:i A') }}
 </td>
 <td class="p-4">
 @if($tx->type === 'credit')
 <span class="inline-block px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full">
 Credit
 </span>
 @else
 <span class="inline-block px-2 py-0.5 bg-rose-50 text-rose-700 text-xs font-bold rounded-full">
 Debit
 </span>
 @endif
 </td>
 <td class="p-4 text-zinc-800 font-semibold">
 <span class="capitalize">{{ str_replace('_', ' ', $tx->source) }}</span>
 @if($tx->description)
 <span class="block text-xs text-zinc-400 font-normal mt-0.5">{{ $tx->description }}</span>
 @endif
 </td>
 <td class="p-4 text-right font-bold {{ $tx->type === 'credit' ? 'text-emerald-600' : 'text-zinc-800' }}">
 {{ $tx->type === 'credit' ? '+' : '-' }} ₹{{ number_format($tx->amount, 2) }}
 </td>
 </tr>
 @endforeach
 </tbody>
 </table>
 </div>
 </div>
 </div>
 @endif

 </div>
 </div>
 </div>

 <!-- Modal for adding/withdrawing funds -->
 <div x-show="showFundModal"  class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50"
 x-transition.opacity
 style="display: none;">
 <div class="bg-white rounded-sm border border-zinc-200 max-w-md w-full p-6 space-y-6" @click.away="showFundModal = false">
 <div class="flex justify-between items-center">
 <h3 class="text-lg font-bold text-zinc-900" x-text="fundAction === 'add' ? 'Add Funds' : 'Withdraw Funds'"></h3>
 <button type="button" @click="showFundModal = false" class="text-zinc-450 hover:text-zinc-650">
 <i data-lucide="x" class="w-5 h-5"></i>
 </button>
 </div>

 <form action="{{ route('dashboard.wallet.funds') }}" method="POST" class="space-y-5">
 @csrf
 <input type="hidden" name="action" :value="fundAction">

 <div class="space-y-2">
 <label class="block text-[11px] font-bold text-zinc-400 uppercase tracking-wider">Amount (₹)</label>
 <div class="flex items-center gap-2 border-b border-zinc-200 pb-2 focus-within:border-zinc-800">
 <span class="text-lg font-bold text-zinc-800">₹</span>
 <input type="number"  name="amount"  required  min="10"  max="100000"  placeholder="Enter amount (min ₹10)"  class="w-full text-base font-semibold bg-transparent border-none outline-none focus:ring-0 p-0 text-zinc-800">
 </div>
 </div>

 <div class="flex justify-end gap-3 pt-2">
 <button type="button" @click="showFundModal = false" class="px-4 py-2 border border-zinc-200 text-zinc-700 text-xs font-bold rounded-sm hover:bg-zinc-50 cursor-pointer">
 Cancel
 </button>
 <button type="submit" class="px-5 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm hover:bg-[#d4b017] cursor-pointer">
 Submit
 </button>
 </div>
 </form>
 </div>
 </div>
</div>
@endsection
