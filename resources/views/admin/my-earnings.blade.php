@extends('layouts.admin')

@section('title', 'My Earnings')
@section('page_title', 'My Earning Reports')

@section('header_actions')
    <a href="{{ route('admin.my-earnings.print') }}?month_year={{ request('month_year') }}" target="_blank"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-zinc-800 hover:text-white rounded-lg text-xs font-bold transition-all shadow-sm">
        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
        <span>Download report (PDF)</span>
    </a>
@endsection

@section('content')
<!-- Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Card 1: This Month -->
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col justify-between relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-1 bg-zinc-900 text-white hover:bg-zinc-800"></div>
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">This Month Earnings</span>
            <div class="p-2 bg-zinc-50 text-zinc-650 rounded-lg">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-3">₹{{ number_format($thisMonthEarned, 2) }}</h3>
        <p class="text-[10px] text-zinc-400 mt-1">Earning generated since 1st of month</p>
    </div>

    <!-- Card 2: Last Month -->
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Last Month Earnings</span>
            <div class="p-2 bg-zinc-50 text-zinc-650 rounded-lg border border-zinc-250">
                <i data-lucide="wallet" class="w-4 h-4"></i>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-3">₹{{ number_format($lastMonthEarned, 2) }}</h3>
        <p class="text-[10px] text-zinc-400 mt-1">Disbursed amount for previous cycle</p>
    </div>

    <!-- Card 3: Total All Time -->
    <div class="bg-white border border-zinc-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Total Earning All Time</span>
            <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                <i data-lucide="award" class="w-4 h-4"></i>
            </div>
        </div>
        <h3 class="text-2xl font-bold text-zinc-900 mt-3">₹{{ number_format($totalEarned, 2) }}</h3>
        <p class="text-[10px] text-zinc-400 mt-1">Lifetime total earnings on platform</p>
    </div>
</div>

<!-- Commissions Table -->
<div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-zinc-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="font-bold text-zinc-800 text-base">Order Commission Records</h3>
            <p class="text-xs text-zinc-400">Detailed list of commissions generated per order.</p>
        </div>
        
        <form action="{{ route('admin.my-earnings') }}" method="GET" class="flex items-center gap-2">
            <input type="month" name="month_year" value="{{ request('month_year') }}"
                   class="p-2.5 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:outline-none transition-all cursor-pointer font-semibold text-zinc-700">
            @if(request()->filled('month_year'))
                <a href="{{ route('admin.my-earnings') }}" class="px-3 py-2 border border-zinc-200 text-zinc-650 hover:bg-zinc-50 rounded-xl text-xs font-semibold">
                    Clear Filter
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-[10px] uppercase tracking-wider text-zinc-400 font-bold border-b border-zinc-200">
                <tr>
                    <th class="px-6 py-4">Order #</th>
                    <th class="px-6 py-4">Sale Value</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Commission %</th>
                    <th class="px-6 py-4">Platform Expenses</th>
                    <th class="px-6 py-4">Your Earning</th>
                    <th class="px-6 py-4 text-right">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100">
                @forelse($filteredOrders as $order)
                <tr class="hover:bg-zinc-50/50 transition-all font-semibold">
                    <td class="px-6 py-4 font-bold text-zinc-800 text-xs">
                        #{{ $order->order_number }}
                    </td>
                    <td class="px-6 py-4 text-zinc-700 text-xs">
                        ₹{{ number_format($order->total_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-zinc-650">
                        <span class="px-2 py-0.5 text-[9px] rounded-full font-bold bg-zinc-100 text-zinc-550 border border-zinc-200">
                            {{ strtoupper($order->listing->category ?? 'GPU') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-zinc-500 text-xs">
                        {{ $order->commission_percent }}%
                    </td>
                    <td class="px-6 py-4 text-zinc-500 text-xs">
                        ₹{{ number_format($order->expenses, 2) }}
                    </td>
                    <td class="px-6 py-4 text-[#16a34a] font-bold text-xs bg-emerald-500/5">
                        ₹{{ number_format($order->net, 2) }}
                    </td>
                    <td class="px-6 py-4 text-right text-zinc-400 text-xs">
                        {{ $order->created_at->format('d M, Y H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-10 h-10 bg-zinc-50 rounded-xl flex items-center justify-center text-zinc-450 mb-2 border border-zinc-200">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-xs font-bold text-zinc-800">No Orders Found</h4>
                            <p class="text-[11px] text-zinc-400 mt-0.5">You have not been assigned any orders in this cycle.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Downloadable Salary Slips -->
<div class="bg-white border border-zinc-200 rounded-xl ring-1 ring-zinc-950/5 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-zinc-100">
        <h3 class="font-bold text-zinc-800 text-base">Downloadable Salary Slips</h3>
        <p class="text-xs text-zinc-400">Access and download your official generated monthly salary slips in PDF format.</p>
    </div>

    <div class="p-6">
        @if($salarySlips->isEmpty())
            <div class="text-center py-6 text-zinc-400 text-xs font-semibold">
                <i data-lucide="file-text" class="w-8 h-8 mx-auto mb-2 text-zinc-350" style="margin-left: auto; margin-right: auto;"></i>
                <p>No salary slips generated yet. Slips will appear here once disbursed by admin.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($salarySlips as $slip)
                <div class="border border-zinc-200 rounded-xl p-4 bg-zinc-50 hover:bg-zinc-100 transition-all flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-zinc-50 text-zinc-650 rounded-lg border border-zinc-200">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-zinc-850 text-xs">{{ $slip->subject }}</h4>
                            <p class="text-[10px] text-zinc-400 mt-0.5 font-semibold">Disbursed on {{ $slip->created_at->format('d M, Y') }}</p>
                        </div>
                    </div>
                    @if($slip->pdf_attachment)
                    <a href="{{ asset('storage/' . $slip->pdf_attachment) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-zinc-800 hover:text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Download PDF</span>
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
