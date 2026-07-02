@extends('layouts.admin')

@section('title', 'Sellers Management')
@section('page_title', 'Sellers Directory')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'sellers') }}" class="flex items-center space-x-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold transition-all border border-rose-200/50">
        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600"></i>
        <span>Trash ({{ $trashedSellers->count() }})</span>
    </a>
@endsection

@section('content')
<div class="bg-white rounded-xl ring-1 ring-zinc-950/5 overflow-hidden mb-6" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
    <!-- Filter Toolbar -->
    <div class="p-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <form action="{{ route('admin.sellers') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1 max-w-2xl">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-zinc-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sellers by name, email, or shop..."
                       onchange="this.form.submit()"
                       class="w-full pl-9 pr-4 py-1.5 border border-zinc-200 rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all h-8">
            </div>
            
             <div class="flex flex-wrap items-center gap-3">
                <select name="kyc_status" onchange="this.form.submit()" class="px-3 py-1 bg-white border border-zinc-200 rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all h-8">
                    <option value="">All KYC Status</option>
                    <option value="pending" {{ request('kyc_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('kyc_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('kyc_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="status" onchange="this.form.submit()" class="px-3 py-1 bg-white border border-zinc-200 rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all h-8">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
                </select>

                <select name="badge_level" onchange="this.form.submit()" class="px-3 py-1 bg-white border border-zinc-200 rounded-lg text-xs focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all h-8">
                    <option value="">All Badges</option>
                    <option value="basic" {{ request('badge_level') === 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="verified" {{ request('badge_level') === 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="fulfilled" {{ request('badge_level') === 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar -->
    <div x-show="selectedIds.length > 0" x-transition.opacity style="display: none;" class="bg-zinc-50 border-y border-zinc-200/50 p-2.5 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-zinc-800 font-semibold text-[10px] uppercase tracking-wider bg-zinc-200 px-2 py-0.5 rounded"><span x-text="selectedIds.length"></span> Selected</span>
            
            <form method="POST" action="{{ route('admin.sellers.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
                @csrf
                <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
                
                <select name="action" x-model="bulkAction" class="text-xs border-zinc-200 bg-white rounded-lg focus:ring-zinc-950 focus:border-zinc-950 py-1 px-2.5 font-medium text-zinc-700 h-8">
                    <option value="">Bulk Actions...</option>
                    <option value="status_active">Set Active</option>
                    <option value="status_suspended">Set Suspended</option>
                    <option value="status_banned">Set Banned</option>
                    <option value="delete">Move to Trash</option>
                </select>
                
                <button type="button" @click="if(bulkAction && confirm('Are you sure you want to apply this action to ' + selectedIds.length + ' sellers?')) $refs.bulkForm.submit()" class="bg-zinc-900 hover:bg-zinc-800 text-white px-3 py-1 rounded-lg text-xs font-medium transition-all h-8" :disabled="!bulkAction">
                    Apply
                </button>
            </form>
        </div>
        <button type="button" @click="selectedIds = []; selectAll = false" class="text-zinc-500 hover:text-zinc-900 text-xs font-semibold px-2 py-1">
            Clear
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50/75 text-[10px] uppercase font-semibold text-zinc-500 border-b border-zinc-200">
                    <th class="px-6 py-3.5 w-12 text-center">
                        <input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ $sellers->pluck('id')->toJson() }} } else { selectedIds = [] }" class="w-3.5 h-3.5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-950">
                    </th>
                    <th class="px-6 py-3.5">Seller Detail</th>
                    <th class="px-6 py-3.5">Shop Details</th>
                    <th class="px-6 py-3.5">Badge</th>
                    <th class="px-6 py-3.5">KYC Status</th>
                    <th class="px-6 py-3.5">Account Status</th>
                    <th class="px-6 py-3.5">Joined Date</th>
                    <th class="px-6 py-3.5 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 text-xs">
                @forelse($sellers as $seller)
                    <tr class="hover:bg-zinc-50/50 transition-colors">
                        <td class="px-6 py-3.5 text-center">
                            <input type="checkbox" value="{{ $seller->id }}" x-model="selectedIds" class="w-3.5 h-3.5 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-950">
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center space-x-2.5">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($seller->user->name) }}&background=e4e4e7&color=71717a" class="w-8 h-8 rounded-lg ring-1 ring-zinc-950/5" alt="avatar">
                                <div>
                                    <p class="font-semibold text-zinc-900 text-xs">{{ $seller->user->name }}</p>
                                    <p class="text-[10px] text-zinc-400">{{ $seller->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <div>
                                <p class="font-medium text-zinc-800">{{ $seller->shop_name }}</p>
                                <p class="text-[10px] text-zinc-400">{{ $seller->shop_slug }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($seller->status !== 'banned')
                                <span class="inline-flex items-center text-xs font-semibold" 
                                      style="color: {{ $seller->badge_color }};">
                                    @if($seller->badge_level === 'basic')
                                        <img src="{{ asset('website_assets/images/basic.png') }}" class="w-3.5 h-3.5 mr-1" alt="Basic Seller">
                                    @elseif($seller->badge_level === 'verified')
                                        <img src="{{ asset('website_assets/images/verified.png') }}" class="w-3.5 h-3.5 mr-1" alt="Verified Seller">
                                    @elseif($seller->badge_level === 'fulfilled')
                                        <img src="{{ asset('website_assets/images/FULFILLED.png') }}" class="w-3.5 h-3.5 mr-1" alt="Fulfilled Seller">
                                    @else
                                        <i data-lucide="{{ $seller->badge_icon }}" class="w-3.5 h-3.5 mr-1"></i>
                                    @endif
                                    {{ $seller->badge_label }}
                                </span>
                            @else
                                <span class="text-[10px] text-zinc-400 font-semibold uppercase">None</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border
                                @if($seller->kyc_status === 'pending') bg-amber-50 text-amber-705 border-amber-200/60
                                @elseif($seller->kyc_status === 'approved') bg-emerald-50 text-emerald-705 border-emerald-200/60
                                @else bg-rose-50 text-rose-705 border-rose-200/60 @endif">
                                {{ ucfirst($seller->kyc_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border
                                @if($seller->status === 'active') bg-emerald-50 text-emerald-750 border-emerald-200/60
                                @elseif($seller->status === 'suspended') bg-amber-50 text-amber-750 border-amber-200/60
                                @elseif($seller->status === 'banned') bg-rose-50 text-rose-750 border-rose-200/60
                                @else bg-zinc-55 border-zinc-200/60 text-zinc-700 @endif">
                                {{ ucfirst($seller->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 text-zinc-400 text-[11px]">{{ $seller->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('admin.sellers.show', $seller->id) }}" class="inline-flex items-center space-x-1 text-xs font-semibold bg-zinc-100 hover:bg-zinc-200 text-zinc-900 px-2.5 py-1.5 rounded-lg border border-zinc-200/50 transition-colors">
                                <span>Manage</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-10 h-10 bg-zinc-50 rounded-lg flex items-center justify-center text-zinc-400 mb-2 ring-1 ring-zinc-200/50">
                                    <i data-lucide="store" class="w-4 h-4"></i>
                                </div>
                                <h4 class="text-xs font-bold text-zinc-800">No Sellers Found</h4>
                                <p class="text-[10px] text-zinc-400 mt-0.5 max-w-xs mx-auto">No seller profiles match your current search query or status filter.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($sellers->hasPages())
        <div class="px-4 py-3 border-t border-zinc-100 bg-zinc-50/30">
            {{ $sellers->links() }}
        </div>
    @endif
</div>
@endsection
