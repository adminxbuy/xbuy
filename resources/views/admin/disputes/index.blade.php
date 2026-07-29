@extends('layouts.admin')

@section('title', 'Disputes Resolution Center')
@section('page_title', 'Escrow Disputes Workspace')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'disputes') }}" class="flex items-center space-x-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold transition-all border border-rose-200/50">
        <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-600"></i>
        <span>Trash ({{ $trashedDisputes->count() }})</span>
    </a>
@endsection

@section('content')
<div class="rounded-xl overflow-hidden mb-6 bg-card text-card-foreground border border-border" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
    <!-- Filter Toolbar -->
    <div class="p-4 border-b border-border bg-card">
        <form action="{{ route('admin.disputes') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4 text-xs">
            <div class="flex flex-wrap items-center gap-4 flex-1">
                <div class="w-full sm:w-60 space-y-1">
                    <label class="text-[10px] font-semibold uppercase tracking-wide block text-muted-foreground">Status</label>
                    <select name="status" class="w-full p-2 rounded-lg focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none transition-all text-xs font-medium h-8 border border-input bg-card text-foreground">
                        <option value="">All Dispute Statuses</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open (Awaiting Seller)</option>
                        <option value="seller_responded" {{ request('status') === 'seller_responded' ? 'selected' : '' }}>Seller Responded</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>

                <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({
                    start: '{{ request('date_start') }}',
                    end: '{{ request('date_end') }}',
                    startName: 'date_start',
                    endName: 'date_end'
                })">
                    <input type="hidden" name="date_start" x-model="dateStart">
                    <input type="hidden" name="date_end" x-model="dateEnd">

                    <div class="space-y-1">
                        <label class="text-[10px] font-semibold uppercase tracking-wide block text-muted-foreground">Timeline / Date Range</label>
                        <div class="relative">
                            <select x-model="currentPreset" @change="applyPreset($event.target.value)"
                                    class="p-2 pl-3 pr-8 text-xs rounded-lg focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none transition-all cursor-pointer font-medium appearance-none h-8 border border-input bg-card text-foreground">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="7days">Last 7 Days</option>
                                <option value="30days">Last 30 Days</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="custom">Custom Range</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
                        <div class="space-y-1">
                            <label class="text-[10px] font-semibold uppercase tracking-wide block text-muted-foreground">From</label>
                            <div class="relative">
                                <i data-lucide="calendar" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
                                <input type="text" x-ref="startInput" placeholder="Start Date" readonly
                                       class="pl-8 pr-4 py-2 text-xs rounded-lg focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none cursor-pointer font-medium w-32 h-8 border border-input bg-card text-foreground">
                            </div>
                        </div>
                        <span class="text-xs mb-2.5 text-muted-foreground">to</span>
                        <div class="space-y-1">
                            <label class="text-[10px] font-semibold uppercase tracking-wide block text-muted-foreground">To</label>
                            <div class="relative">
                                <i data-lucide="calendar" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
                                <input type="text" x-ref="endInput" placeholder="End Date" readonly
                                       class="pl-8 pr-4 py-2 text-xs rounded-lg focus:ring-2 focus:ring-ring/50 focus:border-ring focus:outline-none cursor-pointer font-medium w-32 h-8 border border-input bg-card text-foreground">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-4">
                @if(request()->anyFilled(['status', 'date_start', 'date_end']))
                    <a href="{{ route('admin.disputes') }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center transition-colors border border-border text-muted-foreground">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar -->
    <div x-show="selectedIds.length > 0" x-transition.opacity style="display: none;" class="bg-muted border-t border-b border-border p-2.5 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="font-semibold text-[10px] uppercase tracking-wider px-2.5 py-0.5 rounded text-foreground bg-border"><span x-text="selectedIds.length"></span> Selected</span>
            <form method="POST" action="{{ route('admin.disputes.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
                @csrf
                <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
                <select name="action" x-model="bulkAction" class="text-xs rounded-lg focus:ring-ring focus:border-ring py-1 px-2.5 font-medium h-8 border border-input bg-card text-foreground">
                    <option value="">Bulk Actions...</option>
                    <option value="status_open">Set Open</option>
                    <option value="status_under_review">Set Under Review</option>
                    <option value="status_resolved">Set Resolved</option>
                    <option value="delete">Move to Trash</option>
                </select>
                <button type="button" @click="if(bulkAction && confirm('Are you sure you want to apply this action to ' + selectedIds.length + ' disputes?')) $refs.bulkForm.submit()" class="hover:bg-primary/90 px-3 py-1 rounded-lg text-xs font-medium transition-all h-8 bg-primary text-primary-foreground" :disabled="!bulkAction">
                    Apply
                </button>
            </form>
        </div>
        <button type="button" @click="selectedIds = []; selectAll = false" class="hover:text-foreground text-xs font-semibold px-2 py-1 text-muted-foreground">Clear</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-[10px] uppercase font-semibold bg-muted/50 border-b border-border text-muted-foreground">
                    <th class="px-6 py-4 w-12 text-center">
                        <input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ $disputes->pluck('id')->toJson() }} } else { selectedIds = [] }" class="w-3.5 h-3.5 rounded border-border text-foreground focus:ring-ring">
                    </th>
                    <th class="px-6 py-4">Order Details</th>
                    <th class="px-6 py-4">Buyer Info</th>
                    <th class="px-6 py-4">Seller Info</th>
                    <th class="px-6 py-4">Dispute Reason</th>
                    <th class="px-6 py-4">Time Raised</th>
                    <th class="px-6 py-4">Urgency / Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-xs">
                @forelse($disputes as $dispute)
                    <tr class="transition-colors border-b border-border" :class="{ 'hover:opacity-80': true }" {{ $dispute->urgency_row_tint }}>
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" value="{{ $dispute->id }}" x-model="selectedIds" class="w-3.5 h-3.5 rounded border-border text-foreground focus:ring-ring">
                        </td>
                        <td class="px-6 py-4 font-semibold text-foreground">
                            #{{ $dispute->order->order_number }}
                        </td>
                        <td class="px-6 py-4 font-medium text-foreground">{{ $dispute->buyer->name }}</td>
                        <td class="px-6 py-4 font-medium text-foreground">{{ $dispute->seller->shop_name }}</td>
                        <td class="px-6 py-4 truncate max-w-[200px] text-muted-foreground">{{ ucfirst(str_replace('_', ' ', $dispute->dispute_type)) }}</td>
                        <td class="px-6 py-4 font-medium text-muted-foreground">{{ $dispute->time_since_raised }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1.5">
                                <!-- Urgency badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border w-max
                                    @if($dispute->urgency_status === 'critical') bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-900
                                    @elseif($dispute->urgency_status === 'high') bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-900
                                    @else bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-900 @endif">
                                    @if($dispute->urgency_status === 'critical')
                                        <i class="inline-block w-3 h-3 mr-1" data-lucide="alert-triangle"></i>
                                        Critical ({{ $dispute->created_at->diffForHumans() }})
                                    @elseif($dispute->urgency_status === 'high')
                                        <i class="inline-block w-3 h-3 mr-1" data-lucide="alert-circle"></i>
                                        Urgent ({{ $dispute->created_at->diffForHumans() }})
                                    @else
                                        <i class="inline-block w-3 h-3 mr-1" data-lucide="check-circle"></i>
                                        New ({{ $dispute->created_at->diffForHumans() }})
                                    @endif
                                </span>
                                
                                <!-- Status badge -->
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium border w-max
                                    @if($dispute->status === 'open') bg-amber-50 text-amber-750 border-amber-200/60
                                    @elseif($dispute->status === 'seller_responded') bg-blue-50 text-blue-700 border-blue-200/60
                                    @elseif($dispute->status === 'under_review') bg-muted text-foreground border-border
                                    @elseif($dispute->status === 'resolved') bg-emerald-50 text-emerald-750 border-emerald-200/60
                                    @else bg-muted border-border text-foreground @endif">
                                    {{ ucfirst(str_replace('_', ' ', $dispute->status)) }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.disputes.show', $dispute->id) }}" class="inline-flex items-center space-x-1 text-xs font-semibold px-2.5 py-1.5 rounded-lg transition-colors bg-muted border border-border text-foreground hover:bg-muted/70">
                                <span>Resolve</span>
                                <i data-lucide="chevron-right" class="w-3 h-3"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center mb-2 bg-muted text-muted-foreground border border-border">
                                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                </div>
                                <h4 class="text-xs font-semibold text-foreground">No Disputes Found</h4>
                                <p class="text-[10px] mt-0.5 max-w-xs mx-auto text-muted-foreground">There are no escrow dispute claims matching your query filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($disputes->hasPages())
        <div class="px-4 py-3 border-t border-border bg-muted">
            {{ $disputes->links() }}
        </div>
    @endif
</div>

@endsection