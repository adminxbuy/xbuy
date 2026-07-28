@extends('layouts.admin')

@section('title', 'Admin Activity Logs')
@section('page_title', 'Activity Audit Logs')

@section('content')
<div class="bg-card border border-border rounded-xl ring-0 overflow-hidden mb-6">
    <div class="px-6 py-5 border-b border-border bg-muted/50 flex justify-between items-center">
        <h3 class="font-bold text-foreground text-sm">Security Audit Log Registry</h3>
        <span class="text-xs text-muted-foreground">Total Activity Tracks logged</span>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-muted text-[10px] uppercase font-bold text-muted-foreground border-b border-border">
                    <th class="px-6 py-4">Admin Name</th>
                    <th class="px-6 py-4">Action Event</th>
                    <th class="px-6 py-4">Description details</th>
                    <th class="px-6 py-4">IP Address</th>
                    <th class="px-6 py-4">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border text-sm">
                @forelse($logs as $log)
                    <tr class="hover:bg-muted/30 transition-all">
                        <td class="px-6 py-4 font-semibold text-foreground">
                            {{ $log->admin->name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded font-mono text-xs font-semibold uppercase bg-muted text-foreground">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-muted-foreground truncate max-w-[300px]">{{ $log->details }}</td>
                        <td class="px-6 py-4 font-mono text-muted-foreground text-xs">{{ $log->ip_address }}</td>
                        <td class="px-6 py-4 text-muted-foreground">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-12 h-12 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mb-3 border border-border/50 shadow-sm">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
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
        <div class="px-6 py-4 border-t border-border bg-muted/50">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
