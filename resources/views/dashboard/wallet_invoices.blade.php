@extends('layouts.app')

@section('title', 'Invoices')

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
                    <a href="/dashboard/wallet/invoices" class="px-2 py-2 rounded-sm text-sm font-bold text-zinc-900 bg-zinc-100">
                        Invoices
                    </a>
                    <a href="/dashboard/wallet/income" class="px-2 py-2 rounded-sm text-sm font-medium text-zinc-700">
                        Income
                    </a>
                </nav>
            </div>

            <!-- Right Content Area -->
            <div class="flex-1 max-w-3xl space-y-6">
                @if($orders->count() === 0)
                    <!-- Empty State (matching Vinted exactly) -->
                    <div class="flex flex-col items-center justify-center text-center py-16 px-4 space-y-4">
                        <div class="w-20 h-20 bg-zinc-100 flex items-center justify-center rounded-full text-zinc-400">
                            <i data-lucide="file-text" class="w-10 h-10"></i>
                        </div>
                        <div class="space-y-1.5">
                            <h3 class="text-lg font-bold text-zinc-800">You have no invoices yet</h3>
                            <p class="text-sm text-zinc-400 leading-normal max-w-xs">
                                Your invoices are generated daily. Please check back later.
                            </p>
                        </div>
                    </div>
                @else
                    <!-- Invoices List -->
                    <div class="space-y-3">
                        <h3 class="text-lg font-bold text-zinc-850">My Invoices</h3>
                        <div class="bg-white border border-zinc-200 rounded-sm overflow-hidden divide-y divide-zinc-200">
                            @foreach($orders as $order)
                                <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-bold text-zinc-800">
                                                Invoice #INV-{{ $order->order_number }}
                                            </span>
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-zinc-100 text-zinc-600">
                                                {{ $order->status }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-zinc-400 font-medium">
                                            Dated: {{ $order->created_at->format('d M Y') }} &bull; Item: {{ $order->listing->title ?? 'Product' }}
                                        </p>
                                        <p class="text-sm font-bold text-zinc-900">
                                            Total Paid: ₹{{ number_format($order->total_amount, 2) }}
                                        </p>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2">
                                        <!-- View -->
                                        <a href="{{ route('dashboard.wallet.invoices.view', $order->id) }}" target="_blank"
                                           class="px-3.5 py-2 border border-zinc-200 text-zinc-700 text-xs font-bold rounded-sm inline-flex items-center gap-1.5">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                                        </a>

                                        <!-- Download -->
                                        <a href="{{ route('dashboard.wallet.invoices.download', $order->id) }}"
                                           class="px-3.5 py-2 bg-[#e6c019] text-black text-xs font-bold rounded-sm inline-flex items-center gap-1.5">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i> Download
                                        </a>

                                        <!-- Help -->
                                        <a href="/admin/tickets" 
                                           class="px-3.5 py-2 border border-rose-100 text-rose-600 text-xs font-bold rounded-sm inline-flex items-center gap-1.5">
                                            <i data-lucide="help-circle" class="w-3.5 h-3.5"></i> Help
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
