@extends('layouts.admin')

@section('title', 'Staff Profile - ' . $member->name)
@section('page_title', 'Staff Profile: ' . $member->name)

@section('header_actions')
    <a href="{{ route('admin.staff.directory') }}" class="flex items-center space-x-1.5 px-3 py-1.5 bg-zinc-100 hover:bg-zinc-200 text-zinc-700 hover:text-zinc-900 rounded-lg text-xs font-semibold transition-all border border-zinc-200 shadow-sm">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Back to Directory</span>
    </a>
@endsection

@section('content')
<div x-data="{ 
    activeTab: 'profile',
    bankMasked: true,
    upiMasked: true,
    aadhaarMasked: true,
    panMasked: true,
    expandedMonths: {}
}" class="grid grid-cols-1 lg:grid-cols-4 gap-6">

    <!-- Sidebar Info Card -->
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-zinc-900 text-white hover:bg-zinc-800"></div>
            <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=e4e4e7&color=71717a&size=96" 
                 class="w-24 h-24 rounded-xl mx-auto border-2 border-zinc-100 shadow-sm mb-4" alt="avatar">
            
            <h3 class="font-bold text-zinc-900 text-lg leading-tight">{{ $member->name }}</h3>
            <p class="text-xs font-semibold text-zinc-400 mt-0.5">{{ $member->staffProfile->designation ?? 'Staff Member' }}</p>
            
            <span class="inline-block mt-3 px-3 py-1 text-[10px] rounded-full font-bold
                @if($member->status === 'active') bg-emerald-50 text-emerald-705 border border-emerald-200/60
                @elseif($member->status === 'suspended') bg-amber-100 text-amber-800
                @else bg-rose-50 text-rose-705 border border-rose-200/60 @endif border border-black/5">
                {{ ucfirst($member->status) }}
            </span>

            <div class="border-t border-zinc-100 mt-6 pt-5 text-left text-xs space-y-3">
                <div class="flex justify-between">
                    <span class="text-zinc-400">Email</span>
                    <span class="font-bold text-zinc-800">{{ $member->email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-400">Phone</span>
                    <span class="font-bold text-zinc-800">{{ $member->phone ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-400">Joined Date</span>
                    <span class="font-bold text-zinc-800">
                        {{ $member->staffProfile->joined_at ? $member->staffProfile->joined_at->format('d M, Y') : $member->created_at->format('d M, Y') }}
                    </span>
                </div>
            </div>
            
            <div class="mt-6 flex flex-col gap-2">
                <a href="{{ route('admin.staff.notice', $member->id) }}" 
                   class="w-full inline-flex items-center justify-center gap-1.5 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-zinc-800 hover:text-white font-bold py-2.5 rounded-xl text-xs transition-all shadow-sm">
                    <i data-lucide="mail-open" class="w-3.5 h-3.5"></i>
                    <span>Send Notice / Salary Slip</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tabs Content Container -->
    <div class="lg:col-span-3">
        <!-- Tabs Header Navigation -->
        <div class="bg-zinc-100 p-1.5 rounded-xl flex flex-wrap gap-1 mb-6 border border-zinc-200">
            <button @click="activeTab = 'profile'" 
                    :class="activeTab === 'profile' ? 'bg-white text-zinc-900 font-bold shadow-sm' : 'text-zinc-500 hover:text-zinc-800 font-semibold'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i>
                <span>Profile Info</span>
            </button>
            <button @click="activeTab = 'earnings'" 
                    :class="activeTab === 'earnings' ? 'bg-white text-zinc-900 font-bold shadow-sm' : 'text-zinc-500 hover:text-zinc-800 font-semibold'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                <span>Earnings History</span>
            </button>
            <button @click="activeTab = 'notices'" 
                    :class="activeTab === 'notices' ? 'bg-white text-zinc-900 font-bold shadow-sm' : 'text-zinc-500 hover:text-zinc-800 font-semibold'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                <span>Notices Sent</span>
            </button>
            <button @click="activeTab = 'logs'" 
                    :class="activeTab === 'logs' ? 'bg-white text-zinc-900 font-bold shadow-sm' : 'text-zinc-500 hover:text-zinc-800 font-semibold'"
                    class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
                <i data-lucide="history" class="w-4 h-4"></i>
                <span>Activity Log</span>
            </button>
        </div>

        <!-- Tab 1 — Profile Info -->
        <div x-show="activeTab === 'profile'" class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm space-y-6">
            <div>
                <h3 class="font-bold text-zinc-800 text-base mb-1">Administrative Details</h3>
                <p class="text-xs text-zinc-400">Official employment documents and tracking references.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-zinc-100 pb-6">
                <div>
                    <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block mb-1">Designation</label>
                    <span class="text-sm font-semibold text-zinc-800 block p-3 bg-zinc-50 rounded-xl border border-zinc-150">
                        {{ $member->staffProfile->designation ?? 'Staff Member' }}
                    </span>
                </div>
                <div>
                    <label class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block mb-1">Appointment Letter Ref</label>
                    <span class="text-sm font-semibold text-zinc-800 block p-3 bg-zinc-50 rounded-xl border border-zinc-150">
                        {{ $member->staffProfile->appointment_letter_ref ?? 'Pending Upload' }}
                    </span>
                </div>
            </div>

            <!-- Identity verification masked -->
            <div class="space-y-4 border-b border-zinc-100 pb-6">
                <h4 class="font-bold text-zinc-800 text-sm">Government Issued IDs</h4>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Aadhaar -->
                    <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-150 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block mb-0.5">Aadhaar Number</span>
                            <span class="text-sm font-bold text-zinc-800 block tracking-widest font-mono" x-text="aadhaarMasked ? '•••• •••• ••••' : '{{ $member->staffProfile->aadhaar_number ?? 'Not Provided' }}'"></span>
                        </div>
                        @if($member->staffProfile->aadhaar_number)
                        <button @click="aadhaarMasked = !aadhaarMasked" class="text-xs font-bold text-[#09090b] hover:text-zinc-650 bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
                            <span x-text="aadhaarMasked ? 'Reveal' : 'Hide'"></span>
                        </button>
                        @endif
                    </div>

                    <!-- PAN -->
                    <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-150 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block mb-0.5">PAN Card Number</span>
                            <span class="text-sm font-bold text-zinc-800 block tracking-widest font-mono" x-text="panMasked ? '•••••• ••••' : '{{ $member->staffProfile->pan_number ?? 'Not Provided' }}'"></span>
                        </div>
                        @if($member->staffProfile->pan_number)
                        <button @click="panMasked = !panMasked" class="text-xs font-bold text-[#09090b] hover:text-zinc-650 bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
                            <span x-text="panMasked ? 'Reveal' : 'Hide'"></span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bank / UPI payment details -->
            <div class="space-y-4">
                <h4 class="font-bold text-zinc-800 text-sm">Disbursement Details (Bank & UPI)</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bank Account Info -->
                    <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-150 flex items-center justify-between">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Bank Details</span>
                            <div class="text-xs font-semibold text-zinc-800" x-show="!bankMasked">
                                <p><strong>Bank:</strong> {{ $member->staffProfile->bank_name ?? 'N/A' }}</p>
                                <p><strong>A/C:</strong> {{ $member->staffProfile->bank_account_number ?? 'N/A' }}</p>
                                <p><strong>IFSC:</strong> {{ $member->staffProfile->ifsc_code ?? 'N/A' }}</p>
                            </div>
                            <span class="text-sm font-bold text-zinc-800 block tracking-widest" x-show="bankMasked">•••• •••• ••••</span>
                        </div>
                        @if($member->staffProfile->bank_account_number)
                        <button @click="bankMasked = !bankMasked" class="text-xs font-bold text-[#09090b] hover:text-zinc-650 bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
                            <span x-text="bankMasked ? 'Reveal' : 'Hide'"></span>
                        </button>
                        @endif
                    </div>

                    <!-- UPI Info -->
                    <div class="p-4 bg-zinc-50 rounded-xl border border-zinc-150 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block mb-0.5">UPI ID</span>
                            <span class="text-sm font-bold text-zinc-800 block tracking-widest" x-text="upiMasked ? '••••••••••' : '{{ $member->staffProfile->upi_id ?? 'Not Provided' }}'"></span>
                        </div>
                        @if($member->staffProfile->upi_id)
                        <button @click="upiMasked = !upiMasked" class="text-xs font-bold text-[#09090b] hover:text-zinc-650 bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
                            <span x-text="upiMasked ? 'Reveal' : 'Hide'"></span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2 — Earnings History -->
        <div x-show="activeTab === 'earnings'" class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="font-bold text-zinc-800 text-base mb-1">Platform Earnings & Commissions</h3>
                    <p class="text-xs text-zinc-400">Commission summaries aggregated month-by-month.</p>
                </div>
                <form action="{{ route('admin.staff.show', $member->id) }}" method="GET" class="flex items-center gap-2">
                    <input type="month" name="month_year" value="{{ request('month_year') }}"
                           class="p-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700">
                    @if(request()->filled('month_year'))
                        <a href="{{ route('admin.staff.show', $member->id) }}" class="px-3 py-2 border border-zinc-200 text-zinc-650 hover:bg-zinc-50 rounded-xl text-xs font-semibold">
                            Reset
                        </a>
                    @endif
                </form>
            </div>

            <!-- Month Table -->
            <div class="overflow-hidden border border-zinc-200 rounded-xl">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-[10px] uppercase tracking-wider text-zinc-400 font-bold border-b border-zinc-200">
                        <tr>
                            <th class="px-6 py-3">Month</th>
                            <th class="px-6 py-3">Orders Count</th>
                            <th class="px-6 py-3">Gross</th>
                            <th class="px-6 py-3">Expenses</th>
                            <th class="px-6 py-3">Net Earning</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Breakdown</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-150">
                        @forelse($monthlySummaries as $monthKey => $sum)
                        <tr class="hover:bg-zinc-50/50 transition-all font-semibold">
                            <td class="px-6 py-3.5 text-zinc-800 text-xs">{{ $sum['month_name'] }}</td>
                            <td class="px-6 py-3.5 text-zinc-600 text-xs">{{ $sum['orders_count'] }} orders</td>
                            <td class="px-6 py-3.5 text-zinc-800 text-xs">₹{{ number_format($sum['gross'], 2) }}</td>
                            <td class="px-6 py-3.5 text-zinc-500 text-xs">₹{{ number_format($sum['expenses'], 2) }}</td>
                            <td class="px-6 py-3.5 text-[#09090b] font-bold text-xs bg-zinc-50">₹{{ number_format($sum['net'], 2) }}</td>
                            <td class="px-6 py-3.5">
                                <span class="px-2.5 py-0.5 text-[9px] rounded-full font-bold
                                    @if($sum['status'] === 'paid') bg-green-150 text-green-800
                                    @else bg-zinc-100 text-zinc-600 @endif border border-black/5">
                                    {{ ucfirst($sum['status']) }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right">
                                <button @click="expandedMonths['{{ $sum['month'] }}'] = !expandedMonths['{{ $sum['month'] }}']" 
                                        class="text-xs font-bold bg-zinc-100 hover:bg-zinc-900 text-white hover:bg-zinc-800 text-zinc-800 px-3 py-1 rounded-lg transition-all border border-zinc-200">
                                    <span x-text="expandedMonths['{{ $sum['month'] }}'] ? 'Collapse' : 'Expand'"></span>
                                </button>
                            </td>
                        </tr>

                        <!-- Nested Expandable Order Details -->
                        <tr x-show="expandedMonths['{{ $sum['month'] }}']" class="bg-zinc-50">
                            <td colspan="7" class="p-4 border-t border-b border-zinc-200">
                                <div class="overflow-x-auto rounded-lg border border-zinc-200 shadow-inner bg-white">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-zinc-100 text-[9px] uppercase tracking-wider text-zinc-500 font-bold border-b border-zinc-200">
                                            <tr>
                                                <th class="px-4 py-2">Order #</th>
                                                <th class="px-4 py-2">Date</th>
                                                <th class="px-4 py-2">Category</th>
                                                <th class="px-4 py-2">Sale Value</th>
                                                <th class="px-4 py-2">Commission %</th>
                                                <th class="px-4 py-2">Platform Expense</th>
                                                <th class="px-4 py-2 text-right">Net Earning</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-zinc-100">
                                            @foreach($sum['orders_list'] as $ord)
                                            <tr>
                                                <td class="px-4 py-2.5 font-bold text-zinc-800">#{{ $ord->order_number }}</td>
                                                <td class="px-4 py-2.5 text-zinc-500">{{ $ord->created_at->format('d M, H:i') }}</td>
                                                <td class="px-4 py-2.5 text-zinc-650">{{ strtoupper($ord->listing->category ?? 'GPU') }}</td>
                                                <td class="px-4 py-2.5 font-semibold text-zinc-700">₹{{ number_format($ord->total_amount, 2) }}</td>
                                                <td class="px-4 py-2.5 text-zinc-500">{{ $ord->commission_percent }}%</td>
                                                <td class="px-4 py-2.5 text-zinc-500">₹{{ number_format($ord->expenses, 2) }}</td>
                                                <td class="px-4 py-2.5 font-bold text-[#16a34a] text-right">₹{{ number_format($ord->net, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-10 h-10 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-450 mb-2 border border-zinc-200">
                                        <i data-lucide="wallet" class="w-5 h-5"></i>
                                    </div>
                                    <h4 class="text-xs font-bold text-zinc-800">No Earnings Recorded</h4>
                                    <p class="text-[11px] text-zinc-400 mt-0.5">This staff member hasn't handled any commissionable orders yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab 3 — Notices Sent -->
        <div x-show="activeTab === 'notices'" class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm space-y-6">
            <div>
                <h3 class="font-bold text-zinc-800 text-base mb-1">Notices & Salary Slips</h3>
                <p class="text-xs text-zinc-400">All formal communications and salary slips sent to this employee.</p>
            </div>

            <div class="space-y-4">
                @forelse($notices as $notice)
                <div class="border border-zinc-200 rounded-xl p-4 bg-zinc-50 hover:bg-zinc-100 transition-all flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-zinc-50 text-zinc-650 border border-yellow-250">
                            <i data-lucide="{{ $notice->type === 'Salary Slip' ? 'file-text' : 'alert-circle' }}" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <span class="px-2 py-0.5 text-[9px] rounded-full font-bold bg-white text-zinc-500 border border-zinc-200">
                                {{ $notice->type }}
                            </span>
                            <h4 class="font-bold text-zinc-800 text-xs mt-1.5">{{ $notice->subject }}</h4>
                            <p class="text-[10px] text-zinc-400 mt-0.5">Sent via {{ ucfirst($notice->delivery_method) }} on {{ $notice->created_at->format('d M, Y \a\t H:i') }}</p>
                            
                            <div class="text-xs text-zinc-600 mt-2 bg-white border border-zinc-150 p-3 rounded-lg max-w-xl">
                                {!! $notice->message !!}
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 self-end md:self-center">
                        @if($notice->attachment_path)
                        <a href="{{ asset('storage/' . $notice->attachment_path) }}" target="_blank" 
                           class="inline-flex items-center gap-1 text-xs font-bold bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-zinc-800 hover:text-white px-3 py-1.5 rounded-lg transition-all shadow-sm">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>View Attachment</span>
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="px-6 py-12 text-center border border-zinc-200 rounded-xl bg-zinc-50/50">
                    <div class="w-10 h-10 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-450 mx-auto mb-2 border border-zinc-200">
                        <i data-lucide="mail-search" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-xs font-bold text-zinc-800">No Communications Records</h4>
                    <p class="text-[11px] text-zinc-400 mt-0.5">No formal warnings, notices, or slips dispatched yet.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Tab 4 — Activity Log -->
        <div x-show="activeTab === 'logs'" class="bg-white border border-zinc-200 rounded-xl p-6 shadow-sm space-y-6">
            <div>
                <h3 class="font-bold text-zinc-800 text-base mb-1">Administrative Action Logs</h3>
                <p class="text-xs text-zinc-400">Chronological history of platform audits triggered by this user account.</p>
            </div>

            <div class="relative pl-6 border-l-2 border-zinc-100 space-y-6">
                @forelse($logs as $log)
                <div class="relative">
                    <span class="absolute -left-[31px] top-1.5 w-2.5 h-2.5 rounded-full bg-zinc-900 text-white hover:bg-zinc-800 border-2 border-white ring-4 ring-[#09090b]/20"></span>
                    <span class="text-[10px] text-zinc-400 font-bold block">{{ $log->created_at->format('d M, Y \a\t H:i:s') }}</span>
                    <h5 class="font-bold text-zinc-800 text-xs mt-0.5">{{ ucwords(str_replace('_', ' ', $log->action)) }}</h5>
                    <p class="text-[11px] text-zinc-500 mt-0.5 font-medium">{{ $log->description }}</p>
                    <span class="text-[9px] text-zinc-400 bg-zinc-100 font-semibold px-2 py-0.5 rounded-full mt-1.5 inline-block">IP: {{ $log->ip_address }}</span>
                </div>
                @empty
                <div class="px-6 py-12 text-center border border-zinc-200 rounded-xl bg-zinc-50/50 -ml-6">
                    <div class="w-10 h-10 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-450 mx-auto mb-2 border border-zinc-200">
                        <i data-lucide="shield-alert" class="w-5 h-5"></i>
                    </div>
                    <h4 class="text-xs font-bold text-zinc-800">No Actions Logged</h4>
                    <p class="text-[11px] text-zinc-400 mt-0.5">This staff account has not recorded any operations yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
