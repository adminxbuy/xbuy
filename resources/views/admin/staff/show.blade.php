@extends('layouts.admin')

@section('title', 'Staff Profile - ' . $member->name)
@section('page_title', 'Staff Profile: ' . $member->name)

@section('header_actions')
 <a href="{{ route('admin.staff.directory') }}" class="flex items-center space-x-1.5 px-3 py-1.5 bg-muted hover:bg-muted text-foreground hover:text-foreground rounded-lg text-xs font-semibold transition-all border border-border ">
 <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
 <span>Back to Directory</span>
 </a>
@endsection

@section('content')
<div x-data="{  activeTab: 'profile',
 bankMasked: true,
 upiMasked: true,
 aadhaarMasked: true,
 panMasked: true,
 expandedMonths: {}
}" class="grid grid-cols-1 lg:grid-cols-4 gap-6">

 <!-- Sidebar Info Card -->
 <div class="lg:col-span-1 space-y-6">
 <div class="bg-card border border-border rounded-xl p-6 text-center relative overflow-hidden">
 <div class="absolute top-0 left-0 w-full h-2 bg-primary text-primary-foreground hover:bg-primary/90"></div>
 <img src="https://ui-avatars.com/api/?name={{ urlencode($member->name) }}&background=e4e4e7&color=71717a&size=96"  class="w-24 h-24 rounded-xl mx-auto border-2 border-border mb-4" alt="avatar">
  <h3 class="font-bold text-foreground text-lg leading-tight">{{ $member->name }}</h3>
 <p class="text-xs font-semibold text-muted-foreground mt-0.5">{{ $member->staffProfile->designation ?? 'Staff Member' }}</p>
  <span class="inline-block mt-3 px-3 py-1 text-[10px] rounded-full font-bold
 @if($member->status === 'active') bg-muted text-foreground border border-border/60
 @elseif($member->status === 'suspended') bg-muted text-foreground
 @else bg-muted text-foreground border border-border/60 @endif border border-black/5">
 {{ ucfirst($member->status) }}
 </span>

 <div class="border-t border-border mt-6 pt-5 text-left text-xs space-y-3">
 <div class="flex justify-between">
 <span class="text-muted-foreground">Email</span>
 <span class="font-bold text-foreground">{{ $member->email }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-muted-foreground">Phone</span>
 <span class="font-bold text-foreground">{{ $member->phone ?? 'N/A' }}</span>
 </div>
 <div class="flex justify-between">
 <span class="text-muted-foreground">Joined Date</span>
 <span class="font-bold text-foreground">
 {{ $member->staffProfile->joined_at ? $member->staffProfile->joined_at->format('d M, Y') : $member->created_at->format('d M, Y') }}
 </span>
 </div>
 </div>
  <div class="mt-6 flex flex-col gap-2">
 <a href="{{ route('admin.staff.notice', $member->id) }}"  class="w-full inline-flex items-center justify-center gap-1.5 bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground font-bold py-2.5 rounded-xl text-xs transition-all ">
 <i data-lucide="mail-open" class="w-3.5 h-3.5"></i>
 <span>Send Notice / Salary Slip</span>
 </a>
 </div>
 </div>
 </div>

 <!-- Tabs Content Container -->
 <div class="lg:col-span-3">
 <!-- Tabs Header Navigation -->
 <div class="bg-muted p-1.5 rounded-xl flex flex-wrap gap-1 mb-6 border border-border">
 <button @click="activeTab = 'profile'"  :class="activeTab === 'profile' ? 'bg-card text-foreground font-bold ' : 'text-muted-foreground hover:text-foreground font-semibold'"
 class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
 <i data-lucide="user" class="w-4 h-4"></i>
 <span>Profile Info</span>
 </button>
 <button @click="activeTab = 'earnings'"  :class="activeTab === 'earnings' ? 'bg-card text-foreground font-bold ' : 'text-muted-foreground hover:text-foreground font-semibold'"
 class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
 <i data-lucide="wallet" class="w-4 h-4"></i>
 <span>Earnings History</span>
 </button>
 <button @click="activeTab = 'notices'"  :class="activeTab === 'notices' ? 'bg-card text-foreground font-bold ' : 'text-muted-foreground hover:text-foreground font-semibold'"
 class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
 <i data-lucide="clipboard-list" class="w-4 h-4"></i>
 <span>Notices Sent</span>
 </button>
 <button @click="activeTab = 'logs'"  :class="activeTab === 'logs' ? 'bg-card text-foreground font-bold ' : 'text-muted-foreground hover:text-foreground font-semibold'"
 class="flex-1 px-4 py-2.5 rounded-xl text-xs transition-all flex items-center justify-center gap-2">
 <i data-lucide="history" class="w-4 h-4"></i>
 <span>Activity Log</span>
 </button>
 </div>

 <!-- Tab 1 — Profile Info -->
 <div x-show="activeTab === 'profile'" class="bg-card border border-border rounded-xl p-6 space-y-6">
 <div>
 <h3 class="font-bold text-foreground text-base mb-1">Administrative Details</h3>
 <p class="text-xs text-muted-foreground">Official employment documents and tracking references.</p>
 </div>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-b border-border pb-6">
 <div>
 <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-1">Designation</label>
 <span class="text-sm font-semibold text-foreground block p-3 bg-muted rounded-xl border border-border">
 {{ $member->staffProfile->designation ?? 'Staff Member' }}
 </span>
 </div>
 <div>
 <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-1">Appointment Letter Ref</label>
 <span class="text-sm font-semibold text-foreground block p-3 bg-muted rounded-xl border border-border">
 {{ $member->staffProfile->appointment_letter_ref ?? 'Pending Upload' }}
 </span>
 </div>
 </div>

 <!-- Identity verification masked -->
 <div class="space-y-4 border-b border-border pb-6">
 <h4 class="font-bold text-foreground text-sm">Government Issued IDs</h4>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Aadhaar -->
 <div class="p-4 bg-muted rounded-xl border border-border flex items-center justify-between">
 <div>
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">Aadhaar Number</span>
 <span class="text-sm font-bold text-foreground block tracking-widest font-mono" x-text="aadhaarMasked ? '•••• •••• ••••' : '{{ $member->staffProfile->aadhaar_number ?? 'Not Provided' }}'"></span>
 </div>
 @if($member->staffProfile->aadhaar_number)
 <button @click="aadhaarMasked = !aadhaarMasked" class="text-xs font-bold text-foreground hover:text-muted-foreground bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
 <span x-text="aadhaarMasked ? 'Reveal' : 'Hide'"></span>
 </button>
 @endif
 </div>

 <!-- PAN -->
 <div class="p-4 bg-muted rounded-xl border border-border flex items-center justify-between">
 <div>
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">PAN Card Number</span>
 <span class="text-sm font-bold text-foreground block tracking-widest font-mono" x-text="panMasked ? '•••••• ••••' : '{{ $member->staffProfile->pan_number ?? 'Not Provided' }}'"></span>
 </div>
 @if($member->staffProfile->pan_number)
 <button @click="panMasked = !panMasked" class="text-xs font-bold text-foreground hover:text-muted-foreground bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
 <span x-text="panMasked ? 'Reveal' : 'Hide'"></span>
 </button>
 @endif
 </div>
 </div>
 </div>

 <!-- Bank / UPI payment details -->
 <div class="space-y-4">
 <h4 class="font-bold text-foreground text-sm">Disbursement Details (Bank & UPI)</h4>

 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
 <!-- Bank Account Info -->
 <div class="p-4 bg-muted rounded-xl border border-border flex items-center justify-between">
 <div class="space-y-1">
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Bank Details</span>
 <div class="text-xs font-semibold text-foreground" x-show="!bankMasked">
 <p><strong>Bank:</strong> {{ $member->staffProfile->bank_name ?? 'N/A' }}</p>
 <p><strong>A/C:</strong> {{ $member->staffProfile->bank_account_number ?? 'N/A' }}</p>
 <p><strong>IFSC:</strong> {{ $member->staffProfile->ifsc_code ?? 'N/A' }}</p>
 </div>
 <span class="text-sm font-bold text-foreground block tracking-widest" x-show="bankMasked">•••• •••• ••••</span>
 </div>
 @if($member->staffProfile->bank_account_number)
 <button @click="bankMasked = !bankMasked" class="text-xs font-bold text-foreground hover:text-muted-foreground bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
 <span x-text="bankMasked ? 'Reveal' : 'Hide'"></span>
 </button>
 @endif
 </div>

 <!-- UPI Info -->
 <div class="p-4 bg-muted rounded-xl border border-border flex items-center justify-between">
 <div>
 <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block mb-0.5">UPI ID</span>
 <span class="text-sm font-bold text-foreground block tracking-widest" x-text="upiMasked ? '••••••••••' : '{{ $member->staffProfile->upi_id ?? 'Not Provided' }}'"></span>
 </div>
 @if($member->staffProfile->upi_id)
 <button @click="upiMasked = !upiMasked" class="text-xs font-bold text-foreground hover:text-muted-foreground bg-black/5 hover:bg-black/10 px-3 py-1.5 rounded-lg transition-all">
 <span x-text="upiMasked ? 'Reveal' : 'Hide'"></span>
 </button>
 @endif
 </div>
 </div>
 </div>
 </div>

 <!-- Tab 2 — Earnings History -->
 <div x-show="activeTab === 'earnings'" class="bg-card border border-border rounded-xl p-6 space-y-6">
 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
 <div>
 <h3 class="font-bold text-foreground text-base mb-1">Platform Earnings & Commissions</h3>
 <p class="text-xs text-muted-foreground">Commission summaries aggregated month-by-month.</p>
 </div>
 <form action="{{ route('admin.staff.show', $member->id) }}" method="GET" class="flex items-center gap-2">
 <input type="month" name="month_year" value="{{ request('month_year') }}"
 class="p-2.5 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:outline-none transition-all cursor-pointer font-semibold text-foreground">
 @if(request()->filled('month_year'))
 <a href="{{ route('admin.staff.show', $member->id) }}" class="px-3 py-2 border border-border text-muted-foreground hover:bg-muted rounded-xl text-xs font-semibold">
 Reset
 </a>
 @endif
 </form>
 </div>

 <!-- Month Table -->
 <div class="overflow-hidden border border-border rounded-xl">
 <table class="w-full text-left text-sm">
 <thead class="bg-muted text-xs uppercase tracking-wider text-muted-foreground font-medium border-b border-border">
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
 <tbody class="divide-y divide-border">
 @forelse($monthlySummaries as $monthKey => $sum)
 <tr class="hover:bg-muted transition-all font-semibold">
 <td class="px-6 py-3.5 text-foreground text-xs">{{ $sum['month_name'] }}</td>
 <td class="px-6 py-3.5 text-muted-foreground text-xs">{{ $sum['orders_count'] }} orders</td>
 <td class="px-6 py-3.5 text-foreground text-xs">₹{{ number_format($sum['gross'], 2) }}</td>
 <td class="px-6 py-3.5 text-muted-foreground text-xs">₹{{ number_format($sum['expenses'], 2) }}</td>
 <td class="px-6 py-3.5 text-foreground font-bold text-xs bg-muted">₹{{ number_format($sum['net'], 2) }}</td>
 <td class="px-6 py-3.5">
 <span class="px-2.5 py-0.5 text-[9px] rounded-full font-bold
 @if($sum['status'] === 'paid') bg-green-150 text-green-800
 @else bg-muted text-muted-foreground @endif border border-black/5">
 {{ ucfirst($sum['status']) }}
 </span>
 </td>
 <td class="px-6 py-3.5 text-right">
 <button @click="expandedMonths['{{ $sum['month'] }}'] = !expandedMonths['{{ $sum['month'] }}']"  class="text-xs font-bold bg-muted hover:bg-primary text-primary-foreground hover:bg-primary/90 text-foreground px-3 py-1 rounded-lg transition-all border border-border">
 <span x-text="expandedMonths['{{ $sum['month'] }}'] ? 'Collapse' : 'Expand'"></span>
 </button>
 </td>
 </tr>

 <!-- Nested Expandable Order Details -->
 <tr x-show="expandedMonths['{{ $sum['month'] }}']" class="bg-muted">
 <td colspan="7" class="p-4 border-t border-b border-border">
 <div class="overflow-x-auto rounded-lg border border-border shadow-inner bg-card">
 <table class="w-full text-left text-xs">
 <thead class="bg-muted text-[9px] uppercase tracking-wider text-muted-foreground font-medium border-b border-border">
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
 <tbody class="divide-y divide-border">
 @foreach($sum['orders_list'] as $ord)
 <tr>
 <td class="px-4 py-2.5 font-bold text-foreground">#{{ $ord->order_number }}</td>
 <td class="px-4 py-2.5 text-muted-foreground">{{ $ord->created_at->format('d M, H:i') }}</td>
 <td class="px-4 py-2.5 text-muted-foreground">{{ strtoupper($ord->listing->category ?? 'GPU') }}</td>
 <td class="px-4 py-2.5 font-semibold text-foreground">₹{{ number_format($ord->total_amount, 2) }}</td>
 <td class="px-4 py-2.5 text-muted-foreground">{{ $ord->commission_percent }}%</td>
 <td class="px-4 py-2.5 text-muted-foreground">₹{{ number_format($ord->expenses, 2) }}</td>
 <td class="px-4 py-2.5 font-bold text-muted-foreground text-right">₹{{ number_format($ord->net, 2) }}</td>
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
 <div class="w-10 h-10 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mb-2 border border-border">
 <i data-lucide="wallet" class="w-5 h-5"></i>
 </div>
 <h4 class="text-xs font-bold text-foreground">No Earnings Recorded</h4>
 <p class="text-[11px] text-muted-foreground mt-0.5">This staff member hasn't handled any commissionable orders yet.</p>
 </div>
 </td>
 </tr>
 @endforelse
 </tbody>
 </table>
 </div>
 </div>

 <!-- Tab 3 — Notices Sent -->
 <div x-show="activeTab === 'notices'" class="bg-card border border-border rounded-xl p-6 space-y-6">
 <div>
 <h3 class="font-bold text-foreground text-base mb-1">Notices & Salary Slips</h3>
 <p class="text-xs text-muted-foreground">All formal communications and salary slips sent to this employee.</p>
 </div>

 <div class="space-y-4">
 @forelse($notices as $notice)
 <div class="border border-border rounded-xl p-4 bg-muted hover:bg-muted transition-all flex flex-col md:flex-row md:items-center md:justify-between gap-4">
 <div class="flex items-start gap-3">
 <div class="p-2 rounded-lg bg-muted text-muted-foreground border border-yellow-250">
 <i data-lucide="{{ $notice->type === 'Salary Slip' ? 'file-text' : 'alert-circle' }}" class="w-5 h-5"></i>
 </div>
 <div>
 <span class="px-2 py-0.5 text-[9px] rounded-full font-bold bg-card text-muted-foreground border border-border">
 {{ $notice->type }}
 </span>
 <h4 class="font-bold text-foreground text-xs mt-1.5">{{ $notice->subject }}</h4>
 <p class="text-[10px] text-muted-foreground mt-0.5">Sent via {{ ucfirst($notice->delivery_method) }} on {{ $notice->created_at->format('d M, Y \a\t H:i') }}</p>
  <div class="text-xs text-muted-foreground mt-2 bg-card border border-border p-3 rounded-lg max-w-xl">
 {!! $notice->message !!}
 </div>
 </div>
 </div>
 <div class="flex items-center gap-2 self-end md:self-center">
 @if($notice->attachment_path)
 <a href="{{ asset('storage/' . $notice->attachment_path) }}" target="_blank"  class="inline-flex items-center gap-1 text-xs font-bold bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground px-3 py-1.5 rounded-lg transition-all ">
 <i data-lucide="download" class="w-3.5 h-3.5"></i>
 <span>View Attachment</span>
 </a>
 @endif
 </div>
 </div>
 @empty
 <div class="px-6 py-12 text-center border border-border rounded-xl bg-muted">
 <div class="w-10 h-10 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mx-auto mb-2 border border-border">
 <i data-lucide="mail-search" class="w-5 h-5"></i>
 </div>
 <h4 class="text-xs font-bold text-foreground">No Communications Records</h4>
 <p class="text-[11px] text-muted-foreground mt-0.5">No formal warnings, notices, or slips dispatched yet.</p>
 </div>
 @endforelse
 </div>
 </div>

 <!-- Tab 4 — Activity Log -->
 <div x-show="activeTab === 'logs'" class="bg-card border border-border rounded-xl p-6 space-y-6">
 <div>
 <h3 class="font-bold text-foreground text-base mb-1">Administrative Action Logs</h3>
 <p class="text-xs text-muted-foreground">Chronological history of platform audits triggered by this user account.</p>
 </div>

 <div class="relative pl-6 border-l-2 border-border space-y-6">
 @forelse($logs as $log)
 <div class="relative">
 <span class="absolute -left-[31px] top-1.5 w-2.5 h-2.5 rounded-full bg-primary text-primary-foreground hover:bg-primary/90 border-2 border-card ring-4 ring-foreground/20"></span>
 <span class="text-[10px] text-muted-foreground font-bold block">{{ $log->created_at->format('d M, Y \a\t H:i:s') }}</span>
 <h5 class="font-bold text-foreground text-xs mt-0.5">{{ ucwords(str_replace('_', ' ', $log->action)) }}</h5>
 <p class="text-[11px] text-muted-foreground mt-0.5 font-medium">{{ $log->description }}</p>
 <span class="text-[9px] text-muted-foreground bg-muted font-semibold px-2 py-0.5 rounded-full mt-1.5 inline-block">IP: {{ $log->ip_address }}</span>
 </div>
 @empty
 <div class="px-6 py-12 text-center border border-border rounded-xl bg-muted -ml-6">
 <div class="w-10 h-10 bg-muted rounded-xl flex items-center justify-center text-muted-foreground mx-auto mb-2 border border-border">
 <i data-lucide="shield-alert" class="w-5 h-5"></i>
 </div>
 <h4 class="text-xs font-bold text-foreground">No Actions Logged</h4>
 <p class="text-[11px] text-muted-foreground mt-0.5">This staff account has not recorded any operations yet.</p>
 </div>
 @endforelse
 </div>
 </div>
 </div>
</div>
@endsection
