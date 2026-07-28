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
    <a href="{{ route($backRoute) }}" class="flex items-center space-x-1.5 px-4 py-2 bg-muted hover:bg-muted text-foreground hover:text-foreground rounded-lg text-xs font-semibold transition-all border border-border shadow-sm">
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
    <div class="bg-card border border-border rounded-xl p-4 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <form action="{{ route('admin.trash.index', $modelName) }}" method="GET" class="w-full sm:w-80 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search in trash..."
                class="w-full pl-9 pr-4 py-2.5 border border-border rounded-lg bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none text-xs transition-all font-semibold text-foreground">
            <div class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                <i data-lucide="search" class="w-4 h-4"></i>
            </div>
        </form>
        @if(request('search'))
            <a href="{{ route('admin.trash.index', $modelName) }}" class="text-xs font-bold text-muted-foreground hover:text-foreground transition-all">Clear Search</a>
        @endif
    </div>

    <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden">
        @if($modelName === 'content')
            <!-- Special View for Content Files -->
            @php
                $totalFiles = count($trashedContent['images']) + count($trashedContent['pdfs']) + count($trashedContent['videos']);
            @endphp

            @if($totalFiles === 0)
                <div class="text-center py-20 text-muted-foreground">
                    <i data-lucide="trash-2" class="w-12 h-12 mx-auto stroke-1.5 mb-3 text-muted-foreground"></i>
                    <p class="text-sm font-semibold">No assets found in trash</p>
                    <p class="text-xs text-muted-foreground mt-1">Deleted items will automatically purge after 30 days.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-muted border-b border-border uppercase font-bold text-muted-foreground text-[10px] tracking-wider">
                                <th class="px-5 py-4">Original Name</th>
                                <th class="px-5 py-4">Type</th>
                                <th class="px-5 py-4 text-right">Size</th>
                                <th class="px-5 py-4">Deleted At</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach(['images', 'pdfs', 'videos'] as $fType)
                                @foreach($trashedContent[$fType] as $tFile)
                                    <tr class="hover:bg-muted transition-colors">
                                        <td class="px-5 py-3.5 font-bold text-foreground truncate max-w-[300px]" title="{{ $tFile['original_name'] }}">{{ $tFile['original_name'] }}</td>
                                        <td class="px-5 py-3.5 capitalize text-muted-foreground">{{ $fType }}</td>
                                        <td class="px-5 py-3.5 text-right text-muted-foreground font-semibold">{{ $tFile['size'] }}</td>
                                        <td class="px-5 py-3.5 text-muted-foreground font-medium">{{ date('d M Y, H:i', strtotime($tFile['deleted_at'])) }}</td>
                                        <td class="px-5 py-3.5 text-right flex justify-end space-x-2">
                                            <form action="{{ route('admin.content.restore') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="path" value="{{ $tFile['url'] }}">
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-250 hover:bg-emerald-100 rounded-lg font-medium text-[10px] uppercase flex items-center space-x-1 transition-all">
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
                <div class="text-center py-20 text-muted-foreground">
                    <i data-lucide="trash-2" class="w-12 h-12 mx-auto stroke-1.5 mb-3 text-muted-foreground"></i>
                    <p class="text-sm font-semibold">No items found in trash</p>
                    <p class="text-xs text-muted-foreground mt-1">Deleted items will automatically purge after 30 days.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-muted border-b border-border uppercase font-bold text-muted-foreground text-[10px] tracking-wider">
                                <th class="px-5 py-4">Item Details</th>
                                <th class="px-5 py-4">Context / Secondary</th>
                                <th class="px-5 py-4">Deleted At</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach($trashedItems as $item)
                                <tr class="hover:bg-muted transition-colors">
                                    <td class="px-5 py-3.5">
                                        @if($modelName === 'articles')
                                            <div class="font-bold text-foreground text-sm">{{ $item->title }}</div>
                                        @elseif($modelName === 'sellers')
                                            <div class="font-bold text-foreground text-sm">{{ $item->shop_name }}</div>
                                            <div class="text-[10px] text-muted-foreground">{{ $item->user->name ?? 'N/A' }}</div>
                                        @elseif($modelName === 'listings')
                                            <div class="font-bold text-foreground text-sm">{{ $item->title }}</div>
                                            <div class="text-[10px] text-muted-foreground">Grade: {{ $item->grade }}</div>
                                        @elseif($modelName === 'orders')
                                            <div class="font-bold text-foreground text-sm">#{{ $item->order_number }}</div>
                                        @elseif($modelName === 'disputes')
                                            <div class="font-bold text-foreground text-sm">Dispute #{{ $item->id }}</div>
                                            <div class="text-[10px] text-muted-foreground">Order #{{ $item->order->order_number ?? 'N/A' }}</div>
                                        @elseif($modelName === 'escrow' || $modelName === 'payouts')
                                            <div class="font-bold text-foreground text-sm">Escrow #{{ $item->id }}</div>
                                            <div class="text-[10px] text-muted-foreground">Order #{{ $item->order->order_number ?? 'N/A' }}</div>
                                        @elseif($modelName === 'tickets')
                                            <div class="font-bold text-foreground text-sm">{{ $item->subject }}</div>
                                            <div class="text-[10px] text-muted-foreground">Ticket #{{ $item->id }}</div>
                                        @elseif($modelName === 'subscribers')
                                            <div class="font-bold text-foreground text-sm">{{ $item->email }}</div>
                                        @elseif($modelName === 'ratings')
                                            <div class="font-bold text-foreground text-sm">Rating #{{ $item->id }} ({{ $item->rating_value ?? $item->weighted_total }} Stars)</div>
                                        @elseif($modelName === 'fraud-flags')
                                            <div class="font-bold text-foreground text-sm">{{ ucfirst(str_replace('_', ' ', $item->flag_type)) }}</div>
                                        @elseif($modelName === 'alerts')
                                            <div class="font-bold text-foreground text-sm">{{ $item->title }}</div>
                                        @elseif($modelName === 'pages')
                                            <div class="font-bold text-foreground text-sm">{{ $item->title }}</div>
                                        @elseif($modelName === 'page-categories')
                                            <div class="font-bold text-foreground text-sm">{{ $item->name }}</div>
                                        @else
                                            <div class="font-bold text-foreground text-sm">ID: {{ $item->id }}</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-5 py-3.5">
                                        @if($modelName === 'articles')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-muted text-foreground border border-border">
                                                {{ $item->category ?: 'General' }}
                                            </span>
                                        @elseif($modelName === 'sellers')
                                            <div class="text-muted-foreground font-medium">{{ $item->user->email ?? 'N/A' }}</div>
                                        @elseif($modelName === 'listings')
                                            <div class="text-foreground font-bold">₹{{ number_format($item->price, 2) }}</div>
                                        @elseif($modelName === 'orders')
                                            <div class="text-foreground font-bold">₹{{ number_format($item->total_amount, 2) }}</div>
                                            <div class="text-[10px] text-muted-foreground">{{ $item->buyer->name ?? 'N/A' }}</div>
                                        @elseif($modelName === 'disputes')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-muted text-foreground">Status: {{ $item->status }}</span>
                                        @elseif($modelName === 'escrow' || $modelName === 'payouts')
                                            <div class="text-foreground font-bold">₹{{ number_format($item->amount_held, 2) }}</div>
                                            <div class="text-[10px] text-muted-foreground">Seller Payout: ₹{{ number_format($item->seller_amount, 2) }}</div>
                                        @elseif($modelName === 'tickets')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-muted text-foreground border border-border">{{ $item->status }}</span>
                                        @elseif($modelName === 'subscribers')
                                            <span class="text-muted-foreground font-medium">Joined: {{ $item->created_at->format('d M Y') }}</span>
                                        @elseif($modelName === 'ratings')
                                            <div class="text-muted-foreground italic max-w-xs truncate" title="{{ $item->comment }}">{{ $item->comment ?: '(No Comment)' }}</div>
                                        @elseif($modelName === 'fraud-flags')
                                            <div class="text-muted-foreground font-medium">Flagged User: {{ $item->flaggedUser->name ?? 'N/A' }}</div>
                                        @elseif($modelName === 'alerts')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">Severity: {{ $item->severity }}</span>
                                        @elseif($modelName === 'pages')
                                            <div class="text-muted-foreground font-medium">Slug: /v1/pages/{{ $item->slug }}</div>
                                        @elseif($modelName === 'page-categories')
                                            <div class="text-muted-foreground font-medium">Slug: {{ $item->slug }}</div>
                                        @else
                                            <span class="text-muted-foreground">N/A</span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-3.5 text-muted-foreground font-medium">
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
                                            <button type="submit" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 border border-emerald-250 hover:bg-emerald-100 rounded-lg font-medium text-[10px] uppercase flex items-center space-x-1 transition-all">
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
                    <div class="px-5 py-4 border-t border-border bg-muted">
                        {{ $trashedItems->links() }}
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
@endsection
