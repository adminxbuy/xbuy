@extends('layouts.admin')

@section('title', 'Support Tickets Management')
@section('page_title', 'Buyer Support Tickets Workspace')

@section('header_actions')
    <a href="{{ route('admin.trash.index', 'tickets') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg border border-rose-200/50 text-xs font-bold transition-all border border-red-200 shadow-sm">
        <i data-lucide="trash-2" class="w-4.5 h-4.5 text-red-500"></i>
        <span>Trash ({{ $trashedTickets->count() }})</span>
    </a>
@endsection

@section('content')
<div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 mb-6" x-data="{ selectedIds: [], selectAll: false, bulkAction: '' }">
    <!-- Filter Toolbar -->
    <div class="p-4 border-b border-zinc-200 bg-white">
        <form action="{{ route('admin.tickets') }}" method="GET" class="flex flex-wrap items-center justify-between gap-4 text-sm">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <div class="flex flex-wrap items-center gap-4 flex-1">
                <!-- Search Input -->
                <div class="w-full sm:w-72 space-y-1">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">Search</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-zinc-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by subject, message, buyer..."
                               class="w-full pl-9 pr-4 py-2 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all text-xs font-semibold text-zinc-700">
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-48 space-y-1">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">Status</label>
                    <select name="status" onchange="this.form.submit()" class="w-full p-2.5 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all text-xs font-semibold text-zinc-700">
                        <option value="">All Tickets</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>

                <!-- Date Range Timeline Filter -->
                <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({
                    start: '{{ request('date_start') }}',
                    end: '{{ request('date_end') }}',
                    startName: 'date_start',
                    endName: 'date_end'
                })">
                    <input type="hidden" name="date_start" x-model="dateStart">
                    <input type="hidden" name="date_end" x-model="dateEnd">

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">Timeline / Date Range</label>
                        <div class="relative">
                            <select x-model="currentPreset" @change="applyPreset($event.target.value)"
                                    class="p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700 appearance-none">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="7days">Last 7 Days</option>
                                <option value="15days">Last 15 Days</option>
                                <option value="30days">Last 30 Days</option>
                                <option value="90days">Last 90 Days</option>
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
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">From</label>
                            <div class="relative">
                                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                                <input type="text" x-ref="startInput" placeholder="Start Date" readonly
                                       class="pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none cursor-pointer font-semibold text-zinc-750 w-32">
                            </div>
                        </div>
                        <span class="text-zinc-400 text-xs mb-3">to</span>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">To</label>
                            <div class="relative">
                                <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-zinc-400"></i>
                                <input type="text" x-ref="endInput" placeholder="End Date" readonly
                                       class="pl-9 pr-4 py-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none cursor-pointer font-semibold text-zinc-750 w-32">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-5">
                @if(request()->anyFilled(['status', 'search', 'date_start', 'date_end']))
                    <a href="{{ route('admin.tickets') }}" 
                       class="px-4 py-2.5 border border-zinc-200 text-zinc-650 hover:bg-zinc-50 rounded-xl text-xs font-semibold flex items-center transition-all">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Bulk Action Toolbar -->
    <div x-show="selectedIds.length > 0" x-transition.opacity style="display: none;" class="bg-amber-50 border-y border-amber-100 p-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <span class="text-amber-800 font-bold text-xs uppercase tracking-wider bg-amber-100/50 px-3 py-1 rounded-lg"><span x-text="selectedIds.length"></span> Selected</span>
            <form method="POST" action="{{ route('admin.tickets.bulk-action') }}" class="flex items-center space-x-2" x-ref="bulkForm">
                @csrf
                <input type="hidden" name="selected_ids" x-bind:value="JSON.stringify(selectedIds)">
                <select name="action" x-model="bulkAction" class="text-xs border-amber-200/50 bg-white rounded-lg focus:ring-amber-500 focus:border-amber-500 py-1.5 px-3 font-semibold text-zinc-700">
                    <option value="">Bulk Actions...</option>
                    <option value="status_open">Set Open</option>
                    <option value="status_in_progress">Set In Progress</option>
                    <option value="status_resolved">Set Resolved</option>
                    <option value="delete">Move to Trash</option>
                </select>
                <button type="button" @click="if(bulkAction && confirm('Are you sure you want to apply this action to ' + selectedIds.length + ' tickets?')) $refs.bulkForm.submit()" class="bg-black hover:bg-zinc-800 text-white px-3 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider transition-colors" :disabled="!bulkAction">
                    Apply
                </button>
            </form>
        </div>
        <button type="button" @click="selectedIds = []; selectAll = false" class="text-amber-600 hover:text-amber-800 text-xs font-bold px-2 py-1 uppercase tracking-wider">Clear</button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-zinc-50 text-[10px] uppercase font-bold text-zinc-400 border-b border-zinc-200">
                    <th class="px-6 py-4 w-12 text-center">
                        <input type="checkbox" x-model="selectAll" @change="if(selectAll) { selectedIds = {{ $tickets->pluck('id')->toJson() }} } else { selectedIds = [] }" class="w-4 h-4 rounded border-zinc-300 text-black focus:ring-black">
                    </th>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Buyer Info</th>
                    <th class="px-6 py-4">Subject</th>
                    <th class="px-6 py-4">Message</th>
                    <th class="px-6 py-4">Time Raised</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Update Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 text-sm">
                @forelse($tickets as $ticket)
                    <tr class="transition-all hover:bg-zinc-50/50">
                        <td class="px-6 py-4 text-center">
                            <input type="checkbox" value="{{ $ticket->id }}" x-model="selectedIds" class="w-4 h-4 rounded border-zinc-300 text-black focus:ring-black">
                        </td>
                        <td class="px-6 py-4 font-bold text-zinc-950">
                            #{{ $ticket->id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-semibold text-zinc-900">{{ $ticket->buyer->name ?? 'Unknown Buyer' }}</span>
                                <span class="text-xs text-zinc-500">{{ $ticket->buyer->email ?? '' }}</span>
                                @if(!empty($ticket->buyer->phone))
                                    <span class="text-[11px] text-zinc-400 font-mono mt-0.5">{{ $ticket->buyer->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-zinc-800">
                            {{ $ticket->subject }}
                        </td>
                        <td class="px-6 py-4 text-zinc-500 max-w-[280px]">
                            <div class="line-clamp-2 hover:line-clamp-none transition-all duration-300 whitespace-pre-wrap cursor-pointer" title="Click to expand/collapse message">
                                {{ $ticket->message }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-zinc-500 font-medium text-xs">
                            {{ $ticket->created_at->format('M d, Y h:i A') }}
                            <div class="text-[10px] text-zinc-400 font-normal mt-0.5">{{ $ticket->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                @if($ticket->status === 'open') bg-red-100 text-red-800 border border-red-200
                                @elseif($ticket->status === 'in_progress') bg-amber-100 text-amber-800 border border-amber-200
                                @elseif($ticket->status === 'resolved') bg-emerald-50 text-emerald-705 border border-emerald-200/60 border border-emerald-200
                                @else bg-zinc-100 text-zinc-800 @endif">
                                @if($ticket->status === 'open')
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 mr-1.5 animate-pulse"></span>
                                @elseif($ticket->status === 'in_progress')
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5"></span>
                                @elseif($ticket->status === 'resolved')
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span>
                                @endif
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.tickets.status', $ticket->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                @csrf
                                <select name="status" onchange="this.form.submit()" 
                                        class="p-1.5 pr-8 border border-zinc-200 rounded-lg text-xs font-semibold text-zinc-700 bg-white hover:bg-zinc-50 focus:outline-none focus:ring-1 focus:ring-zinc-950 focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950">
                                    <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                    <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 bg-zinc-100 rounded-xl flex items-center justify-center text-zinc-400 mb-3 border border-zinc-200/50 shadow-sm">
                                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                                </div>
                                <h4 class="text-sm font-bold text-zinc-800">No Tickets Found</h4>
                                <p class="text-xs text-zinc-450 mt-1 max-w-xs mx-auto">There are no support tickets matching your filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tickets->hasPages())
        <div class="px-6 py-4 border-t border-zinc-200 bg-zinc-50/50">
            {{ $tickets->links() }}
        </div>
    @endif
</div>

@endsection
