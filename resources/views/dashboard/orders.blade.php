@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="bg-zinc-50 min-h-[calc(100vh-120px)] py-10">
    <div class="max-w-7xl mx-auto px-5">
        <div class="flex flex-col md:flex-row gap-10">
            
            <!-- Left Sidebar Navigation (Matching screenshot/Vinted) -->
            <div class="w-full md:w-1/4 shrink-0 space-y-4">
                <div class="pb-3 border-b border-zinc-200">
                    <h2 class="text-xl font-bold text-zinc-900 tracking-tight">My orders</h2>
                </div>
                <nav class="flex flex-col gap-0.5">
                    <a href="{{ route('dashboard.orders', ['order_type' => 'sold']) }}" 
                       class="px-2 py-2 rounded-sm text-sm {{ $type === 'sold' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700 hover:bg-zinc-50' }}">
                        Sold
                    </a>
                    <a href="{{ route('dashboard.orders', ['order_type' => 'bought']) }}" 
                       class="px-2 py-2 rounded-sm text-sm {{ $type === 'bought' ? 'font-bold text-zinc-900 bg-zinc-100' : 'font-medium text-zinc-700 hover:bg-zinc-50' }}">
                        Bought
                    </a>
                </nav>
            </div>

            <!-- Right Content Area -->
            <div class="flex-1 space-y-6">
                <!-- Status Tabs Filters -->
                <div class="bg-white border border-zinc-200 rounded-sm p-4 flex flex-wrap gap-2 items-center">
                    <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'all']) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors cursor-pointer border {{ $status === 'all' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300' }}">
                        All
                    </a>
                    <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'in_progress']) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors cursor-pointer border {{ $status === 'in_progress' ? 'bg-[#e6c019] text-black border-[#e6c019]' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300' }}">
                        In progress
                    </a>
                    <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'completed']) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors cursor-pointer border {{ $status === 'completed' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300' }}">
                        Completed
                    </a>
                    <a href="{{ route('dashboard.orders', ['order_type' => $type, 'status' => 'cancelled']) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-bold transition-colors cursor-pointer border {{ $status === 'cancelled' ? 'bg-zinc-900 text-white border-zinc-900' : 'bg-white text-zinc-700 border-zinc-200 hover:border-zinc-300' }}">
                        Canceled
                    </a>
                </div>

                <!-- Orders List -->
                @if($orders->count() === 0)
                    <!-- Empty State (Matching Screenshot) -->
                    <div class="bg-white border border-zinc-200 rounded-sm p-16 flex flex-col items-center justify-center text-center space-y-4">
                        <div class="w-20 h-20 text-[#e6c019] flex items-center justify-center">
                            <svg class="w-16 h-16 stroke-[1.5]" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="space-y-1.5">
                            <h3 class="text-lg font-bold text-zinc-900">No orders yet</h3>
                            <p class="text-sm text-zinc-500 max-w-sm">
                                @if($type === 'sold')
                                    When you sell something, it'll be listed here
                                @else
                                    When you buy something, it'll be listed here
                                @endif
                            </p>
                        </div>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($orders as $order)
                            <div class="bg-white border border-zinc-200 rounded-sm p-5 flex flex-col sm:flex-row gap-5 items-start sm:items-center justify-between">
                                <!-- Order Info / Left side -->
                                <div class="flex gap-4 items-center">
                                    <!-- Thumbnail -->
                                    <div class="w-16 h-16 shrink-0 bg-zinc-100 rounded-sm overflow-hidden border border-zinc-200">
                                        @if($order->listing && $order->listing->primary_image_url)
                                            <img src="{{ $order->listing->primary_image_url }}" alt="{{ $order->listing->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-zinc-400">
                                                <i data-lucide="image" class="w-6 h-6"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center flex-wrap gap-2">
                                            <span class="text-xs font-semibold uppercase tracking-wider text-zinc-400">
                                                #{{ $order->order_number }}
                                            </span>
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" 
                                                  style="background-color: {{ $order->status_color }}15; color: {{ $order->status_color }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>
                                        <h4 class="font-bold text-sm text-zinc-900">
                                            {{ $order->listing->title ?? 'Deleted Product' }}
                                        </h4>
                                        <p class="text-xs text-zinc-500">
                                            @if($type === 'sold')
                                                Buyer: <span class="font-semibold">{{ $order->buyer->name ?? 'Unknown' }}</span>
                                            @else
                                                Seller: <span class="font-semibold">{{ $order->seller->user->name ?? 'Unknown' }}</span>
                                            @endif
                                            &bull; {{ $order->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions / Right side -->
                                <div class="flex flex-col sm:items-end gap-2 w-full sm:w-auto shrink-0 border-t sm:border-t-0 pt-3 sm:pt-0 border-zinc-100">
                                    <span class="text-lg font-black text-zinc-900 block sm:text-right">
                                        ₹{{ number_format($order->total_amount, 2) }}
                                    </span>
                                    <div class="flex items-center gap-2">
                                        @if($type === 'bought')
                                            <a href="{{ route('dashboard.wallet.invoices.view', $order->id) }}" target="_blank"
                                               class="px-3.5 py-2 border border-zinc-200 text-zinc-700 text-xs font-bold rounded-sm inline-flex items-center gap-1.5 hover:bg-zinc-55 hover:border-zinc-300 transition-colors">
                                                <i data-lucide="eye" class="w-3.5 h-3.5"></i> Invoice
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
