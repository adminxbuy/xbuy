@extends('layouts.admin')

@section('title', 'Sellers Management')
@section('page_title', 'Sellers Directory')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'sellers') }}" class="flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
        <span>Trash ({{ $trashedSellers->count() }})</span>
    </a>
@endsection

@section('content')
<div class="rounded-xl overflow-hidden" style="border: 1px solid var(--border); background: var(--card);" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
    <!-- Filter Toolbar -->
    <div class="p-4 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4" style="border-color: var(--border);">
        <form action="{{ route('admin.sellers') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-2xl">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5" style="color: var(--muted-foreground);"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sellers by name, email, or shop..."
                       onchange="this.form.submit()"
                       class="w-full pl-9 pr-4 py-1.5 rounded-lg text-xs focus:ring-1 focus:outline-none transition-all h-8" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <select name="kyc_status" onchange="this.form.submit()" class="px-3 py-1 rounded-lg text-xs focus:ring-1 focus:outline-none transition-all h-8" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                    <option value="">All KYC Status</option>
                    <option value="pending" {{ request('kyc_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('kyc_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('kyc_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-1 rounded-lg text-xs focus:ring-1 focus:outline-none transition-all h-8" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>

                <select name="badge_level" onchange="this.form.submit()" class="px-3 py-1 rounded-lg text-xs focus:ring-1 focus:outline-none transition-all h-8" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                    <option value="">All Badges</option>
                    <option value="basic" {{ request('badge_level') === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="verified" {{ request('badge_level') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="fulfilled" {{ request('badge_level') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar -->
    <div x-show="selectedIds.length > 0" x-transition.opacity class="p-2.5 flex items-center justify-between" style="background: var(--muted); border-bottom: 1px solid var(--border);">
        <div class="flex items-center space-x-3">
            <span class="font-semibold text-[10px] uppercase tracking-wider px-2 py-0.5 rounded" style="background: var(--primary); color: var(--primary-foreground);"><span x-text="selectedIds.length"></span> Selected</span>
            
            <form method="POST" action="{{ route('admin.sellers.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
                @csrf
                <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
                
                <select name="action" x-model="bulkAction" class="text-xs rounded-lg focus:ring-1 py-1 px-2.5 font-medium h-8" style="border: 1px solid var(--border); background: var(--card); color: var(--foreground);">
                    <option value="">Bulk Actions...</option>
                    <option value="status_active">Set Active</option>
                    <option value="status_suspended">Set Suspended</option>
                    <option value="status_banned">Set Banned</option>
                    <option value="delete">Move to Trash</option>
                </select>
                
                <button type="button" @click="if(bulkAction && confirm('Apply this action to ' + selectedIds.length + ' sellers?')) $refs.bulkForm.submit()" class="px-3 py-1 rounded-lg text-xs font-medium transition-all h-8" style="background: var(--primary); color: var(--primary-foreground);" :disabled="!bulkAction">
                    Apply
                </button>
            </form>
        </div>
        <button type="button" @click="selectedIds = []; selectAll = false" class="text-xs font-semibold px-2 py-1" style="color: var(--muted-foreground);">Clear</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
            <thead style="background: var(--muted);">
                <tr class="border-b" style="border-color: var(--border);">
                    <th class="px-6 py-3.5 w-12 text-center">
                        <input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ $sellers->pluck('id')->toJson() }} } else { selectedIds = [] }" class="rounded">
                    </th>
                    <th class="px-6 py-3.5 font-semibold" style="color: var(--muted-foreground);">Seller Detail</th>
                    <th class="px-6 py-3.5 font-semibold" style="color: var(--muted-foreground);">Shop Details</th>
                    <th class="px-6 py-3.5 font-semibold" style="color: var(--muted-foreground);">Badge</th>
                    <th class="px-6 py-3.5 font-semibold" style="color: var(--muted-foreground);">KYC Status</th>
                    <th class="px-6 py-3.5 font-semibold" style="color: var(--muted-foreground);">Account Status</th>
                    <th class="px-6 py-3.5 font-semibold" style="color: var(--muted-foreground);">Joined</th>
                    <th class="px-6 py-3.5 text-right font-semibold" style="color: var(--muted-foreground);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                    <tr class="border-b transition-colors" style="border-color: var(--border);">
                        <td class="px-6 py-3.5 text-center">
                            <input type="checkbox" value="{{ $seller->id }}" x-model="selectedIds" class="rounded">
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center space-x-2.5">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($seller->user->name) }}&background=e4e4e7&color=71717a" class="w-8 h-8 rounded-lg object-cover" alt="avatar" style="box-shadow: 0 0 0 1px var(--border);">
                                <div>
                                    <p class="font-semibold">{{ $seller->user->name }}</p>
                                    <p class="text-[10px]" style="color: var(--muted-foreground);">{{ $seller->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <p class="font-medium">{{ $seller->shop_name }}</p>
                            <p class="text-[10px]" style="color: var(--muted-foreground);">{{ $seller->shop_slug }}</p>
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
                                <span class="text-[10px] font-semibold uppercase" style="color: var(--muted-foreground);">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border
                                @if($seller->kyc_status === 'pending') style="background: #fef3c7; color: #92400e; border-color: #fde68a;"
                                @elseif($seller->kyc_status === 'approved') style="background: #f0fdf4; color: #166534; border-color: #bbf7d0;"
                                @else style="background: #fef2f2; color: #991b1b; border-color: #fecaca;" @endif">
                                {{ ucfirst($seller->kyc_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border
                                @if($seller->status === 'active') style="background: #f0fdf4; color: #166534; border-color: #bbf7d0;"
                                @elseif($seller->status === 'suspended') style="background: #fef3c7; color: #92400e; border-color: #fde68a;"
                                @elseif($seller->status === 'banned') style="background: #fef2f2; color: #991b1b; border-color: #fecaca;"
                                @else style="background: var(--muted); color: var(--muted-foreground); border-color: var(--border);" @endif">
                                {{ ucfirst($seller->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5" style="color: var(--muted-foreground);">{{ $seller->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="inline-flex items-center space-x-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors" style="border: 1px solid var(--border); color: var(--foreground);">
                                <span>Manage</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-2" style="background: var(--muted);">
                                    <i data-lucide="store" class="w-4 h-4" style="color: var(--muted-foreground);"></i>
                                </div>
                                <h4 class="text-xs font-bold">No Sellers Found</h4>
                                <p class="text-[10px] mt-0.5 max-w-xs mx-auto" style="color: var(--muted-foreground);">No seller profiles match your current filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sellers->hasPages())
        <div class="px-4 py-3 border-t" style="border-color: var(--border); background: var(--muted);">
            {{ $sellers->links() }}
        </div>
    @endif
</div>
@endsection