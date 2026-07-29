@extends('layouts.admin')

@section('title', 'Alerts Registry')
@section('page_title', 'System Alerts & Notifications')

@section('header_actions')
 <a href="{{ route('admin.trash.index', 'alerts') }}" class="flex items-center space-x-1.5 px-4 py-2.5 bg-muted hover:bg-muted text-foreground rounded-lg border border-border/50 text-xs font-medium transition-all border border-border ">
 <i data-lucide="trash-2" class="w-4.5 h-4.5 text-muted-foreground"></i>
 <span>Trash ({{ $trashedAlerts->count() }})</span>
 </a>
@endsection

@section('content')
 <div class="space-y-6">
 <!-- Top Actions / Controls -->
 <div
 class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 bg-card border border-border rounded-xl p-4 ">
 <div class="flex items-center gap-3">
 <div class="p-2.5 bg-muted text-muted-foreground rounded-xl">
 <i data-lucide="bell" class="w-5 h-5 animate-pulse"></i>
 </div>
 <div>
 <h4 class="font-bold text-foreground text-sm">Alerts Workspace</h4>
 <p class="text-xs text-muted-foreground">Manage real-time system notifications and administration events.</p>
 </div>
 </div>

 <div class="flex flex-wrap items-center gap-2">
 @if(\App\Models\AdminAlert::where('is_read', false)->exists())
 <form action="{{ route('admin.alerts.read-all') }}" method="POST" class="inline">
 @csrf
 <button type="submit"
 class="w-full sm:w-auto px-4 py-2 border border-border hover:bg-muted text-foreground rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all">
 <i data-lucide="check-square" class="w-3.5 h-3.5"></i>
 <span>Mark All as Read</span>
 </button>
 </form>
 @endif

 @if(\App\Models\AdminAlert::exists())
 <form action="{{ route('admin.alerts.clear-all') }}" method="POST" class="inline"
 onsubmit="return confirm('Are you sure you want to clear all alerts? This cannot be undone.');">
 @csrf
 <button type="submit"
 class="w-full sm:w-auto px-4 py-2 bg-muted hover:bg-muted text-foreground rounded-lg text-xs font-semibold flex items-center justify-center gap-1.5 transition-all">
 <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
 <span>Clear All Alerts</span>
 </button>
 </form>
 @endif
 </div>
 </div>

 <!-- Filters Panel -->
 <div class="bg-card border border-border rounded-xl p-4 ">
 <form action="{{ route('admin.alerts') }}" method="GET"
 class="flex flex-wrap items-center justify-between gap-4 text-sm">
 <div class="flex flex-wrap items-center gap-4 flex-1">
 <!-- Search input -->
 <div class="w-full sm:w-72 space-y-1">
 <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">Search</label>
 <div class="relative">
 <i data-lucide="search"
 class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
 <input type="text" name="search" value="{{ request('search') }}"
 placeholder="Search title or message content…"
 class="w-full pl-9 pr-4 py-2.5 text-xs border border-border rounded-lg bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none">
 </div>
 </div>

 <!-- Status Filter -->
 <div class="w-full sm:w-48 space-y-1">
 <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">Status</label>
 <div class="relative">
 <select name="status"
 class="w-full p-2.5 pl-3 pr-8 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all cursor-pointer font-semibold text-foreground appearance-none">
 <option value="">All Statuses</option>
 <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
 <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
 </select>
 <div
 class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
 <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
 </div>
 </div>
 </div>

 <!-- Timeline Filter -->
 <div class="date-range-picker-container flex flex-wrap items-center gap-4" x-data="dateRangePicker({
 start: '{{ request('date_start') }}',
 end: '{{ request('date_end') }}',
 startName: 'date_start',
 endName: 'date_end'
 })">
 <input type="hidden" name="date_start" x-model="dateStart">
 <input type="hidden" name="date_end" x-model="dateEnd">

 <div class="space-y-1">
 <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">Created Date
 Range</label>
 <div class="relative">
 <select x-model="currentPreset" @change="applyPreset($event.target.value)"
 class="p-2.5 pl-3 pr-8 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all cursor-pointer font-semibold text-foreground appearance-none">
 <option value="all">All Time</option>
 <option value="today">Today</option>
 <option value="yesterday">Yesterday</option>
 <option value="7days">Last 7 Days</option>
 <option value="30days">Last 30 Days</option>
 <option value="this_month">This Month</option>
 <option value="last_month">Last Month</option>
 <option value="custom">Custom Range</option>
 </select>
 <div
 class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
 <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
 </div>
 </div>
 </div>

 <div class="flex items-end gap-2" x-show="currentPreset === 'custom'">
 <div class="space-y-1">
 <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">From</label>
 <div class="relative">
 <i data-lucide="calendar"
 class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-ref="startInput" placeholder="Start Date" readonly
 class="pl-9 pr-4 py-2.5 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none cursor-pointer font-semibold text-foreground w-32">
 </div>
 </div>
 <span class="text-muted-foreground text-xs mb-3 flex-shrink-0">to</span>
 <div class="space-y-1">
 <label class="text-xs font-bold text-muted-foreground uppercase tracking-wider block">To</label>
 <div class="relative">
 <i data-lucide="calendar"
 class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground"></i>
 <input type="text" x-ref="endInput" placeholder="End Date" readonly
 class="pl-9 pr-4 py-2.5 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none cursor-pointer font-semibold text-foreground w-32">
 </div>
 </div>
 </div>
 </div>
 </div>

 <!-- Clear filters -->
 <div class="flex items-center gap-2 pt-5">
 @if(request()->anyFilled(['search', 'status', 'date_start', 'date_end']))
 <a href="{{ route('admin.alerts') }}"
 class="px-4 py-2 border border-border text-muted-foreground hover:bg-muted rounded-lg text-xs font-semibold flex items-center transition-all">
 Clear Filters
 </a>
 @endif
 </div>
 </form>
 </div>

 <!-- Collapsible Trash Bin -->
 <div x-data="{ openTrash: false }"
 class="bg-muted border border-border rounded-xl p-4 transition-all ">
 <button @click="openTrash = !openTrash" type="button"
 class="flex items-center justify-between w-full text-foreground hover:text-foreground focus:outline-none">
 <div class="flex items-center space-x-2 font-bold text-xs uppercase tracking-wider">
 <i data-lucide="trash-2" class="w-4.5 h-4.5 text-muted-foreground"></i>
 <span>Trash Bin ({{ $trashedAlerts->count() }})</span>
 </div>
 <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
 :class="openTrash ? 'rotate-180' : ''"></i>
 </button>

 <div x-show="openTrash" x-cloak class="mt-4 border-t border-border pt-4">
 @if($trashedAlerts->isEmpty())
 <p class="text-xs text-muted-foreground italic text-center py-4">No deleted alerts in trash.</p>
 @else
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse text-xs">
 <thead>
 <tr
 class="bg-muted uppercase font-bold text-muted-foreground border-b border-border text-[10px]">
 <th class="px-4 py-2.5">Title</th>
 <th class="px-4 py-2.5">Message</th>
 <th class="px-4 py-2.5">Deleted At</th>
 <th class="px-4 py-2.5 text-right">Actions</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-border">
 @foreach($trashedAlerts as $tAlert)
 <tr class="hover:bg-muted">
 <td class="px-4 py-3 font-bold text-foreground">{{ $tAlert->title }}</td>
 <td class="px-4 py-3 text-muted-foreground truncate max-w-xs" title="{{ $tAlert->message }}">
 {{ $tAlert->message }}</td>
 <td class="px-4 py-3 text-muted-foreground">{{ $tAlert->deleted_at->format('d M Y, H:i') }}</td>
 <td class="px-4 py-3 text-right flex justify-end space-x-2">
 <form
 action="{{ route('admin.trash.restore', ['model' => 'alerts', 'id' => $tAlert->id]) }}"
 method="POST">
 @csrf
 <button type="submit"
 class="px-2 py-1 bg-muted text-foreground border border-emerald-250 hover:bg-muted rounded-lg font-medium text-[10px] uppercase flex items-center space-x-1">
 <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
 <span>Restore</span>
 </button>
 </form>
 @if(auth()->user()->isSuperAdmin())
 <form
 action="{{ route('admin.trash.force', ['model' => 'alerts', 'id' => $tAlert->id]) }}"
 method="POST"
 onsubmit="return confirm('Are you sure you want to permanently delete this alert? This action is irreversible!')">
 @csrf
 @method('DELETE')
 <button type="submit"
 class="px-2 py-1 bg-muted text-foreground border border-red-250 hover:bg-muted rounded-lg font-bold text-[10px] uppercase flex items-center space-x-1">
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

 <!-- Notifications List -->
 <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden">
 <div class="divide-y divide-border">
 @forelse($alerts as $alert)
 @php
 // Map notification type to dynamic icons and color accents
 $colorClass = 'bg-muted text-muted-foreground border-border';
 $iconName = 'bell';

 if (str_contains(strtolower($alert->type), 'dispute') || str_contains(strtolower($alert->title), 'dispute')) {
 $colorClass = 'bg-muted text-rose-600 border-rose-100';
 $iconName = 'alert-triangle';
 } elseif (str_contains(strtolower($alert->type), 'kyc') || str_contains(strtolower($alert->type), 'seller') || str_contains(strtolower($alert->title), 'seller')) {
 $colorClass = 'bg-muted text-muted-foreground border-indigo-100';
 $iconName = 'user-plus';
 } elseif (str_contains(strtolower($alert->type), 'order') || str_contains(strtolower($alert->title), 'order')) {
 $colorClass = 'bg-muted text-muted-foreground border-blue-100';
 $iconName = 'shopping-bag';
 } elseif (str_contains(strtolower($alert->type), 'escrow') || str_contains(strtolower($alert->title), 'escrow')) {
 $colorClass = 'bg-muted text-muted-foreground border-amber-100';
 $iconName = 'shield-alert';
 } elseif (str_contains(strtolower($alert->type), 'listing') || str_contains(strtolower($alert->title), 'listing')) {
 $colorClass = 'bg-muted text-muted-foreground border-emerald-100';
 $iconName = 'package';
 }

 // Build action links based on references
 $url = '#';
 if ($alert->reference_type && $alert->reference_id) {
 if (str_contains(strtolower($alert->reference_type), 'dispute')) {
 $url = route('admin.disputes.show', $alert->reference_id);
 } elseif (str_contains(strtolower($alert->reference_type), 'order')) {
 $url = route('admin.orders.show', $alert->reference_id);
 } elseif (str_contains(strtolower($alert->reference_type), 'seller')) {
 $url = route('admin.sellers.show', $alert->reference_id);
 } elseif (str_contains(strtolower($alert->reference_type), 'listing')) {
 $url = route('admin.listings.show', $alert->reference_id);
 }
 }
 @endphp

 <div
 class="p-5 flex items-start justify-between gap-4 transition-all duration-200 {{ !$alert->is_read ? 'bg-muted' : 'hover:bg-muted' }}">
 <div class="flex items-start gap-4">
 <!-- Alert Icon -->
 <div
 class="w-10 h-10 rounded-xl border flex items-center justify-center flex-shrink-0 {{ $colorClass }}">
 <i data-lucide="{{ $iconName }}" class="w-5 h-5"></i>
 </div>

 <!-- Alert Message -->
 <div class="space-y-1">
 <div class="flex flex-wrap items-center gap-2">
 <h5 class="font-bold text-foreground text-sm">
 @if($url !== '#')
 <a href="{{ $url }}"
 class="hover:text-brand-ink hover:underline flex items-center gap-1">
 <span>{{ $alert->title }}</span>
 <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
 </a>
 @else
 {{ $alert->title }}
 @endif
 </h5>
 @if(!$alert->is_read)
 <span class="inline-block w-2.5 h-2.5 rounded-full bg-primary text-primary-foreground hover:bg-primary/90" title="Unread"></span>
 @endif
 </div>
 <p class="text-xs text-muted-foreground leading-relaxed">{{ $alert->message }}</p>
 <span class="inline-block text-[10px] text-muted-foreground font-medium pt-1">
 <i data-lucide="calendar" class="inline w-3 h-3 mr-0.5 -mt-0.5"></i>
 {{ $alert->created_at->diffForHumans() }}
 </span>
 </div>
 </div>

 <!-- Actions -->
 <div class="shrink-0 flex items-center gap-2">
 @if(!$alert->is_read)
 <form action="{{ route('admin.alerts.read', $alert->id) }}" method="POST">
 @csrf
 <button type="submit"
 class="px-3 py-1.5 bg-muted hover:bg-primary text-primary-foreground hover:bg-primary/90/25 border border-border hover:border-foreground text-foreground hover:text-foreground rounded-lg text-[10px] font-medium transition-all"
 title="Mark as Read">
 <i data-lucide="check" class="w-3.5 h-3.5 inline mr-0.5"></i> Mark Read
 </button>
 </form>
 @else
 <span
 class="text-[10px] font-semibold text-muted-foreground flex items-center gap-1 bg-muted border border-border/60 px-2.5 py-1 rounded-lg">
 <i data-lucide="check-check" class="w-3 h-3 text-muted-foreground"></i> Read
 </span>
 @endif
 </div>
 </div>
 @empty
 <div class="p-16 text-center">
 <div
 class="w-12 h-12 rounded-xl bg-muted border border-border/60 flex items-center justify-center mx-auto mb-3 text-muted-foreground">
 <i data-lucide="bell-off" class="w-5 h-5"></i>
 </div>
 <h4 class="text-sm font-bold text-foreground">No Alerts Found</h4>
 <p class="text-xs text-muted-foreground mt-1 max-w-xs mx-auto">There are no administrative system alerts
 matching your current query filters.</p>
 </div>
 @endforelse
 </div>

 <!-- Pagination -->
 @if($alerts->hasPages())
 <div class="px-5 py-4 border-t border-border bg-muted">
 {{ $alerts->links() }}
 </div>
 @endif
 </div>

 </div>
@endsection