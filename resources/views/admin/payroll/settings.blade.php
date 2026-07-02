@extends('layouts.admin')

@section('title', 'Payroll Settings')
@section('page_title', 'Payroll Settings')

@section('content')
<div class="max-w-3xl bg-white border border-zinc-200 rounded-xl p-6 shadow-sm">
    <div class="mb-6 border-b border-zinc-100 pb-5">
        <h3 class="font-bold text-zinc-800 text-base">Global Payroll settings</h3>
        <p class="text-xs text-zinc-400">Configure global parameters used for staff order commission and disbursement calculations.</p>
    </div>

    <form action="{{ route('admin.payroll.settings.save') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Commission Percent -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Commission Percentage (%)</label>
                <div class="relative">
                    <input type="number" step="0.1" name="commission_percent" value="{{ $settings['commission_percent'] }}" min="0" max="100" required
                           class="w-full p-3 pr-10 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all font-semibold text-zinc-800">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">%</span>
                </div>
            </div>

            <!-- Platform Expense -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Platform Expense per Order (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">₹</span>
                    <input type="number" name="platform_expense" value="{{ $settings['platform_expense'] }}" min="0" required
                           class="w-full pl-7 pr-4 p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all font-semibold text-zinc-800">
                </div>
            </div>

            <!-- Min Cap -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Minimum Cap per Order (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">₹</span>
                    <input type="number" name="min_cap" value="{{ $settings['min_cap'] }}" min="0" required
                           class="w-full pl-7 pr-4 p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all font-semibold text-zinc-800">
                </div>
            </div>

            <!-- Max Cap -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Maximum Cap per Order (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">₹</span>
                    <input type="number" name="max_cap" value="{{ $settings['max_cap'] }}" min="0" required
                           class="w-full pl-7 pr-4 p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all font-semibold text-zinc-800">
                </div>
            </div>

            <!-- Payment Day -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Payment Day of Month</label>
                <div class="relative">
                    <input type="number" name="payment_day" value="{{ $settings['payment_day'] }}" min="1" max="31" required
                           class="w-full p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all font-semibold text-zinc-800">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-zinc-400">th</span>
                </div>
            </div>

            <!-- Amendment Trigger -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-zinc-500 uppercase tracking-wider block">Amendment Trigger (orders/month)</label>
                <input type="number" name="amendment_trigger" value="{{ $settings['amendment_trigger'] }}" min="1" required
                       class="w-full p-3 text-xs border border-zinc-200 rounded-xl bg-zinc-50 hover:bg-zinc-100 focus:bg-white focus:ring-1 focus:ring-zinc-950 focus:border-zinc-950 focus:outline-none transition-all font-semibold text-zinc-800">
            </div>
        </div>

        <div class="p-4 bg-zinc-50 border border-zinc-150 rounded-xl text-[11px] text-zinc-500 font-semibold space-y-1.5">
            <p class="font-bold text-zinc-700 flex items-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4 text-[#09090b]"></i>
                Important Notice regarding site parameters:
            </p>
            <p>1. Changing these values does not affect previously finalized / paid monthly payroll statements.</p>
            <p>2. Calculated earnings formula per order is: <code>net_earning = max(min_cap, min(max_cap, order_amount * commission_percent)) - platform_expense</code>.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-100">
            <button type="submit" class="bg-zinc-900 text-white hover:bg-zinc-800 hover:bg-zinc-800 hover:text-white font-bold px-6 py-2.5 rounded-xl text-xs transition-all shadow-sm flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </form>
</div>
@endsection
