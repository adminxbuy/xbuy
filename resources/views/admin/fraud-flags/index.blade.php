@extends('layouts.admin')

@section('title', 'Fraud Flags')
@section('page_title', 'Fraud Detection Center')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'fraud_flags') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
        <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
        <span>Trash ({{ $trashedFlags->count() }})</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="fraudFlagsPage()">

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-3.5 text-sm font-medium">
            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl px-5 py-3.5 text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
            <ul class="list-disc ml-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Header / Actions Row ── --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 bg-white border border-zinc-200 rounded-xl p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-rose-50 text-rose-600 rounded-xl">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-zinc-950 text-sm">Fraud Detection Center</h4>
                <p class="text-xs text-zinc-500">Monitor auto-detected and manually created fraud flags. Review or dismiss each case.</p>
            </div>
        </div>
        <button
            @click="showCreateModal = true"
            class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            Flag Manually
        </button>
    </div>

    {{-- ── Summary Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $cards = [
                ['label' => 'Total Flags',  'value' => $summary['total'],     'color' => 'zinc',    'icon' => 'shield'],
                ['label' => 'Pending',       'value' => $summary['pending'],   'color' => 'amber',   'icon' => 'clock'],
                ['label' => 'Reviewed',      'value' => $summary['reviewed'],  'color' => 'blue',    'icon' => 'check-circle'],
                ['label' => 'Dismissed',     'value' => $summary['dismissed'], 'color' => 'emerald', 'icon' => 'x-circle'],
            ];
        @endphp

        @foreach($cards as $card)
        <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-{{ $card['color'] }}-50 text-{{ $card['color'] }}-600 flex items-center justify-center flex-shrink-0 border border-{{ $card['color'] }}-100">
                <i data-lucide="{{ $card['icon'] }}" class="w-4 h-4"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">{{ $card['label'] }}</p>
                <p class="text-xl font-black text-zinc-900">{{ $card['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Filters ── --}}
    <div class="bg-white border border-zinc-200 rounded-xl p-4 shadow-sm">
        <form action="{{ route('admin.fraud-flags.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            {{-- Search --}}
            <div class="w-full sm:w-64 space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Search User / Listing</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="User name or listing title…"
                           class="w-full pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none">
                </div>
            </div>

            {{-- Flag Type --}}
            <div class="w-full sm:w-56 space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Flag Type</label>
                <div class="relative">
                    <select name="flag_type"
                            class="w-full p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700 appearance-none">
                        <option value="">All Types</option>
                        <option value="duplicate_serial"            {{ request('flag_type') === 'duplicate_serial'             ? 'selected' : '' }}>Duplicate Serial</option>
                        <option value="multiple_accounts_same_ip"   {{ request('flag_type') === 'multiple_accounts_same_ip'    ? 'selected' : '' }}>Multiple Accounts / Same IP</option>
                        <option value="same_bank_multiple_sellers"  {{ request('flag_type') === 'same_bank_multiple_sellers'   ? 'selected' : '' }}>Same Bank – Multiple Sellers</option>
                        <option value="rapid_listings"              {{ request('flag_type') === 'rapid_listings'               ? 'selected' : '' }}>Rapid Listings</option>
                        <option value="suspicious_buyer_pattern"    {{ request('flag_type') === 'suspicious_buyer_pattern'     ? 'selected' : '' }}>Suspicious Buyer Pattern</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="w-full sm:w-40 space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Status</label>
                <div class="relative">
                    <select name="status"
                            class="w-full p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700 appearance-none">
                        <option value="">All Statuses</option>
                        <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="reviewed"  {{ request('status') === 'reviewed'  ? 'selected' : '' }}>Reviewed</option>
                        <option value="dismissed" {{ request('status') === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>

            {{-- Timeline / Date Range Picker --}}
            <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({
                start: '{{ request('date_start') }}',
                end: '{{ request('date_end') }}',
                startName: 'date_start',
                endName: 'date_end'
            })">
                <input type="hidden" name="date_start" x-model="dateStart">
                <input type="hidden" name="date_end" x-model="dateEnd">

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Timeline / Date Range</label>
                    <div class="relative">
                        <select x-model="currentPreset" @change="applyPreset($event.target.value)"
                                class="p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700 appearance-none">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="7days">Last 7 Days</option>
                            <option value="30days">Last 30 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-zinc-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>

                <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">From</label>
                        <div class="relative">
                            <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                            <input type="text" x-ref="startInput" placeholder="Start Date" readonly
                                   class="pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none cursor-pointer font-semibold text-zinc-750 w-32">
                        </div>
                    </div>
                    <span class="text-zinc-400 text-xs mb-3 flex-shrink-0">to</span>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">To</label>
                        <div class="relative">
                            <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                            <input type="text" x-ref="endInput" placeholder="End Date" readonly
                                   class="pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none cursor-pointer font-semibold text-zinc-750 w-32">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-5">
                <button type="submit"
                        class="px-4 py-2.5 bg-zinc-900 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all hover:bg-zinc-700">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'flag_type', 'status', 'date_start', 'date_end']))
                    <a href="{{ route('admin.fraud-flags.index') }}"
                       class="px-4 py-2.5 border border-zinc-200 text-zinc-600 hover:bg-zinc-50 rounded-xl text-xs font-semibold transition-all">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>


    <!-- Collapsible Trash Bin -->
    <div x-data="{ openTrash: false }" class="bg-zinc-50 border border-zinc-200 rounded-xl p-4 transition-all shadow-sm">
        <button @click="openTrash = !openTrash" type="button" class="flex items-center justify-between w-full text-zinc-700 hover:text-black focus:outline-none">
            <div class="flex items-center space-x-2 font-bold text-xs uppercase tracking-wider">
                <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
                <span>Trash Bin ({{ $trashedFlags->count() }})</span>
            </div>
            <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200" :class="openTrash ? 'rotate-180' : ''"></i>
        </button>
        
        <div x-show="openTrash" x-cloak class="mt-4 border-t border-zinc-200 pt-4">
            @if($trashedFlags->isEmpty())
                <p class="text-xs text-zinc-450 italic text-center py-4">No deleted fraud flags in trash.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-zinc-100/80 uppercase font-bold text-zinc-500 border-b border-zinc-200 text-[10px]">
                                <th class="px-4 py-2.5">Flag Type</th>
                                <th class="px-4 py-2.5">Flagged User</th>
                                <th class="px-4 py-2.5">Flagged Listing</th>
                                <th class="px-4 py-2.5">Deleted At</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($trashedFlags as $tFlag)
                                <tr class="hover:bg-zinc-100/30">
                                    <td class="px-4 py-3 font-bold text-zinc-800">{{ str_replace('_', ' ', $tFlag->flag_type) }}</td>
                                    <td class="px-4 py-3 text-zinc-650">{{ $tFlag->flaggedUser->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-zinc-650">{{ $tFlag->flaggedListing->title ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-zinc-400">{{ $tFlag->deleted_at->format('d M Y, H:i') }}</td>
                                    <td class="px-4 py-3 text-right flex justify-end space-x-2">
                                        <form action="{{ route('admin.trash.restore', ['model' => 'fraud-flags', 'id' => $tFlag->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-emerald-50 text-emerald-700 border border-emerald-250 hover:bg-emerald-100 rounded-lg font-bold text-[10px] uppercase flex items-center space-x-1">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                                <span>Restore</span>
                                            </button>
                                        </form>
                                        @if(auth()->user()->isSuperAdmin())
                                            <form action="{{ route('admin.trash.force', ['model' => 'fraud-flags', 'id' => $tFlag->id]) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this fraud flag? This action is irreversible!')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2 py-1 bg-red-50 text-red-700 border border-red-250 hover:bg-red-100 rounded-lg font-bold text-[10px] uppercase flex items-center space-x-1">
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
            @endif
        </div>
    </div>

    {{-- ── Flags Table ── --}}
    <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
        @forelse($flags as $flag)
            @php
                $typeLabels = [
                    'duplicate_serial'           => ['label' => 'Duplicate Serial',         'icon' => 'copy',          'color' => 'rose'],
                    'multiple_accounts_same_ip'  => ['label' => 'Multiple Accounts / IP',   'icon' => 'users',         'color' => 'orange'],
                    'same_bank_multiple_sellers' => ['label' => 'Same Bank – Multi Seller',  'icon' => 'landmark',      'color' => 'purple'],
                    'rapid_listings'             => ['label' => 'Rapid Listings',            'icon' => 'zap',           'color' => 'yellow'],
                    'suspicious_buyer_pattern'   => ['label' => 'Suspicious Buyer Pattern',  'icon' => 'eye',           'color' => 'blue'],
                ];

                $statusConfig = [
                    'pending'   => ['badge' => 'bg-amber-100 text-amber-700',  'dot' => 'bg-amber-400'],
                    'reviewed'  => ['badge' => 'bg-blue-100 text-blue-700',    'dot' => 'bg-blue-400'],
                    'dismissed' => ['badge' => 'bg-zinc-100 text-zinc-500',    'dot' => 'bg-zinc-300'],
                ];

                $type   = $typeLabels[$flag->flag_type]   ?? ['label' => $flag->flag_type, 'icon' => 'shield-alert', 'color' => 'zinc'];
                $status = $statusConfig[$flag->status]    ?? $statusConfig['pending'];
            @endphp

            <div class="border-b border-zinc-100 last:border-0" x-data="{ expanded: false }">
                {{-- Main Row --}}
                <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-4 hover:bg-zinc-50/40 transition-all">

                    {{-- Type Icon --}}
                    <div class="w-10 h-10 rounded-xl bg-{{ $type['color'] }}-50 text-{{ $type['color'] }}-600 border border-{{ $type['color'] }}-100 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="{{ $type['icon'] }}" class="w-4 h-4"></i>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0 space-y-0.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-bold text-zinc-900">{{ $type['label'] }}</span>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $status['badge'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status['dot'] }}"></span>
                                {{ ucfirst($flag->status) }}
                            </span>
                            <span class="text-[10px] text-zinc-400">#{{ $flag->id }}</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-zinc-500 mt-1">
                            @if($flag->flaggedUser)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="user" class="w-3 h-3"></i>
                                    {{ $flag->flaggedUser->name }}
                                </span>
                            @endif
                            @if($flag->flaggedListing)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="package" class="w-3 h-3"></i>
                                    {{ Str::limit($flag->flaggedListing->title, 35) }}
                                </span>
                            @endif
                            @if($flag->reviewer)
                                <span class="flex items-center gap-1">
                                    <i data-lucide="check-circle" class="w-3 h-3 text-blue-400"></i>
                                    Reviewed by {{ $flag->reviewer->name }}
                                </span>
                            @endif
                            <span class="flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3 h-3"></i>
                                {{ $flag->created_at->diffForHumans() }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        {{-- Expand details --}}
                        <button @click="expanded = !expanded"
                                class="px-3 py-1.5 border border-zinc-200 hover:bg-zinc-100 text-zinc-600 rounded-lg text-[10px] font-semibold flex items-center gap-1 transition-all">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span x-text="expanded ? 'Hide' : 'Details'"></span>
                        </button>

                        @if($flag->status === 'pending')
                            {{-- Review --}}
                            <form action="{{ route('admin.fraud-flags.review', $flag->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> Review
                                </button>
                            </form>

                            {{-- Dismiss --}}
                            <form action="{{ route('admin.fraud-flags.dismiss', $flag->id) }}" method="POST"
                                  onsubmit="return confirm('Dismiss this flag?')">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 text-zinc-600 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Dismiss
                                </button>
                            </form>
                        @elseif($flag->status === 'reviewed')
                            {{-- Only Dismiss available --}}
                            <form action="{{ route('admin.fraud-flags.dismiss', $flag->id) }}" method="POST"
                                  onsubmit="return confirm('Dismiss this flag?')">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 text-zinc-600 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> Dismiss
                                </button>
                            </form>
                        @else
                            <span class="text-[10px] font-semibold text-zinc-400 flex items-center gap-1 bg-zinc-50 border border-zinc-200/60 px-2.5 py-1 rounded-lg">
                                <i data-lucide="check-check" class="w-3 h-3 text-zinc-400"></i> Closed
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Expandable Details Panel --}}
                <div x-show="expanded" x-collapse class="border-t border-dashed border-zinc-200 bg-zinc-50/60 px-5 py-4">
                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                        <i data-lucide="info" class="w-3 h-3"></i> Flag Details
                    </p>

                    @if($flag->details && count($flag->details))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($flag->details as $key => $value)
                                <div class="bg-white border border-zinc-200 rounded-xl p-3">
                                    <p class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1">{{ str_replace('_', ' ', $key) }}</p>
                                    @if(is_array($value))
                                        <ul class="text-xs text-zinc-700 space-y-0.5 font-mono">
                                            @foreach($value as $item)
                                                <li class="flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
                                                    {{ $item }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-xs text-zinc-800 font-medium font-mono break-all">{{ $value }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-zinc-400 italic">No additional details recorded.</p>
                    @endif

                    {{-- Quick links --}}
                    <div class="flex flex-wrap gap-2 mt-4">
                        @if($flag->flaggedUser)
                            <a href="{{ route('admin.sellers.show', $flag->flaggedUser->id) }}"
                               class="inline-flex items-center gap-1.5 text-[10px] font-bold text-blue-600 hover:underline bg-blue-50 border border-blue-100 px-3 py-1.5 rounded-lg transition-all">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                View User Profile
                            </a>
                        @endif
                        @if($flag->flaggedListing)
                            <a href="{{ route('admin.listings.show', $flag->flaggedListing->id) }}"
                               class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-600 hover:underline bg-emerald-50 border border-emerald-100 px-3 py-1.5 rounded-lg transition-all">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                View Listing
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-16 text-center">
                <div class="w-14 h-14 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto mb-4 text-emerald-500">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-bold text-zinc-800">No Fraud Flags Found</h4>
                <p class="text-xs text-zinc-400 mt-1 max-w-xs mx-auto">
                    No fraud flags match your current filters.
                    @if(!request()->anyFilled(['search', 'flag_type', 'status']))
                        The system will automatically detect and list suspicious activity here.
                    @endif
                </p>
            </div>
        @endforelse

        {{-- Pagination --}}
        @if($flags->hasPages())
            <div class="px-5 py-4 border-t border-zinc-200 bg-zinc-50/50">
                {{ $flags->links() }}
            </div>
        @endif
    </div>

    {{-- ── Manual Create Modal ── --}}
    <div x-show="showCreateModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
         @keydown.escape.window="showCreateModal = false">

        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md"
             @click.stop
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">

            <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-rose-50 text-rose-600 rounded-lg">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-sm font-bold text-zinc-900">Flag Manually</h3>
                </div>
                <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-700 transition-colors p-1 rounded-lg hover:bg-zinc-100">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('admin.fraud-flags.store') }}" method="POST" class="px-6 py-5 space-y-4">
                @csrf

                {{-- Flag Type --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-zinc-600">Flag Type <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="flag_type" required
                                class="w-full p-3 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all appearance-none font-medium text-zinc-700">
                            <option value="">Select flag type…</option>
                            <option value="duplicate_serial">Duplicate Serial</option>
                            <option value="multiple_accounts_same_ip">Multiple Accounts / Same IP</option>
                            <option value="same_bank_multiple_sellers">Same Bank – Multiple Sellers</option>
                            <option value="rapid_listings">Rapid Listings</option>
                            <option value="suspicious_buyer_pattern">Suspicious Buyer Pattern</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-zinc-400">
                            <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                        </div>
                    </div>
                </div>

                {{-- User ID --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-zinc-600">User ID <span class="text-zinc-400">(optional)</span></label>
                    <input type="number" name="flagged_user_id" placeholder="e.g. 42"
                           class="w-full p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all">
                </div>

                {{-- Listing ID --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-zinc-600">Listing ID <span class="text-zinc-400">(optional)</span></label>
                    <input type="number" name="flagged_listing_id" placeholder="e.g. 17"
                           class="w-full p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all">
                </div>

                {{-- Note --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-zinc-600">Admin Note <span class="text-zinc-400">(optional)</span></label>
                    <textarea name="note" rows="3" placeholder="Describe what was suspicious…"
                              class="w-full p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all resize-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-zinc-100">
                    <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2.5 text-xs font-semibold text-zinc-600 border border-zinc-200 rounded-xl hover:bg-zinc-50 transition-all">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm">
                        <i data-lucide="flag" class="w-3.5 h-3.5"></i>
                        Create Flag
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function fraudFlagsPage() {
    return {
        showCreateModal: false,
    };
}
</script>
@endpush
@endsection
