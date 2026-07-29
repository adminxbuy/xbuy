@extends('layouts.admin')

@section('title', 'Staff Directory')
@section('page_title', 'Staff Directory')

@section('header_actions')
    @if(Auth::user()->isSuperAdmin())
    <a href="{{ route('admin.accounts.create') }}" class="inline-flex items-center gap-2 bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground font-medium px-4 py-2.5 rounded-lg text-xs transition-all shadow-sm">
        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
        <span>Add Staff Account</span>
    </a>
    @endif
@endsection

@section('content')
<div class="bg-card border border-border rounded-xl ring-0 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-border flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="font-bold text-foreground text-base">Active Platform Staff</h3>
            <p class="text-xs text-muted-foreground">Manage directory details, suspension, and notice dispatches.</p>
        </div>
        <form action="{{ route('admin.staff.directory') }}" method="GET" class="flex items-center gap-2">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search staff..." 
                       class="pl-9 pr-4 py-2 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all w-56 font-semibold">
            </div>
            @if(request()->filled('search'))
                <a href="{{ route('admin.staff.directory') }}" class="px-3 py-2 border border-border text-muted-foreground hover:bg-muted rounded-lg text-xs font-semibold">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground font-medium border-b border-border">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Designation</th>
                    <th class="px-6 py-4">Role Group</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4">This Month Earning</th>
                    <th class="px-6 py-4">Total Earned</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($staff as $member)
                <tr class="hover:bg-muted transition-all">
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=e4e4e7&color=71717a&size=40" class="w-10 h-10 rounded-xl" alt="avatar">
                            <div>
                                <h4 class="text-xs font-bold text-foreground">{{ $member->name }}</h4>
                                <p class="text-[10px] text-muted-foreground">{{ $member->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-muted-foreground text-xs font-semibold">
                        {{ $member->staffProfile->designation ?? 'Staff Member' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 text-[9px] rounded-full font-bold bg-muted text-muted-foreground border border-border">
                            {{ ucfirst(str_replace('_', ' ', $member->admin_role)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-muted-foreground text-xs">
                        {{ $member->staffProfile->joined_at ? $member->staffProfile->joined_at->format('d M, Y') : $member->created_at->format('d M, Y') }}
                    </td>
                    <td class="px-6 py-4 font-bold text-foreground text-xs">
                        ₹{{ number_format($member->this_month_earned, 2) }}
                    </td>
                    <td class="px-6 py-4 font-bold text-foreground text-xs">
                        ₹{{ number_format($member->total_earned, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 text-[10px] rounded-full font-bold
                            @if($member->status === 'active') bg-emerald-50 text-emerald-700 border border-emerald-200/60
                            @elseif($member->status === 'suspended') bg-amber-100 text-amber-800
                            @else bg-rose-50 text-rose-700 border border-rose-200/60 @endif">
                            {{ ucfirst($member->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap space-x-1">
                        <a href="{{ route('admin.staff.show', $member->id) }}" 
                           class="inline-flex items-center gap-1 text-xs font-bold bg-muted hover:bg-primary text-primary-foreground hover:bg-primary/90 text-foreground px-3 py-1.5 rounded-lg transition-all border border-border">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                            <span>Profile</span>
                        </a>

                        <a href="{{ route('admin.staff.notice', $member->id) }}" 
                           class="inline-flex items-center gap-1 text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 px-3 py-1.5 rounded-lg transition-all border border-blue-100">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            <span>Send Notice</span>
                        </a>

                        @if($member->id !== Auth::id())
                            @if($member->status === 'active')
                            <form action="{{ route('admin.staff.suspend', $member->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" onclick="return confirm('Are you sure you want to suspend this staff account?')"
                                        class="inline-flex items-center gap-1 text-xs font-bold bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg transition-all border border-amber-100">
                                    <i data-lucide="pause" class="w-3.5 h-3.5"></i>
                                    <span>Suspend</span>
                                </button>
                            </form>
                            @endif

                            @if($member->status !== 'banned')
                            <form action="{{ route('admin.staff.terminate', $member->id) }}" method="POST" class="inline-block">
                                @csrf
                                <button type="submit" onclick="return confirm('Are you sure you want to terminate this staff account? This will restrict all dashboard access.')"
                                        class="inline-flex items-center gap-1 text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-700 px-3 py-1.5 rounded-lg transition-all border border-rose-100">
                                    <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                                    <span>Terminate</span>
                                </button>
                            </form>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-10 h-10 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mb-2 border border-border">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-xs font-bold text-foreground">No Staff Found</h4>
                            <p class="text-[11px] text-muted-foreground mt-0.5">Try searching with a different term.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
