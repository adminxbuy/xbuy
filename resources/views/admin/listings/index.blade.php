@extends('layouts.admin')

@section('title', 'Listings')
@section('page_title', '')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'listings') }}"
       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-border bg-card hover:bg-muted text-foreground text-xs font-semibold transition-all">
        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
        Trash
        <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $trashedListings->count() }}</span>
    </a>
@endsection

@section('content')

{{-- ══ Alpine State ════════════════════════════════════════════════════════ --}}
<div x-data="{
    drawerOpen: false,
    listing: null,
    rejecting: false,
    rejectReason: '',
    selectedIds: [],
    selectAll: false,
    bulkAction: '',
    menuOpen: null,
    open(data) {
        this.listing = data;
        this.rejecting = false;
        this.rejectReason = '';
        this.drawerOpen = true;
        this.menuOpen = null;
    },
    close() { this.drawerOpen = false; }
}" @keydown.escape.window="close()" @click="menuOpen = null">

{{-- ══ Page Title ═══════════════════════════════════════════════════════════ --}}
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-foreground tracking-tight">Products</h1>
        <p class="text-xs text-muted-foreground mt-0.5">All seller product listings</p>
    </div>
    <a href="{{ route('admin.trash.index', 'listings') }}"
       class="inline-flex items-center gap-2 px-4 py-2 border border-border rounded-lg bg-card hover:bg-red-50 hover:border-red-200 text-muted-foreground hover:text-red-600 text-xs font-semibold transition-all">
        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
        Trash
        @if($trashedListings->count())
            <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $trashedListings->count() }}</span>
        @endif
    </a>
</div>

{{-- ══ Stats Row ════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @php
        $statCards = [
            ['label'=>'Total Listings',    'value'=>$tabCounts['all'],              'change'=>'+12.4%', 'up'=>true],
            ['label'=>'Pending Approval',  'value'=>$tabCounts['pending_approval'], 'change'=>$tabCounts['pending_approval'].' new', 'up'=>true],
            ['label'=>'Active Listings',   'value'=>$tabCounts['active'],           'change'=>'+5.02',  'up'=>true],
            ['label'=>'Rejected / Sold',   'value'=>$tabCounts['rejected']+$tabCounts['sold'], 'change'=>'-3.58%', 'up'=>false],
        ];
    @endphp
    @foreach($statCards as $c)
    <div class="bg-card border border-border rounded-xl p-4">
        <div class="flex items-center justify-between mb-2">
            <p class="text-xs text-muted-foreground font-medium">{{ $c['label'] }}</p>
            <span class="text-[10px] font-bold {{ $c['up'] ? 'text-emerald-600' : 'text-red-500' }}">{{ $c['change'] }}</span>
        </div>
        <p class="text-2xl font-bold text-foreground tracking-tight">{{ number_format($c['value']) }}</p>
    </div>
    @endforeach
</div>

{{-- ══ Main Card ════════════════════════════════════════════════════════════ --}}
<div class="bg-card border border-border rounded-xl overflow-hidden">

    {{-- ── Toolbar ──────────────────────────────────────────────────────── --}}
    <div class="p-4 border-b border-border">
        <div class="flex flex-wrap items-center gap-2">
            {{-- Search --}}
            <form action="{{ route('admin.listings') }}" method="GET" id="filterForm" class="contents">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                <div class="relative flex-1 min-w-[160px] max-w-xs">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search products…"
                           class="w-full pl-9 pr-3 py-2 text-xs border border-border rounded-lg bg-card focus:outline-none focus:ring-1 focus:ring-ring focus:border-border transition-all placeholder-muted-foreground">
                </div>

                {{-- Status --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click.stop="open = !open"
                            class="inline-flex items-center gap-1.5 px-3 py-2 border border-border rounded-lg text-xs font-medium text-foreground bg-card hover:bg-muted transition-all">
                        <i data-lucide="circle-plus" class="w-3.5 h-3.5 text-muted-foreground"></i>
                        Status
                        @if(request('status'))
                            <span class="bg-primary text-primary-foreground px-1.5 py-0.5 rounded text-[10px] font-bold">{{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
                        @endif
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         class="absolute left-0 top-full mt-1.5 w-44 bg-card border border-border rounded-xl shadow-lg z-20 py-1.5 overflow-hidden"
                         style="display:none;">
                        @php
                            $statusOptions = [
                                '' => 'All Status',
                                'pending_approval' => 'Pending Approval',
                                'active'           => 'Active',
                                'paused'           => 'Paused',
                                'rejected'         => 'Rejected',
                                'sold'             => 'Sold',
                            ];
                        @endphp
                        @foreach($statusOptions as $val => $label)
                            <a href="{{ route('admin.listings', array_merge(request()->except('status','page'), $val ? ['status'=>$val] : [])) }}"
                               class="flex items-center justify-between px-3 py-2 text-xs hover:bg-muted transition-colors {{ request('status', '') === $val ? 'font-semibold text-foreground' : 'text-muted-foreground' }}">
                                {{ $label }}
                                @if(request('status', '') === $val)
                                    <i data-lucide="check" class="w-3 h-3 text-foreground"></i>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- Category (placeholder filter) --}}
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click.stop="open = !open"
                            class="inline-flex items-center gap-1.5 px-3 py-2 border border-border rounded-lg text-xs font-medium text-foreground bg-card hover:bg-muted transition-all">
                        <i data-lucide="circle-plus" class="w-3.5 h-3.5 text-muted-foreground"></i>
                        Category
                    </button>
                </div>

                {{-- Date Range --}}
                <div class="relative" x-data="dateRangePicker({ start: '{{ request('date_start') }}', end: '{{ request('date_end') }}', startName: 'date_start', endName: 'date_end' })">
                    <input type="hidden" name="date_start" x-model="dateStart">
                    <input type="hidden" name="date_end" x-model="dateEnd">
                    <button type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-2 border border-border rounded-lg text-xs font-medium text-foreground bg-card hover:bg-muted transition-all">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-muted-foreground"></i>
                        <span x-text="currentPreset === 'all' ? 'All Time' : currentPreset"></span>
                        <i data-lucide="chevron-down" class="w-3 h-3 text-muted-foreground"></i>
                    </button>
                </div>

                @if(request()->anyFilled(['search','date_start','date_end','status']))
                    <a href="{{ route('admin.listings') }}"
                       class="inline-flex items-center gap-1 px-3 py-2 text-xs font-medium text-muted-foreground hover:text-foreground transition-colors">
                        <i data-lucide="x" class="w-3 h-3"></i> Reset
                    </a>
                @endif
            </form>

            <div class="flex-1"></div>

            {{-- Columns Toggle --}}
            <div class="relative" x-data="{ open: false }">
                <button @click.stop="open = !open"
                        class="inline-flex items-center gap-1.5 px-3 py-2 border border-border rounded-lg text-xs font-medium text-foreground bg-card hover:bg-muted transition-all ml-auto">
                    <i data-lucide="columns-3" class="w-3.5 h-3.5 text-muted-foreground"></i>
                    Columns
                    <i data-lucide="sliders-horizontal" class="w-3 h-3 text-muted-foreground"></i>
                </button>
                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 top-full mt-1.5 w-44 bg-card border border-border rounded-xl shadow-lg z-20 p-2"
                     style="display:none;">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wider px-2 pb-1.5">Toggle columns</p>
                    @foreach(['seller'=>'Seller','category'=>'Category','price'=>'Price','warranty'=>'Warranty','documents'=>'Documents','status'=>'Status'] as $col=>$label)
                    <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-muted cursor-pointer text-xs text-foreground select-none">
                        <input type="checkbox" class="w-3.5 h-3.5 rounded border-border text-foreground focus:ring-0"
                               x-bind:id="'col_{{ $col }}'" checked
                               @change="document.querySelectorAll('.col-{{ $col }}').forEach(el => el.classList.toggle('hidden'))">
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Bulk Bar ──────────────────────────────────────────────────────── --}}
    <div x-show="selectedIds.length > 0"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         class="px-4 py-2.5 bg-background border-b border-border flex items-center gap-4"
         style="display:none;">
        <span class="text-primary-foreground text-xs font-semibold"><span x-text="selectedIds.length"></span> selected</span>
        <form method="POST" action="{{ route('admin.listings.bulk-action') }}" class="flex items-center gap-2" x-ref="bulkForm">
            @csrf
            <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
            <select name="action" x-model="bulkAction"
                    class="text-xs bg-primary border border-border text-primary-foreground rounded-lg px-3 py-1.5 focus:outline-none">
                <option value="">Action…</option>
                <option value="status_active">Set Active</option>
                <option value="status_paused">Set Paused</option>
                <option value="status_rejected">Set Rejected</option>
                <option value="delete">Move to Trash</option>
            </select>
            <button type="button" :disabled="!bulkAction"
                    @click="if(bulkAction && confirm('Apply to '+selectedIds.length+' listings?')) $refs.bulkForm.submit()"
                    class="px-3 py-1.5 bg-card text-foreground text-xs font-bold rounded-lg hover:bg-muted transition-all disabled:opacity-40">
                Apply
            </button>
        </form>
        <button @click="selectedIds=[];selectAll=false" class="ml-auto text-muted-foreground hover:text-primary-foreground text-xs transition-colors">Deselect all</button>
    </div>

    {{-- ── Table ─────────────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-border">
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" x-model="selectAll"
                               @change="if(selectAll){selectedIds={{ collect($listings->items())->pluck('id')->toJson() }}}else{selectedIds=[]}"
                               class="w-4 h-4 rounded border-border text-foreground focus:ring-0 focus:ring-offset-0">
                    </th>
                    <th class="px-4 py-3 text-left">
                        <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors">
                            Product Name <i data-lucide="arrow-up-down" class="w-3 h-3 text-muted-foreground"></i>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left col-price">
                        <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors">
                            Price <i data-lucide="arrow-up-down" class="w-3 h-3 text-muted-foreground"></i>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left col-category">
                        <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors">
                            Category <i data-lucide="arrow-up-down" class="w-3 h-3 text-muted-foreground"></i>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left col-warranty">
                        <span class="text-xs font-semibold text-muted-foreground">Warranty</span>
                    </th>
                    <th class="px-4 py-3 text-left col-documents">
                        <span class="text-xs font-semibold text-muted-foreground">Documents</span>
                    </th>
                    <th class="px-4 py-3 text-left col-seller">
                        <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors">
                            Seller <i data-lucide="arrow-up-down" class="w-3 h-3 text-muted-foreground"></i>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left col-status">
                        <button class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors">
                            Status <i data-lucide="arrow-up-down" class="w-3 h-3 text-muted-foreground"></i>
                        </button>
                    </th>
                    <th class="px-4 py-3 w-10"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($listings as $listing)
                @php
                    $imgUrl = $listing->primary_image_url;
                    $images = $listing->images->map(fn($i) => ['url' => $i->image_url, 'is_primary' => $i->is_primary])->values()->toArray();
                    $dp = [
                        'id'                           => $listing->id,
                        'listing_number'               => $listing->listing_number,
                        'title'                        => $listing->title,
                        'serial_number'                => $listing->serial_number,
                        'category'                     => $listing->category,
                        'grade'                        => $listing->grade,
                        'brand'                        => $listing->brand,
                        'model_name'                   => $listing->model_name,
                        'price'                        => number_format($listing->price, 2),
                        'original_price'               => $listing->original_price ? number_format($listing->original_price, 2) : null,
                        'description'                  => $listing->description,
                        'condition_notes'              => $listing->condition_notes,
                        'manufacturer_warranty_status' => $listing->manufacturer_warranty_status,
                        'manufacturer_warranty_months' => $listing->manufacturer_warranty_months,
                        'seller_warranty_months'       => $listing->seller_warranty_months,
                        'document_status'              => $listing->document_status,
                        'listing_status'               => $listing->listing_status,
                        'rejection_reason'             => $listing->rejection_reason,
                        'shipping_type'                => $listing->shipping_type,
                        'shipping_charges'             => number_format($listing->shipping_charges, 2),
                        'pickup_city'                  => $listing->pickup_city,
                        'pickup_state'                 => $listing->pickup_state,
                        'trusted_buyers_only'          => (bool) $listing->trusted_buyers_only,
                        'created_at'                   => $listing->created_at->format('d M Y'),
                        'shop_name'                    => $listing->seller->shop_name,
                        'seller_email'                 => $listing->seller->user->email ?? '',
                        'seller_id'                    => $listing->seller_id,
                        'seller_badge'                 => $listing->seller->badge_level ?? 'basic',
                        'images'                       => $images,
                        'approve_url'                  => route('admin.listings.approve', $listing->id),
                        'pause_url'                    => route('admin.listings.pause', $listing->id),
                        'seller_url'                   => route('admin.sellers.show', $listing->seller_id),
                        'is_previously_sold'           => (bool) $listing->is_previously_sold,
                        'csrf'                         => csrf_token(),
                    ];

                    $statusCfg = match($listing->listing_status) {
                        'active'           => ['dot' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'bg' => 'bg-emerald-50',   'border' => 'border-emerald-200', 'label' => 'Active'],
                        'pending_approval' => ['dot' => 'bg-primary',  'text' => 'text-foreground',  'bg' => 'bg-muted',    'border' => 'border-border',  'label' => 'Pending'],
                        'paused'           => ['dot' => 'bg-blue-400',    'text' => 'text-blue-700',    'bg' => 'bg-blue-50',      'border' => 'border-blue-200',    'label' => 'Paused'],
                        'rejected'         => ['dot' => 'bg-red-500',     'text' => 'text-red-700',     'bg' => 'bg-red-50',       'border' => 'border-red-200',     'label' => 'Rejected'],
                        'sold'             => ['dot' => 'bg-violet-500',  'text' => 'text-violet-700',  'bg' => 'bg-violet-50',    'border' => 'border-violet-200',  'label' => 'Sold'],
                        default            => ['dot' => 'bg-muted-foreground/40', 'text' => 'text-muted-foreground', 'bg' => 'bg-muted', 'border' => 'border-border', 'label' => ucfirst($listing->listing_status)],
                    };
                @endphp
                <tr class="border-b border-border hover:bg-muted/60 transition-colors group">

                    {{-- Checkbox --}}
                    <td class="px-4 py-3" @click.stop>
                        <input type="checkbox" value="{{ $listing->id }}" x-model="selectedIds"
                               class="w-4 h-4 rounded border-border text-foreground focus:ring-0 focus:ring-offset-0">
                    </td>

                    {{-- Product Name --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" alt=""
                                     class="w-9 h-9 rounded-lg object-cover border border-border shrink-0 bg-muted">
                            @else
                                <div class="w-9 h-9 rounded-lg bg-muted border border-border flex items-center justify-center text-muted-foreground shrink-0">
                                    <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <button type="button" @click="open({{ json_encode($dp) }})"
                                        class="text-xs font-medium text-foreground hover:text-foreground text-left truncate max-w-[200px] block transition-colors">
                                    {{ $listing->title }}
                                </button>
                                @if($listing->listing_number)
                                    <span class="text-[10px] font-mono text-muted-foreground">{{ $listing->listing_number }}</span>
                                @endif
                                @if($listing->is_previously_sold)
                                    <span class="text-[9px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded ml-1">⚠ Prev. Sold</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Price --}}
                    <td class="px-4 py-3 col-price">
                        <span class="text-xs font-semibold text-foreground">₹{{ number_format($listing->price, 0) }}</span>
                    </td>

                    {{-- Category --}}
                    <td class="px-4 py-3 col-category">
                        <span class="text-xs text-foreground capitalize">{{ $listing->category }}</span>
                    </td>

                    {{-- Warranty --}}
                    <td class="px-4 py-3 col-warranty">
                        @php
                            [$wCls, $wLabel] = match($listing->manufacturer_warranty_status) {
                                'active'  => ['text-emerald-700 bg-emerald-50 border-emerald-200', 'Active'],
                                'expired' => ['text-muted-foreground bg-muted border-border',         'Expired'],
                                default   => ['text-red-600 bg-red-50 border-red-200',             'None'],
                            };
                        @endphp
                        <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-md border {{ $wCls }}">{{ $wLabel }}</span>
                    </td>

                    {{-- Documents --}}
                    <td class="px-4 py-3 col-documents">
                        @php
                            [$dCls, $dLabel] = match($listing->document_status) {
                                'full'    => ['text-emerald-700 bg-emerald-50 border-emerald-200', 'Full'],
                                'partial' => ['text-foreground bg-muted border-border',   'Partial'],
                                default   => ['text-red-600 bg-red-50 border-red-200',             'None'],
                            };
                        @endphp
                        <span class="inline-flex items-center text-[10px] font-semibold px-2 py-0.5 rounded-md border {{ $dCls }}">{{ $dLabel }}</span>
                    </td>

                    {{-- Seller --}}
                    <td class="px-4 py-3 col-seller">
                        <span class="text-xs text-muted-foreground">{{ $listing->seller->shop_name }}</span>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3 col-status">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full border
                                     {{ $statusCfg['text'] }} {{ $statusCfg['bg'] }} {{ $statusCfg['border'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusCfg['dot'] }} shrink-0"></span>
                            {{ $statusCfg['label'] }}
                        </span>
                    </td>

                    {{-- ··· Action Menu --}}
                    <td class="px-4 py-3" @click.stop>
                        <div class="relative flex justify-end" x-data="{ menuOpen: false }">
                            <button @click.stop="menuOpen = !menuOpen"
                                    class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-muted text-muted-foreground hover:text-foreground transition-all">
                                <i data-lucide="ellipsis" class="w-4 h-4"></i>
                            </button>

                            {{-- Dropdown --}}
                            <div x-show="menuOpen"
                                 @click.outside="menuOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 top-full mt-1 w-44 bg-card border border-border rounded-xl shadow-xl z-50 overflow-hidden"
                                 style="display:none;">

                                {{-- Header --}}
                                <div class="px-3 py-2 border-b border-border">
                                    <p class="text-xs font-semibold text-muted-foreground">Actions</p>
                                </div>

                                {{-- View details --}}
                                <button type="button"
                                        @click="open({{ json_encode($dp) }}); menuOpen = false"
                                        class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs text-foreground hover:bg-muted transition-colors text-left">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-muted-foreground shrink-0"></i>
                                    View details
                                </button>

                                {{-- Copy ID --}}
                                <button type="button"
                                        @click="navigator.clipboard.writeText('{{ $listing->listing_number ?? $listing->id }}'); menuOpen = false"
                                        class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs text-foreground hover:bg-muted transition-colors text-left">
                                    <i data-lucide="copy" class="w-3.5 h-3.5 text-muted-foreground shrink-0"></i>
                                    Copy ID
                                </button>

                                {{-- Status Actions --}}
                                @if($listing->listing_status === 'pending_approval')
                                <form action="{{ route('admin.listings.approve', $listing->id) }}" method="POST">
                                    @csrf <input type="hidden" name="status" value="active">
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs text-emerald-700 hover:bg-emerald-50 transition-colors text-left">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                        Approve
                                    </button>
                                </form>
                                @endif

                                @if($listing->listing_status === 'active')
                                <form action="{{ route('admin.listings.pause', $listing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs text-blue-700 hover:bg-blue-50 transition-colors text-left">
                                        <i data-lucide="pause-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                        Pause
                                    </button>
                                </form>
                                @endif

                                {{-- Separator --}}
                                <div class="h-px bg-muted mx-2 my-1"></div>

                                {{-- Delete --}}
                                <form action="{{ route('admin.listings.approve', $listing->id) }}" method="POST"
                                      onsubmit="return confirm('Move this listing to trash?')">
                                    @csrf <input type="hidden" name="status" value="rejected">
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-3 py-2.5 text-xs text-red-600 hover:bg-red-50 transition-colors text-left">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 shrink-0"></i>
                                        Delete
                                    </button>
                                </form>

                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-muted border border-border flex items-center justify-center text-muted-foreground">
                                <i data-lucide="package-open" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">No listings found</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Try adjusting your filters</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Footer / Pagination ───────────────────────────────────────────── --}}
    <div class="px-4 py-3 border-t border-border flex items-center justify-between">
        <p class="text-xs text-muted-foreground">
            <span x-text="selectedIds.length"></span> of {{ $listings->total() }} row(s) selected.
        </p>
        <div class="flex items-center gap-1.5">
            @if($listings->onFirstPage())
                <span class="px-3.5 py-1.5 text-xs font-semibold border border-border rounded-lg text-muted-foreground bg-card cursor-not-allowed select-none">Previous</span>
            @else
                <a href="{{ $listings->previousPageUrl() }}"
                   class="px-3.5 py-1.5 text-xs font-semibold border border-border rounded-lg text-muted-foreground bg-card hover:bg-muted transition-all">Previous</a>
            @endif

            @foreach($listings->getUrlRange(max(1, $listings->currentPage()-2), min($listings->lastPage(), $listings->currentPage()+2)) as $page => $url)
                @if($page == $listings->currentPage())
                    <span class="w-8 h-8 flex items-center justify-center text-xs font-bold border border-border rounded-lg text-primary-foreground bg-primary">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="w-8 h-8 flex items-center justify-center text-xs font-semibold border border-border rounded-lg text-muted-foreground bg-card hover:bg-muted transition-all">{{ $page }}</a>
                @endif
            @endforeach

            @if($listings->hasMorePages())
                <a href="{{ $listings->nextPageUrl() }}"
                   class="px-3.5 py-1.5 text-xs font-semibold border border-border rounded-lg text-muted-foreground bg-card hover:bg-muted transition-all">Next</a>
            @else
                <span class="px-3.5 py-1.5 text-xs font-semibold border border-border rounded-lg text-muted-foreground bg-card cursor-not-allowed select-none">Next</span>
            @endif
        </div>
    </div>
</div>

{{-- ══ Trash Bin ═══════════════════════════════════════════════════════════ --}}
@if($trashedListings->count())
<div x-data="{ open: false }" class="mt-4 bg-card border border-border rounded-xl overflow-hidden">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between px-5 py-3.5 hover:bg-muted transition-colors text-left">
        <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground">
            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-red-500"></i>
            Trash Bin
            <span class="bg-red-100 text-red-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $trashedListings->count() }}</span>
        </div>
        <i data-lucide="chevron-down" class="w-4 h-4 text-muted-foreground transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
    </button>
    <div x-show="open" x-cloak class="border-t border-border">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-border">
                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-muted-foreground">Title</th>
                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-muted-foreground">Seller</th>
                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-muted-foreground">Price</th>
                    <th class="px-5 py-2.5 text-left text-[11px] font-semibold text-muted-foreground">Deleted</th>
                    <th class="px-5 py-2.5 text-right text-[11px] font-semibold text-muted-foreground">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @foreach($trashedListings as $tl)
                <tr class="hover:bg-muted/50 transition-colors">
                    <td class="px-5 py-2.5 font-medium text-foreground">{{ $tl->title }}</td>
                    <td class="px-5 py-2.5 text-muted-foreground">{{ $tl->seller->shop_name ?? 'N/A' }}</td>
                    <td class="px-5 py-2.5 font-semibold text-foreground">₹{{ number_format($tl->price, 0) }}</td>
                    <td class="px-5 py-2.5 text-muted-foreground">{{ $tl->deleted_at->format('d M Y') }}</td>
                    <td class="px-5 py-2.5 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <form action="{{ route('admin.trash.restore', ['model' => 'listings', 'id' => $tl->id]) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-emerald-200 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-[10px] font-semibold transition-all">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i> Restore
                                </button>
                            </form>
                            @if(auth()->user()->isSuperAdmin())
                            <form action="{{ route('admin.trash.force', ['model' => 'listings', 'id' => $tl->id]) }}" method="POST"
                                  onsubmit="return confirm('Permanently delete? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-[10px] font-semibold transition-all">
                                    <i data-lucide="trash" class="w-3 h-3"></i> Delete Forever
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif


{{-- ══════════════════════════════════════════════════════════════════════════
     SIDE DRAWER — FULL PRODUCT DETAIL
════════════════════════════════════════════════════════════════════════════ --}}

{{-- Backdrop --}}
<div x-show="drawerOpen"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     @click="close()"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40" style="display:none;"></div>

{{-- Panel --}}
<div x-show="drawerOpen"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
     class="fixed top-0 right-0 h-full w-full max-w-xl bg-card shadow-2xl z-50 flex flex-col"
     style="display:none;">

    {{-- Drawer Header --}}
    <div class="flex items-start justify-between px-6 py-5 border-b border-border shrink-0">
        <div class="min-w-0 flex-1 pr-4">
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <span x-show="listing?.listing_number"
                      x-text="listing?.listing_number"
                      class="text-[10px] font-bold font-mono bg-primary text-primary-foreground px-2 py-0.5 rounded"></span>
                <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full border"
                      :class="{
                          'bg-muted text-foreground border-border':  listing?.listing_status === 'pending_approval',
                          'bg-emerald-50 text-emerald-700 border-emerald-200': listing?.listing_status === 'active',
                          'bg-blue-50 text-blue-700 border-blue-200':        listing?.listing_status === 'paused',
                          'bg-red-50 text-red-700 border-red-200':           listing?.listing_status === 'rejected',
                          'bg-violet-50 text-violet-700 border-violet-200':  listing?.listing_status === 'sold',
                          'bg-muted text-muted-foreground border-border':       !['pending_approval','active','paused','rejected','sold'].includes(listing?.listing_status),
                      }"
                      x-text="(listing?.listing_status||'').replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase())">
                </span>
            </div>
            <h3 class="text-sm font-bold text-foreground leading-snug" x-text="listing?.title ?? 'Listing Details'"></h3>
        </div>
        <button @click="close()" class="p-1.5 text-muted-foreground hover:text-foreground hover:bg-muted rounded-lg transition-all shrink-0">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    {{-- Drawer Scrollable Body --}}
    <div class="flex-1 overflow-y-auto">

        {{-- Previously Sold Warning --}}
        <div x-show="listing?.is_previously_sold"
             class="mx-6 mt-5 flex items-start gap-3 p-3.5 bg-amber-50 border border-amber-200 rounded-xl">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
            <p class="text-xs text-amber-800 font-semibold leading-relaxed">Previously sold serial number — verify authenticity carefully before approving.</p>
        </div>

        {{-- Images --}}
        <div class="px-6 pt-5">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-3">Product Images</p>
            <div class="grid grid-cols-3 gap-2">
                <template x-for="(img, i) in (listing?.images ?? [])" :key="i">
                    <div class="relative aspect-square rounded-xl overflow-hidden border border-border bg-muted">
                        <img :src="img.url" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300" alt="product">
                        <span x-show="img.is_primary"
                              class="absolute top-1.5 left-1.5 text-[9px] font-bold bg-primary text-primary-foreground hover:bg-primary/90 text-primary-foreground px-1.5 py-0.5 rounded">PRIMARY</span>
                    </div>
                </template>
                <div x-show="!listing?.images?.length" class="col-span-3 py-8 flex flex-col items-center text-muted-foreground">
                    <i data-lucide="image-off" class="w-6 h-6 mb-1.5"></i>
                    <span class="text-xs">No images uploaded</span>
                </div>
            </div>
        </div>

        {{-- Key IDs Section --}}
        <div class="px-6 pt-5">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-3">Identifiers</p>
            <div class="grid grid-cols-1 gap-2">
                <div class="flex items-center justify-between px-4 py-3 bg-muted border border-border rounded-xl">
                    <span class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Serial Number</span>
                    <span class="text-xs font-bold font-mono text-foreground select-all" x-text="listing?.serial_number || '—'"></span>
                </div>
                <div class="flex items-center justify-between px-4 py-3 bg-muted border border-border rounded-xl">
                    <span class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Listing Number</span>
                    <span class="text-xs font-bold font-mono text-blue-700 select-all" x-text="listing?.listing_number || '—'"></span>
                </div>
                <div class="flex items-center justify-between px-4 py-3 bg-muted border border-border rounded-xl">
                    <span class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Listed On</span>
                    <span class="text-xs font-semibold text-foreground" x-text="listing?.created_at || '—'"></span>
                </div>
            </div>
        </div>

        {{-- Product Details --}}
        <div class="px-6 pt-5">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-3">Product Details</p>
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Category</p>
                    <p class="text-xs font-semibold text-foreground mt-1 capitalize" x-text="listing?.category || '—'"></p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Grade</p>
                    <p class="text-xs font-bold text-foreground mt-1" x-text="'Grade ' + (listing?.grade || '—')"></p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Brand</p>
                    <p class="text-xs font-semibold text-foreground mt-1" x-text="listing?.brand || 'N/A'"></p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Model</p>
                    <p class="text-xs font-semibold text-foreground mt-1" x-text="listing?.model_name || 'N/A'"></p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Price</p>
                    <p class="text-sm font-bold text-foreground mt-1" x-text="'₹' + (listing?.price || '0')"></p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Shipping</p>
                    <p class="text-xs font-medium text-foreground mt-1 capitalize"
                       x-text="(listing?.shipping_type || '') + ' — ₹' + (listing?.shipping_charges || '0')"></p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Mfr. Warranty</p>
                    <p class="text-xs font-bold mt-1"
                       :class="{ 'text-emerald-700': listing?.manufacturer_warranty_status==='active', 'text-muted-foreground': listing?.manufacturer_warranty_status==='expired', 'text-red-600': listing?.manufacturer_warranty_status==='none' }"
                       x-text="listing?.manufacturer_warranty_status + (listing?.manufacturer_warranty_months ? ' (' + listing.manufacturer_warranty_months + 'm)' : '')">
                    </p>
                </div>
                <div class="bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Documents</p>
                    <p class="text-xs font-bold mt-1 capitalize"
                       :class="{ 'text-emerald-700': listing?.document_status==='full', 'text-foreground': listing?.document_status==='partial', 'text-red-600': listing?.document_status==='none' }"
                       x-text="listing?.document_status || '—'">
                    </p>
                </div>
                <div class="col-span-2 bg-muted rounded-xl p-3 border border-border">
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide">Pickup Location</p>
                    <p class="text-xs font-medium text-foreground mt-1"
                       x-text="[listing?.pickup_city, listing?.pickup_state].filter(Boolean).join(', ') || '—'"></p>
                </div>
            </div>

            {{-- Description --}}
            <div class="mt-2 bg-muted rounded-xl p-3 border border-border">
                <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-wide mb-1.5">Description / Condition Notes</p>
                <p class="text-xs text-muted-foreground leading-relaxed whitespace-pre-wrap"
                   x-text="listing?.description || listing?.condition_notes || 'No description provided.'"></p>
            </div>
        </div>

        {{-- Seller Card --}}
        <div class="px-6 pt-5 pb-6">
            <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest mb-3">Seller</p>
            <div class="bg-muted border border-border rounded-xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 flex items-center justify-center font-bold text-foreground text-sm shrink-0"
                     x-text="listing?.shop_name?.charAt(0)?.toUpperCase() ?? 'S'">
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-foreground truncate" x-text="listing?.shop_name"></p>
                    <p class="text-[11px] text-muted-foreground truncate" x-text="listing?.seller_email"></p>
                    <span class="text-[10px] font-semibold capitalize px-2 py-0.5 rounded-md bg-muted text-muted-foreground mt-1 inline-block"
                          x-text="(listing?.seller_badge || 'basic') + ' seller'"></span>
                </div>
                <a :href="listing?.seller_url" target="_blank"
                   class="text-xs font-semibold text-muted-foreground hover:text-foreground bg-card border border-border hover:border-ring px-3 py-1.5 rounded-lg transition-all shrink-0">
                    View Profile
                </a>
            </div>

            {{-- Rejection Reason --}}
            <div x-show="listing?.rejection_reason" class="mt-3 p-4 bg-red-50 border border-red-100 rounded-xl">
                <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-1">Rejection Reason</p>
                <p class="text-xs text-red-700 leading-relaxed" x-text="listing?.rejection_reason"></p>
            </div>
        </div>
    </div>

    {{-- Drawer Footer Actions --}}
    <div class="shrink-0 border-t border-border px-6 py-4 bg-card space-y-2">

        {{-- Approve --}}
        <template x-if="listing?.listing_status === 'pending_approval' && !rejecting">
            <form :action="listing?.approve_url" method="POST">
                <input type="hidden" name="_token" :value="listing?.csrf">
                <input type="hidden" name="status" value="active">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold py-2.5 rounded-xl transition-all">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Approve Listing
                </button>
            </form>
        </template>

        {{-- Reject Toggle --}}
        <template x-if="['pending_approval','active','paused'].includes(listing?.listing_status) && !rejecting">
            <button @click="rejecting = true"
                    class="w-full flex items-center justify-center gap-2 bg-card border border-border hover:border-red-200 hover:bg-red-50 text-muted-foreground hover:text-red-700 text-xs font-bold py-2.5 rounded-xl transition-all">
                <i data-lucide="x-circle" class="w-4 h-4"></i> Reject Listing
            </button>
        </template>

        {{-- Reject Form --}}
        <template x-if="['pending_approval','active','paused'].includes(listing?.listing_status) && rejecting">
            <form :action="listing?.approve_url" method="POST" class="space-y-2">
                <input type="hidden" name="_token" :value="listing?.csrf">
                <input type="hidden" name="status" value="rejected">
                <label class="block text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Rejection Reason</label>
                <textarea name="reason" rows="3" required x-model="rejectReason"
                          placeholder="e.g., Missing serial photo, incomplete docs…"
                          class="w-full p-3 text-xs border border-border rounded-xl bg-muted focus:bg-card focus:border-ring outline-none resize-none transition-all"></textarea>
                <div class="flex gap-2">
                    <button type="button" @click="rejecting = false"
                            class="flex-1 bg-card border border-border text-muted-foreground text-xs font-semibold py-2.5 rounded-xl hover:bg-muted transition-all">Cancel</button>
                    <button type="submit"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs font-bold py-2.5 rounded-xl transition-all">Confirm Reject</button>
                </div>
            </form>
        </template>

        {{-- Pause / Reactivate --}}
        <template x-if="listing?.listing_status === 'active' || listing?.listing_status === 'paused'">
            <form :action="listing?.pause_url" method="POST">
                <input type="hidden" name="_token" :value="listing?.csrf">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-card border border-border hover:border-blue-200 hover:bg-blue-50 text-muted-foreground hover:text-blue-700 text-xs font-bold py-2.5 rounded-xl transition-all">
                    <i data-lucide="pause-circle" class="w-4 h-4"></i>
                    <span x-text="listing?.listing_status === 'paused' ? 'Reactivate Listing' : 'Pause Listing'"></span>
                </button>
            </form>
        </template>

        {{-- Mark as Sold --}}
        <template x-if="listing?.listing_status === 'active' || listing?.listing_status === 'paused'">
            <form :action="listing?.approve_url" method="POST">
                <input type="hidden" name="_token" :value="listing?.csrf">
                <input type="hidden" name="status" value="sold">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-card border border-border hover:border-violet-200 hover:bg-violet-50 text-muted-foreground hover:text-violet-700 text-xs font-bold py-2.5 rounded-xl transition-all">
                    <i data-lucide="badge-check" class="w-4 h-4"></i> Mark as Sold
                </button>
            </form>
        </template>

        {{-- Move to Pending --}}
        <template x-if="listing?.listing_status === 'rejected' || listing?.listing_status === 'sold'">
            <form :action="listing?.approve_url" method="POST">
                <input type="hidden" name="_token" :value="listing?.csrf">
                <input type="hidden" name="status" value="pending_approval">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-card border border-border hover:border-border hover:bg-muted text-muted-foreground hover:text-foreground text-xs font-bold py-2.5 rounded-xl transition-all">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> Move to Pending Review
                </button>
            </form>
        </template>

        {{-- Reactivate (from rejected/sold) --}}
        <template x-if="listing?.listing_status === 'rejected' || listing?.listing_status === 'sold'">
            <form :action="listing?.approve_url" method="POST">
                <input type="hidden" name="_token" :value="listing?.csrf">
                <input type="hidden" name="status" value="active">
                <button type="submit"
                        class="w-full flex items-center justify-center gap-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 text-xs font-bold py-2.5 rounded-xl transition-all">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Reactivate Listing
                </button>
            </form>
        </template>

    </div>
</div>

</div>{{-- /x-data --}}
@endsection
