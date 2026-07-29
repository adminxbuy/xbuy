@extends('layouts.admin')

@section('title', 'Sellers Management')
@section('page_title', 'Sellers Directory')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'sellers') }}" class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900">
        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
        <span>Trash ({{ $trashedSellers->count() }})</span>
    </a>
@endsection

@section('content')
<div class="rounded-xl overflow-hidden border border-border bg-card" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
    <!-- Filter Toolbar -->
    <div class="p-4 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form action="{{ route('admin.sellers') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-2xl">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sellers by name, email, or shop..."
                       onchange="this.form.submit()"
                       class="w-full pl-9 pr-4 py-1.5 rounded-lg text-xs focus:outline-none transition-all h-8 border border-input bg-card text-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <select name="kyc_status" onchange="this.form.submit()" class="px-3 py-1 rounded-lg text-xs focus:outline-none transition-all h-8 border border-input bg-card text-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
                    <option value="">All KYC Status</option>
                    <option value="pending" {{ request('kyc_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('kyc_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('kyc_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-1 rounded-lg text-xs focus:outline-none transition-all h-8 border border-input bg-card text-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>

                <select name="badge_level" onchange="this.form.submit()" class="px-3 py-1 rounded-lg text-xs focus:outline-none transition-all h-8 border border-input bg-card text-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
                    <option value="">All Badges</option>
                    <option value="basic" {{ request('badge_level') === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="verified" {{ request('badge_level') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="fulfilled" {{ request('badge_level') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar -->
    <div x-show="selectedIds.length > 0" x-transition.opacity class="p-2.5 flex items-center justify-between bg-muted border-b border-border">
        <div class="flex items-center space-x-3">
            <span class="font-semibold text-[10px] uppercase tracking-wider px-2 py-0.5 rounded bg-primary text-primary-foreground"><span x-text="selectedIds.length"></span> Selected</span>

            <form method="POST" action="{{ route('admin.sellers.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
                @csrf
                <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">

                <select name="action" x-model="bulkAction" class="text-xs rounded-lg focus:ring-1 py-1 px-2.5 font-medium h-8 border border-input bg-card text-foreground focus:border-ring focus:ring-2 focus:ring-ring/50">
                    <option value="">Bulk Actions...</option>
                    <option value="status_active">Set Active</option>
                    <option value="status_suspended">Set Suspended</option>
                    <option value="status_banned">Set Banned</option>
                    <option value="delete">Move to Trash</option>
                </select>
                
                <button type="button" @click="if(bulkAction && confirm('Apply this action to ' + selectedIds.length + ' sellers?')) $refs.bulkForm.submit()" class="px-3 py-1 rounded-lg text-xs font-medium transition-all h-8 bg-primary text-primary-foreground" :disabled="!bulkAction">
                    Apply
                </button>
            </form>
        </div>
        <button type="button" @click="selectedIds = []; selectAll = false" class="text-xs font-semibold px-2 py-1 text-muted-foreground">Clear</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-muted/50">
                <tr class="border-b border-border">
                    <th class="px-6 py-3.5 w-12 text-center">
                        <input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ $sellers->pluck('id')->toJson() }} } else { selectedIds = [] }" class="rounded">
                    </th>
                    <th class="px-6 py-3.5 font-medium text-muted-foreground">Seller Detail</th>
                    <th class="px-6 py-3.5 font-medium text-muted-foreground">Shop Details</th>
                    <th class="px-6 py-3.5 font-medium text-muted-foreground">Badge</th>
                    <th class="px-6 py-3.5 font-medium text-muted-foreground">KYC Status</th>
                    <th class="px-6 py-3.5 font-medium text-muted-foreground">Account Status</th>
                    <th class="px-6 py-3.5 font-medium text-muted-foreground">Joined</th>
                    <th class="px-6 py-3.5 text-right font-medium text-muted-foreground">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                    <tr class="border-b border-border transition-colors">
                        <td class="px-6 py-3.5 text-center">
                            <input type="checkbox" value="{{ $seller->id }}" x-model="selectedIds" class="rounded">
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center space-x-2.5">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($seller->user->name) }}&background=e4e4e7&color=71717a" class="w-8 h-8 rounded-lg object-cover ring-1 ring-border" alt="avatar">
                                <div>
                                    <p class="font-semibold">{{ $seller->user->name }}</p>
                                    <p class="text-[10px] text-muted-foreground">{{ $seller->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <p class="font-medium">{{ $seller->shop_name }}</p>
                            <p class="text-[10px] text-muted-foreground">{{ $seller->shop_slug }}</p>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($seller->status !== 'banned')
                                <span class="inline-flex items-center text-xs font-semibold" style="color: {{ $seller->badge_color }};">
                                    @if($seller->badge_level === 'basic')
                                        <img src="{{ asset('website_assets/images/basic.png') }}" class="w-3.5 h-3.5 mr-1" alt="Basic">
                                    @elseif($seller->badge_level === 'verified')
                                        <img src="{{ asset('website_assets/images/verified.png') }}" class="w-3.5 h-3.5 mr-1" alt="Verified">
                                    @elseif($seller->badge_level === 'fulfilled')
                                        <img src="{{ asset('website_assets/images/FULFILLED.png') }}" class="w-3.5 h-3.5 mr-1" alt="Fulfilled">
                                    @else
                                        <i data-lucide="{{ $seller->badge_icon }}" class="w-3.5 h-3.5 mr-1"></i>
                                    @endif
                                    {{ $seller->badge_label }}
                                </span>
                            @else
                                <span class="text-[10px] font-semibold uppercase text-muted-foreground">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border
                                @if($seller->kyc_status === 'pending') bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900
                                @elseif($seller->kyc_status === 'approved') bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900
                                @else bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900 @endif">
                                {{ ucfirst($seller->kyc_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border
                                @if($seller->status === 'active') bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900
                                @elseif($seller->status === 'suspended') bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900
                                @elseif($seller->status === 'banned') bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900
                                @else bg-muted text-muted-foreground border-border @endif">
                                {{ ucfirst($seller->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-muted-foreground">{{ $seller->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="inline-flex items-center space-x-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors border border-border text-foreground">
                                <span>Manage</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-2 bg-muted">
                                    <i data-lucide="store" class="w-4 h-4 text-muted-foreground"></i>
                                </div>
                                <h4 class="text-xs font-bold">No Sellers Found</h4>
                                <p class="text-[10px] mt-0.5 max-w-xs mx-auto text-muted-foreground">No seller profiles match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sellers->hasPages())
        <div class="px-4 py-3 border-t border-border bg-muted">
            {{ $sellers->links() }}
        </div>
    @endif
</div>
@endsection