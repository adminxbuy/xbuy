@extends('layouts.admin')

@section('title', 'Staff Management')
@section('page_title', 'Staff Management')

@section('content')
<div class="space-y-6" x-data="adminAccountsPage()">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-5 py-3.5 text-sm font-medium">
            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl px-5 py-3.5 text-sm font-medium">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl px-5 py-3.5 text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 mt-0.5"></i>
            <ul class="list-disc ml-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-4 bg-card border border-border rounded-xl p-4 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-muted text-foreground rounded-xl border border-border">
                <i data-lucide="users-round" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-foreground text-sm">Staff Management</h4>
                <p class="text-xs text-muted-foreground">Create, update roles, and revoke staff access. Only Super Admins can manage this section.</p>
            </div>
        </div>
        <a href="{{ route('admin.accounts.create') }}"
           class="px-4 py-2.5 bg-primary text-primary-foreground hover:bg-primary/90 hover:bg-[#e6c22f] text-foreground rounded-xl text-xs font-bold flex items-center gap-1.5 transition-all shadow-sm">
            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
            Add Staff
        </a>
    </div>

    {{-- Role Summary Cards --}}
    @php
        $roleConfig = [
            'super_admin' => ['label' => 'Super Admin',  'color' => 'violet', 'icon' => 'crown'],
            'operations'  => ['label' => 'Operations',   'color' => 'blue',   'icon' => 'settings-2'],
            'support'     => ['label' => 'Support',      'color' => 'teal',   'icon' => 'headphones'],
            'finance'     => ['label' => 'Finance',      'color' => 'emerald','icon' => 'banknote'],
            'content'     => ['label' => 'Content',      'color' => 'amber',  'icon' => 'pen-line'],
            'moderator'   => ['label' => 'Moderator',    'color' => 'rose',   'icon' => 'shield-check'],
            'custom'      => ['label' => 'Custom Scope', 'color' => 'zinc',   'icon' => 'sliders'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3">
        @foreach($roleConfig as $roleKey => $rc)
        <a href="{{ route('admin.accounts.index', ['admin_role' => $roleKey]) }}"
           class="bg-card border {{ request('admin_role') === $roleKey ? 'border-yellow-400 ring-2 ring-yellow-150' : 'border-border' }} rounded-xl p-3 shadow-sm hover:shadow-md transition-all flex flex-col items-center gap-2 text-center">
            <div class="w-9 h-9 rounded-xl bg-{{ $rc['color'] }}-50 text-{{ $rc['color'] }}-600 flex items-center justify-center border border-{{ $rc['color'] }}-100">
                <i data-lucide="{{ $rc['icon'] }}" class="w-4 h-4"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-muted-foreground">{{ $rc['label'] }}</p>
                <p class="text-lg font-black text-foreground">{{ $roleCounts[$roleKey] ?? 0 }}</p>
            </div>
        </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-card border border-border rounded-xl p-4 shadow-sm">
        <form action="{{ route('admin.accounts.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
            <div class="w-full sm:w-64 space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Search</label>
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email…"
                           class="w-full pl-9 pr-4 py-2.5 text-xs border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none">
                </div>
            </div>
            <div class="w-full sm:w-48 space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Role</label>
                <div class="relative">
                    <select name="admin_role" class="w-full p-2.5 pl-3 pr-8 text-xs border border-border rounded-xl bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all cursor-pointer font-semibold text-foreground appearance-none">
                        <option value="">All Roles</option>
                        @foreach($roleConfig as $rk => $rc)
                            <option value="{{ $rk }}" {{ request('admin_role') === $rk ? 'selected' : '' }}>{{ $rc['label'] }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-muted-foreground">
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 pt-5">
                <button type="submit" class="px-4 py-2.5 bg-primary text-primary-foreground rounded-xl text-xs font-semibold flex items-center gap-1.5 hover:bg-primary/80 transition-all">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                </button>
                @if(request()->anyFilled(['search','admin_role']))
                    <a href="{{ route('admin.accounts.index') }}" class="px-4 py-2.5 border border-border text-muted-foreground hover:bg-muted rounded-xl text-xs font-semibold transition-all">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Admins Table --}}
    <div class="bg-card border border-border rounded-xl ring-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/60">
                        <th class="text-left px-5 py-3 text-[10px] font-black text-muted-foreground uppercase tracking-widest">Staff Member</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-muted-foreground uppercase tracking-widest">Role</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-muted-foreground uppercase tracking-widest">Access</th>
                        <th class="text-left px-4 py-3 text-[10px] font-black text-muted-foreground uppercase tracking-widest">Joined</th>
                        <th class="text-right px-5 py-3 text-[10px] font-black text-muted-foreground uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse($admins as $admin)
                        @php
                            $isPreset = in_array($admin->admin_role, ['super_admin', 'operations', 'support', 'finance', 'content', 'moderator']);
                            $customAccessList = $admin->permissions ? implode(', ', array_map('ucfirst', array_keys(array_filter($admin->permissions, fn($val) => $val !== 'none')))) : 'Custom Permissions';
                            $accessMap = [
                                'super_admin' => 'Full Access',
                                'operations'  => 'Sellers, Listings, Orders, Disputes, Users',
                                'support'     => 'Tickets, Orders (read)',
                                'finance'     => 'Escrow, Payouts, Analytics',
                                'content'     => 'Articles, Pages, Categories, Mail',
                                'moderator'   => 'Listings, Fraud Flags, Ratings, Alerts',
                            ];
                            if ($isPreset) {
                                $rc = $roleConfig[$admin->admin_role];
                                $accessStr = $accessMap[$admin->admin_role] ?? 'Full Access';
                            } else {
                                $roleLabel = ($admin->admin_role === 'custom') ? 'Custom Scope' : $admin->admin_role;
                                $rc = [
                                    'label' => ucfirst(str_replace('_', ' ', $roleLabel)),
                                    'color' => 'zinc',
                                    'icon' => 'sliders'
                                ];
                                $accessStr = $customAccessList;
                            }
                            
                            $avatarBg = match($rc['color']) {
                                'violet' => '7c3aed',
                                'blue' => '2563eb',
                                'teal' => '0d9488',
                                'emerald' => '059669',
                                'amber' => 'd97706',
                                'rose' => 'e11d48',
                                default => '71717a',
                            };
                        @endphp
                        <tr class="hover:bg-muted/40 transition-all">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&background={{ $avatarBg }}&color=fff&size=36"
                                         class="w-8 h-8 rounded-lg flex-shrink-0" alt="{{ $admin->name }}">
                                    <div>
                                        <p class="text-xs font-bold text-foreground flex items-center gap-1.5">
                                            {{ $admin->name }}
                                            @if($admin->id === Auth::id())
                                                <span class="text-[9px] bg-amber-50 text-amber-705 border border-amber-200/60 px-1.5 py-0.5 rounded-full font-bold">You</span>
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-muted-foreground">{{ $admin->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full bg-{{ $rc['color'] }}-50 text-{{ $rc['color'] }}-700 border border-{{ $rc['color'] }}-100">
                                    <i data-lucide="{{ $rc['icon'] }}" class="w-3 h-3"></i>
                                    {{ $rc['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="text-[10px] text-muted-foreground max-w-[200px] truncate" title="{{ $accessStr }}">
                                    {{ $accessStr }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-[10px] text-muted-foreground">{{ $admin->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($admin->id !== Auth::id())
                                        {{-- Edit Role --}}
                                        <a href="{{ route('admin.accounts.edit', $admin->id) }}"
                                           class="px-3 py-1.5 bg-muted hover:bg-muted border border-border text-foreground rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
                                        </a>

                                        {{-- Revoke Access --}}
                                        <form action="{{ route('admin.accounts.destroy', $admin->id) }}" method="POST"
                                              onsubmit="return confirm('Revoke staff access for {{ $admin->name }}? They will become a regular buyer.')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 rounded-lg text-[10px] font-bold flex items-center gap-1 transition-all">
                                                <i data-lucide="user-x" class="w-3.5 h-3.5"></i> Revoke
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[10px] text-muted-foreground bg-muted border border-border px-2.5 py-1 rounded-lg">Your account</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-16 text-center">
                                <div class="w-12 h-12 rounded-xl bg-muted border border-border flex items-center justify-center mx-auto mb-3 text-muted-foreground">
                                    <i data-lucide="users-round" class="w-5 h-5"></i>
                                </div>
                                <h4 class="text-sm font-bold text-foreground">No Staff Accounts Found</h4>
                                <p class="text-xs text-muted-foreground mt-1">No staff members match your current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($admins->hasPages())
            <div class="px-5 py-4 border-t border-border bg-muted/50">
                {{ $admins->links() }}
            </div>
        @endif
    </div></div>

@endsection
