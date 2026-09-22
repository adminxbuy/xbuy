@extends('layouts.admin')

@section('title', 'Admin Activity Logs')
@section('page_title', 'Activity Audit Logs')

@section('content')
<div class="bg-card border border-border rounded-xl ring-0 overflow-hidden mb-6">
 <div class="px-6 py-5 border-b border-border bg-muted flex justify-between items-center">
 <h3 class="text-sm font-semibold text-foreground">Security Audit Log Registry</h3>
 <span class="text-xs text-muted-foreground">Total Activity Tracks logged</span>
 </div>

 <!-- Table -->
 <div class="overflow-x-auto">
 <table class="w-full text-left border-collapse">
 <thead>
 <tr>
 <th class="h-10 px-4 align-middle text-xs font-medium text-muted-foreground bg-muted/40 uppercase tracking-wider border-b border-border">Admin Name</th>
 <th class="h-10 px-4 align-middle text-xs font-medium text-muted-foreground bg-muted/40 uppercase tracking-wider border-b border-border">Action Event</th>
 <th class="h-10 px-4 align-middle text-xs font-medium text-muted-foreground bg-muted/40 uppercase tracking-wider border-b border-border">Description details</th>
 <th class="h-10 px-4 align-middle text-xs font-medium text-muted-foreground bg-muted/40 uppercase tracking-wider border-b border-border">IP Address</th>
 <th class="h-10 px-4 align-middle text-xs font-medium text-muted-foreground bg-muted/40 uppercase tracking-wider border-b border-border">Timestamp</th>
 </tr>
 </thead>
 <tbody class="divide-y divide-border text-sm">
 @forelse($logs as $log)
 <tr class="border-b border-border transition-colors last:border-0 hover:bg-muted/50">
 <td class="p-4 align-middle text-sm font-semibold text-foreground border-b border-border/60">
 {{ $log->admin->name }}
 </td>
 <td class="p-4 align-middle text-sm text-foreground border-b border-border/60">
 <span class="px-2 py-0.5 rounded font-mono text-xs font-semibold uppercase bg-muted text-foreground">
 {{ $log->action }}
 </span>
 </td>
 <td class="p-4 align-middle text-sm text-muted-foreground border-b border-border/60 truncate max-w-[300px]">{{ $log->details }}</td>
 <td class="p-4 align-middle text-xs font-mono text-muted-foreground border-b border-border/60">{{ $log->ip_address }}</td>
 <td class="p-4 align-middle text-sm text-muted-foreground border-b border-border/60">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
 </tr>
 @empty
 <tr>
 <td colspan="5" class="px-6 py-16 text-center">
 <div class="flex flex-col items-center justify-center">
 <div class="w-12 h-12 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mb-3 border border-border/50 ">
 <i data-lucide="file-text" class="size-5"></i>
 </div>
 <h4 class="text-sm font-bold text-foreground">No Audit Logs Found</h4>
 <p class="text-xs text-muted-foreground mt-1 max-w-xs mx-auto">There are no administrative audit logs recorded in the database.</p>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>

 <!-- Pagination -->
 @if($logs->hasPages())
 <div class="px-6 py-4 border-t border-border bg-muted">
 {{ $logs->links() }}
 </div>
 @endif
</div>
@endsection
