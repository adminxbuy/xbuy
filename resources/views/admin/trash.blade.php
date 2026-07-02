@extends('layouts.admin')

@php
    $displayNames = [
        'articles' => 'Blog Articles',
        'sellers' => 'Seller Profiles',
        'listings' => 'Product Listings',
        'orders' => 'Orders',
        'disputes' => 'Disputes',
        'escrow' => 'Escrow Holds',
        'payouts' => 'Payouts',
        'tickets' => 'Support Tickets',
        'subscribers' => 'Mail Subscribers',
        'ratings' => 'Ratings & Reviews',
        'fraud-flags' => 'Fraud Flags',
        'alerts' => 'Admin Alerts',
        'pages' => 'Custom Pages',
        'page-categories' => 'Page Categories',
        'content' => 'Uploaded Assets'
    ];
    $title = ($displayNames[$modelName] ?? ucfirst($modelName)) . ' Trash Bin';
@endphp

@section('title', $title)
@section('page_title', $title)

@section('header_actions')
    @php
        $backRoutes = [
            'articles' => 'admin.articles.index',
            'sellers' => 'admin.sellers',
            'listings' => 'admin.listings',
            'orders' => 'admin.orders',
            'disputes' => 'admin.disputes',
            'escrow' => 'admin.escrow',
            'payouts' => 'admin.payouts',
            'tickets' => 'admin.tickets',
            'subscribers' => 'admin.mails.index',
            'ratings' => 'admin.ratings',
            'fraud-flags' => 'admin.fraud-flags.index',
            'alerts' => 'admin.alerts',
            'pages' => 'admin.pages.index',
            'page-categories' => 'admin.pages.index',
            'content' => 'admin.content'
        ];
        $backRoute = $backRoutes[$modelName] ?? 'admin.dashboard';
    @endphp
    <a href="{{ route($backRoute) }}" class="flex items-center space-x-1.5 px-4 py-2 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 hover:text-zinc-900 rounded-xl text-xs font-semibold transition-all border border-zinc-200 shadow-sm">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to {{ $displayNames[$modelName] ?? 'List' }}</span>
    </a>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center space-x-2 shadow-sm">
            <i data-lucide="check-circle" class="w-4.5 h-4.5 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center space-x-2 shadow-sm">
            <i data-lucide="alert-octagon" class="w-4.5 h-4.5 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Search Bar -->
    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <form action="{{ route('admin.trash.index', $modelName) }}" method="GET" class="w-full sm:w-80 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in trash..."
                class="w-full pl-9 pr-4 py-2.5 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none text-xs transition-all font-semibold text-zinc-700">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
        </form>
        @if(request('search'))
            <a href="{{ route('admin.trash.index', $modelName) }}" class="text-xs font-bold text-zinc-500 hover:text-black transition-all">Clear Search</a>
        @endif
    </div>

    <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
        @if($modelName === 'content')
            <!-- Special View for Content Files -->
            @php
                $totalFiles = count($trashedContent['images']) + count($trashedContent['pdfs']) + count($trashedContent['videos']);
            @endphp

            @if($totalFiles === 0)
                <div class="text-center py-20 text-zinc-400">
                    <i data-lucide="trash-2" class="w-12 h-12 mx-auto stroke-1.5 mb-3 text-zinc-300"></i>
                    <p class="text-sm font-semibold">No assets found in trash</p>
                    <p class="text-xs text-zinc-450 mt-1">Deleted items will automatically purge after 30 days.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-200 uppercase font-bold text-zinc-500 text-[10px] tracking-wider">
                                <th class="px-5 py-4">Original Name</th>
                                <th class="px-5 py-4">Type</th>
                                <th class="px-5 py-4 text-right">Size</th>
                                <th class="px-5 py-4">Deleted At</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach(['images', 'pdfs', 'videos'] as $fType)
                                @foreach($trashedContent[$fType] as $tFile)
                                    <tr class="hover:bg-zinc-50/50 transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-zinc-800 truncate max-w-[300px]" title="{{ $tFile['original_name'] }}">{{ $tFile['original_name'] }}</td>
                                        <td class="px-5 py-3.5 capitalize text-zinc-550">{{ $fType }}</td>
                                        <td class="px-5 py-3.5 text-right text-zinc-650 font-semibold">{{ $tFile['size'] }}</td>
                                        <td class="px-5 py-3.5 text-zinc-450 font-medium">{{ date('d M Y, H:i', strtotime($tFile['deleted_at'])) }}</td>
                                        <td class="px-5 py-3.5 text-right flex justify-end space-x-2">
                                            <form action="{{ route('admin.content.restore') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="path" value="{{ $tFile['url'] }}">
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-250 hover:bg-emerald-100 rounded-xl font-bold text-[10px] uppercase flex items-center space-x-1 transition-all">
                                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                    <span>Restore</span>
                                                </button>
                                            </form>
                                            @if(auth()->user()->isSuperAdmin())
                                                <form action="{{ route('admin.content.force-delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this file? This action is irreversible!')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="path" value="{{ $tFile['url'] }}">
                                                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 border border-red-250 hover:bg-red-100 rounded-xl font-bold text-[10px] uppercase flex items-center space-x-1 transition-all">
                                                        <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                                        <span>Delete Forever</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        @else
            <!-- General View for Database Models -->
            @if($trashedItems->isEmpty())
                <div class="text-center py-20 text-zinc-400">
                    <i data-lucide="trash-2" class="w-12 h-12 mx-auto stroke-1.5 mb-3 text-zinc-300"></i>
                    <p class="text-sm font-semibold">No items found in trash</p>
                    <p class="text-xs text-zinc-450 mt-1">Deleted items will automatically purge after 30 days.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-zinc-50 border-b border-zinc-200 uppercase font-bold text-zinc-500 text-[10px] tracking-wider">
                                <th class="px-5 py-4">Item Details</th>
                                <th class="px-5 py-4">Context / Secondary</th>
                                <th class="px-5 py-4">Deleted At</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($trashedItems as $item)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-5 py-3.5">
                                        @if($modelName === 'articles')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->title }}</div>
                                        @elseif($modelName === 'sellers')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->shop_name }}</div>
                                            <div class="text-[10px] text-zinc-450">{{ $item->user->name ?? 'N/A' }}</div>
                                        @elseif($modelName === 'listings')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->title }}</div>
                                            <div class="text-[10px] text-zinc-450">Grade: {{ $item->grade }}</div>
                                        @elseif($modelName === 'orders')
                                            <div class="font-bold text-zinc-800 text-sm">#{{ $item->order_number }}</div>
                                        @elseif($modelName === 'disputes')
                                            <div class="font-bold text-zinc-800 text-sm">Dispute #{{ $item->id }}</div>
                                            <div class="text-[10px] text-zinc-450">Order #{{ $item->order->order_number ?? 'N/A' }}</div>
                                        @elseif($modelName === 'escrow' || $modelName === 'payouts')
                                            <div class="font-bold text-zinc-800 text-sm">Escrow #{{ $item->id }}</div>
                                            <div class="text-[10px] text-zinc-450">Order #{{ $item->order->order_number ?? 'N/A' }}</div>
                                        @elseif($modelName === 'tickets')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->subject }}</div>
                                            <div class="text-[10px] text-zinc-450">Ticket #{{ $item->id }}</div>
                                        @elseif($modelName === 'subscribers')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->email }}</div>
                                        @elseif($modelName === 'ratings')
                                            <div class="font-bold text-zinc-800 text-sm">Rating #{{ $item->id }} ({{ $item->rating_value ?? $item->weighted_total }} Stars)</div>
                                        @elseif($modelName === 'fraud-flags')
                                            <div class="font-bold text-zinc-800 text-sm">{{ ucfirst(str_replace('_', ' ', $item->flag_type)) }}</div>
                                        @elseif($modelName === 'alerts')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->title }}</div>
                                        @elseif($modelName === 'pages')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->title }}</div>
                                        @elseif($modelName === 'page-categories')
                                            <div class="font-bold text-zinc-800 text-sm">{{ $item->name }}</div>
                                        @else
                                            <div class="font-bold text-zinc-800 text-sm">ID: {{ $item->id }}</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-5 py-3.5">
                                        @if($modelName === 'articles')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-700 border border-zinc-200">
                                                {{ $item->category ?: 'General' }}
                                            </span>
                                        @elseif($modelName === 'sellers')
                                            <div class="text-zinc-600 font-medium">{{ $item->user->email ?? 'N/A' }}</div>
                                        @elseif($modelName === 'listings')
                                            <div class="text-zinc-800 font-bold">₹{{ number_format($item->price, 2) }}</div>
                                        @elseif($modelName === 'orders')
                                            <div class="text-zinc-850 font-bold">₹{{ number_format($item->total_amount, 2) }}</div>
                                            <div class="text-[10px] text-zinc-400">{{ $item->buyer->name ?? 'N/A' }}</div>
                                        @elseif($modelName === 'disputes')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-100 text-zinc-700">Status: {{ $item->status }}</span>
                                        @elseif($modelName === 'escrow' || $modelName === 'payouts')
                                            <div class="text-zinc-850 font-bold">₹{{ number_format($item->amount_held, 2) }}</div>
                                            <div class="text-[10px] text-zinc-400">Seller Payout: ₹{{ number_format($item->seller_amount, 2) }}</div>
                                        @elseif($modelName === 'tickets')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-zinc-50 text-zinc-800 border border-zinc-200">{{ $item->status }}</span>
                                        @elseif($modelName === 'subscribers')
                                            <span class="text-zinc-400 font-medium">Joined: {{ $item->created_at->format('d M Y') }}</span>
                                        @elseif($modelName === 'ratings')
                                            <div class="text-zinc-500 italic max-w-xs truncate" title="{{ $item->comment }}">{{ $item->comment ?: '(No Comment)' }}</div>
                                        @elseif($modelName === 'fraud-flags')
                                            <div class="text-zinc-500 font-medium">Flagged User: {{ $item->flaggedUser->name ?? 'N/A' }}</div>
                                        @elseif($modelName === 'alerts')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">Severity: {{ $item->severity }}</span>
                                        @elseif($modelName === 'pages')
                                            <div class="text-zinc-500 font-medium">Slug: /v1/pages/{{ $item->slug }}</div>
                                        @elseif($modelName === 'page-categories')
                                            <div class="text-zinc-500 font-medium">Slug: {{ $item->slug }}</div>
                                        @else
                                            <span class="text-zinc-400">N/A</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-3.5 text-zinc-450 font-medium">
                                        {{ $item->deleted_at ? $item->deleted_at->format('d M Y, H:i') : 'N/A' }}
                                    </td>

                                    <td class="px-5 py-3.5 text-right flex justify-end space-x-2">
                                        <!-- Restore Action -->
                                        @php
                                            $restoreRoutes = [
                                                'pages' => 'admin.pages.restore',
                                                'page-categories' => 'admin.pages.categories.restore'
                                            ];
                                            $restoreRoute = $restoreRoutes[$modelName] ?? 'admin.trash.restore';
                                        @endphp
                                        <form action="{{ $restoreRoute === 'admin.trash.restore' ? route($restoreRoute, ['model' => $modelName, 'id' => $item->id]) : route($restoreRoute, $item->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-250 hover:bg-emerald-100 rounded-xl font-bold text-[10px] uppercase flex items-center space-x-1 transition-all">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>

                                        <!-- Permanent Delete Action -->
                                        @php
                                            $forceRoutes = [
                                                'pages' => 'admin.pages.force-delete',
                                                'page-categories' => 'admin.pages.categories.force-delete'
                                            ];
                                            $forceRoute = $forceRoutes[$modelName] ?? 'admin.trash.force';
                                        @endphp
                                        @if(auth()->user()->isSuperAdmin())
                                            <form action="{{ $forceRoute === 'admin.trash.force' ? route($forceRoute, ['model' => $modelName, 'id' => $item->id]) : route($forceRoute, $item->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this item? This action is irreversible!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-700 border border-red-250 hover:bg-red-100 rounded-xl font-bold text-[10px] uppercase flex items-center space-x-1 transition-all">
                                                    <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                                    <span>Delete Forever</span>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($trashedItems->hasPages())
                    <div class="px-5 py-4 border-t border-zinc-200 bg-zinc-50/50">
                        {{ $trashedItems->links() }}
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
@endsection
