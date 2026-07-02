@extends('layouts.admin')

@section('title', 'Registered Buyers')
@section('page_title', 'Registered Buyers')

@section('content')
<div class="space-y-6" x-data="usersPage()">
    <!-- Badge Filter Quick Navigation -->
    <div class="flex flex-wrap gap-2.5">
        <a href="{{ route('admin.users') }}"
           class="text-xs font-semibold px-4 py-2.5 rounded-xl border transition-all {{ !request('badge') ? 'bg-zinc-900 text-white border-zinc-900 shadow-sm' : 'bg-white border-zinc-200 text-zinc-650 hover:bg-zinc-50' }}">
            All Buyers
        </a>
        <a href="{{ route('admin.users', ['badge' => 'new_buyer']) }}"
           class="text-xs font-semibold px-4 py-2.5 rounded-xl border transition-all {{ request('badge') === 'new_buyer' ? 'bg-zinc-900 text-white border-zinc-900 shadow-sm' : 'bg-white border-zinc-200 text-zinc-650 hover:bg-zinc-50' }}">
            🆕 New Buyer ({{ $badgeCounts['new_buyer'] }})
        </a>
        <a href="{{ route('admin.users', ['badge' => 'verified_buyer']) }}"
           class="text-xs font-semibold px-4 py-2.5 rounded-xl border transition-all {{ request('badge') === 'verified_buyer' ? 'bg-zinc-900 text-white border-zinc-900 shadow-sm' : 'bg-white border-zinc-200 text-zinc-650 hover:bg-zinc-50' }}">
            🔵 Verified Buyer ({{ $badgeCounts['verified_buyer'] }})
        </a>
        <a href="{{ route('admin.users', ['badge' => 'trusted_buyer']) }}"
           class="text-xs font-semibold px-4 py-2.5 rounded-xl border transition-all {{ request('badge') === 'trusted_buyer' ? 'bg-zinc-900 text-white border-zinc-900 shadow-sm' : 'bg-white border-zinc-200 text-zinc-650 hover:bg-zinc-50' }}">
            🟢 Trusted Buyer ({{ $badgeCounts['trusted_buyer'] }})
        </a>
    </div>

    <!-- Advanced Filters Panel -->
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap items-center justify-between gap-4 text-sm">
            @if(request('badge'))
                <input type="hidden" name="badge" value="{{ request('badge') }}">
            @endif
            
            <div class="flex flex-wrap items-center gap-4 flex-1">
                <div class="w-full sm:w-60 space-y-1">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..."
                           class="w-full p-2.5 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none">
                </div>

                <div class="w-full sm:w-48 space-y-1">
                    <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">Status</label>
                    <select name="status" class="w-full p-2.5 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none font-semibold text-zinc-700">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="banned" {{ request('status') === 'banned' ? 'selected' : '' }}>Banned</option>
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
                        <label class="text-xs font-bold text-zinc-500 uppercase tracking-wider block">Joined Date Range</label>
                        <div class="relative">
                            <select x-model="currentPreset" @change="applyPreset($event.target.value)"
                                    class="p-2.5 pl-3 pr-8 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700 appearance-none">
                                <option value="all">All Time</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="7days">Last 7 Days</option>
                                <option value="30days">Last 30 Days</option>
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
                @if(request()->anyFilled(['search', 'status', 'date_start', 'date_end']))
                    <a href="{{ route('admin.users', request()->only('badge')) }}" class="px-4 py-2 border border-zinc-200 text-zinc-650 hover:bg-zinc-50 rounded-xl text-xs font-semibold flex items-center transition-all">
                        Clear Filters
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-zinc-50 border-b border-zinc-150 text-[11px] font-bold text-zinc-500 uppercase tracking-wider">
                        <th class="p-4 pl-6">Buyer Name</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Location (Show City)</th>
                        <th class="p-4 text-center">Trust Badge</th>
                        <th class="p-4 text-center">Completed Orders</th>
                        <th class="p-4">Joined Date</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50/30 transition-colors text-zinc-750">
                            <!-- Buyer Name -->
                            <td class="p-4 pl-6">
                                <div class="flex items-center gap-2.5">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=e4e4e7&color=71717a&size=32"
                                         class="w-8 h-8 rounded-lg shrink-0 border border-zinc-200 shadow-sm" alt="">
                                    <span class="font-bold text-zinc-850">{{ $user->name }}</span>
                                </div>
                            </td>
                            <!-- Email -->
                            <td class="p-4 text-xs font-medium text-zinc-600">{{ $user->email }}</td>
                            <!-- Phone -->
                            <td class="p-4 text-xs font-mono text-zinc-500">{{ $user->phone ?? '—' }}</td>
                            <!-- Location (Show City) -->
                            <td class="p-4 text-xs text-zinc-650 font-medium">
                                @if($user->city || $user->state)
                                    <div>{{ $user->city ?: '—' }}{{ $user->state ? ', ' . $user->state : '' }}</div>
                                    <div class="mt-1">
                                        @if($user->show_city)
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-250/50 rounded-full">Shown</span>
                                        @else
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 bg-zinc-100 text-zinc-500 border border-zinc-200 rounded-full">Hidden</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <!-- Trust Badge -->
                            <td class="p-4 text-center">
                                @php
                                    $badge = $user->buyer_badge ?? 'new_buyer';
                                    [$badgeLabel, $badgeClass] = match($badge) {
                                        'trusted_buyer' => ['Trusted Buyer', 'bg-emerald-50 text-emerald-705 border border-emerald-200/60 border-emerald-200'],
                                        'verified_buyer' => ['Verified Buyer', 'bg-blue-50 text-blue-705 border border-blue-200/60 border-blue-200'],
                                        default => ['New Buyer', 'bg-zinc-100 text-zinc-600 border-zinc-200'],
                                    };
                                @endphp
                                <span class="text-[10px] font-bold px-2 py-0.5 border rounded-full {{ $badgeClass }}">
                                    {{ $badgeLabel }}
                                </span>
                            </td>
                            <!-- Completed Orders -->
                            <td class="p-4 text-center font-bold text-zinc-700">
                                {{ $user->total_orders ?? 0 }}
                            </td>
                            <!-- Joined Date -->
                            <td class="p-4 text-xs text-zinc-450">{{ $user->created_at->format('d M Y') }}</td>
                            <!-- Status -->
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider rounded-full
                                    @if($user->status === 'active') bg-emerald-50 text-emerald-705 border border-emerald-200/60
                                    @elseif($user->status === 'suspended') bg-amber-100 text-amber-800
                                    @elseif($user->status === 'banned') bg-rose-50 text-rose-705 border border-rose-200/60
                                    @else bg-zinc-100 text-zinc-700 @endif">
                                    {{ $user->status }}
                                </span>
                            </td>
                             <!-- Actions -->
                             <td class="p-4 pr-6 text-right space-x-1.5 whitespace-nowrap">
                                 <button @click="openKycModal({{ json_encode($user) }})" class="text-[10px] bg-zinc-900 text-white hover:bg-zinc-850 px-2 py-1.5 rounded-lg transition-all font-bold" title="KYC / Wallet Info">
                                     KYC / Wallet
                                 </button>
                                 <a href="/admin/orders?buyer_id={{ $user->id }}" class="text-[10px] bg-zinc-100 text-zinc-700 hover:bg-zinc-200 border border-zinc-200 px-2 py-1.5 rounded-lg transition-all font-semibold" title="View Orders">
                                     Orders
                                 </a>
                                 @if($user->status === 'active')
                                     <button @click="openActionModal('suspend', {{ json_encode($user) }})" class="text-[10px] bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-250/30 px-2 py-1.5 rounded-lg transition-all font-bold">
                                         Suspend
                                     </button>
                                     <button @click="openActionModal('ban', {{ json_encode($user) }})" class="text-[10px] bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-250/30 px-2 py-1.5 rounded-lg transition-all font-bold">
                                         Ban
                                     </button>
                                 @else
                                     <span class="text-xs text-zinc-400 font-medium">—</span>
                                 @endif
                             </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-12 text-center text-zinc-450 italic">
                                No registered buyers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="p-4 border-t border-zinc-150">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Suspend/Ban Action Modal -->
    <div x-show="actionModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none" x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-zinc-950/65 backdrop-blur-md transition-opacity" @click="closeActionModal()"></div>

        <!-- Modal Card -->
        <div class="relative w-full max-w-md mx-auto bg-white rounded-[24px] shadow-2xl border border-zinc-200 z-10 overflow-hidden" 
             x-show="actionModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-5 border-b border-zinc-150 flex items-center justify-between bg-zinc-50/50">
                <h4 class="font-bold text-zinc-900 text-sm" x-text="actionType === 'suspend' ? 'Suspend User' : 'Ban User'"></h4>
                <button @click="closeActionModal()" class="text-zinc-450 hover:text-zinc-800 focus:outline-none">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form :action="'/admin/users/' + targetUser.id + '/' + actionType" method="POST" class="p-6 space-y-4 text-sm">
                @csrf
                <p class="text-xs text-zinc-550 leading-relaxed">
                    Are you sure you want to <span class="font-bold text-zinc-850" x-text="actionType"></span> the account of 
                    <span class="font-bold text-zinc-900" x-text="targetUser.name"></span> (<span x-text="targetUser.email"></span>)?
                </p>

                <div class="space-y-1.5">
                    <label for="action_reason" class="block text-xs font-bold text-zinc-500 uppercase tracking-wider">Reason for Action</label>
                    <textarea id="action_reason" name="reason" required placeholder="Specify why this account action is being taken..." rows="3"
                              class="w-full p-3 border border-zinc-200 rounded-xl bg-zinc-50 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all"></textarea>
                </div>

                <div class="pt-4 border-t border-zinc-150 flex justify-end space-x-2">
                    <button type="button" @click="closeActionModal()" class="px-4 py-2 border border-zinc-200 text-zinc-650 hover:bg-zinc-50 font-semibold rounded-lg text-xs transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            :class="actionType === 'suspend' ? 'bg-amber-500 hover:bg-amber-600' : 'bg-red-650 hover:bg-red-700'"
                            class="px-5 py-2 text-white font-semibold rounded-lg text-xs shadow-sm transition-all">
                        Confirm Action
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KYC / Wallet Details Modal -->
    <div x-show="kycModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto outline-none" x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-zinc-950/65 backdrop-blur-md transition-opacity" @click="closeKycModal()"></div>

        <!-- Modal Card -->
        <div class="relative w-full max-w-lg mx-auto bg-white rounded-[24px] shadow-2xl border border-zinc-200 z-10 overflow-hidden" 
             x-show="kycModalOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            
            <div class="px-6 py-5 border-b border-zinc-150 flex items-center justify-between bg-zinc-50/50">
                <h4 class="font-bold text-zinc-900 text-sm">KYC & Wallet Verification Details</h4>
                <button @click="closeKycModal()" class="text-zinc-450 hover:text-zinc-800 focus:outline-none">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="p-6 space-y-5 text-sm" x-show="kycUser">
                <!-- User Basic Info -->
                <div class="flex items-center gap-3 pb-4 border-b border-zinc-100" x-if="kycUser">
                    <img :src="'https://ui-avatars.com/api/?name=' + encodeURIComponent(kycUser.name) + '&background=e4e4e7&color=71717a&size=40'"
                         class="w-10 h-10 rounded-lg shrink-0 border border-zinc-200 shadow-sm" alt="">
                    <div>
                        <h5 class="font-bold text-zinc-850" x-text="kycUser.name"></h5>
                        <p class="text-xs text-zinc-505" x-text="kycUser.email"></p>
                    </div>
                </div>

                <!-- Wallet KYC data -->
                <div class="space-y-4" x-show="kycUser && kycUser.wallet">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block">First Name</span>
                            <span class="font-semibold text-zinc-800" x-text="kycUser && kycUser.wallet ? kycUser.wallet.first_name || '—' : '—'"></span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block">Last Name</span>
                            <span class="font-semibold text-zinc-800" x-text="kycUser && kycUser.wallet ? kycUser.wallet.last_name || '—' : '—'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block">Date of Birth</span>
                            <span class="font-semibold text-zinc-800" x-text="kycUser && kycUser.wallet && kycUser.wallet.dob_day ? (kycUser.wallet.dob_day + ' ' + kycUser.wallet.dob_month + ' ' + kycUser.wallet.dob_year) : '—'"></span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block">SSN Last 4</span>
                            <span class="font-semibold text-zinc-800 font-mono" x-text="kycUser && kycUser.wallet ? kycUser.wallet.ssn_last_four || '—' : '—'"></span>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block">Billing Address</span>
                        <span class="font-semibold text-zinc-850" x-text="kycUser ? kycUser.address || (kycUser.wallet ? kycUser.wallet.billing_address : '—') : '—'"></span>
                    </div>

                    <div class="pt-4 border-t border-zinc-100 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block">Wallet Balance</span>
                            <span class="font-extrabold text-zinc-900 text-lg" x-text="kycUser && kycUser.wallet ? '₹' + parseFloat(kycUser.wallet.balance).toFixed(2) : '₹0.00'"></span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-zinc-450 uppercase tracking-wider block mb-1">Activation Status</span>
                            <span :class="kycUser && kycUser.wallet && kycUser.wallet.is_activated ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-zinc-100 text-zinc-500 border border-zinc-200'"
                                  class="px-2.5 py-0.5 border text-xs font-bold rounded-full"
                                  x-text="kycUser && kycUser.wallet && kycUser.wallet.is_activated ? 'Active' : 'Pending'">
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="py-8 text-center text-zinc-400 italic" x-show="kycUser && !kycUser.wallet">
                    No wallet details initialized for this user.
                </div>

                <div class="pt-5 border-t border-zinc-150 flex justify-end">
                    <button type="button" @click="closeKycModal()" class="px-5 py-2.5 bg-zinc-900 text-white font-semibold rounded-lg text-xs hover:bg-zinc-800 transition-all shadow-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function usersPage() {
    return {
        actionModalOpen: false,
        actionType: 'suspend', // suspend or ban
        targetUser: { id: '', name: '', email: '' },
        kycModalOpen: false,
        kycUser: null,

        openActionModal(type, user) {
            this.actionType = type;
            this.targetUser = user;
            this.actionModalOpen = true;
            setTimeout(() => lucide.createIcons(), 50);
        },

        closeActionModal() {
            this.actionModalOpen = false;
        },

        openKycModal(user) {
            this.kycUser = user;
            this.kycModalOpen = true;
            setTimeout(() => lucide.createIcons(), 50);
        },

        closeKycModal() {
            this.kycModalOpen = false;
        }
    };
}
</script>
@endsection
