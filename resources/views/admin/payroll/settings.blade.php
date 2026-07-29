@extends('layouts.admin')

@section('title', 'Payroll Settings')
@section('page_title', 'Payroll Settings')

@section('content')
<div class="max-w-3xl bg-card border border-border rounded-xl p-6 shadow-sm">
    <div class="mb-6 border-b border-border pb-5">
        <h3 class="font-bold text-foreground text-base">Global Payroll settings</h3>
        <p class="text-xs text-muted-foreground">Configure global parameters used for staff order commission and disbursement calculations.</p>
    </div>

    <form action="{{ route('admin.payroll.settings.save') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Commission Percent -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Commission Percentage (%)</label>
                <div class="relative">
                    <input type="number" step="0.1" name="commission_percent" value="{{ $settings['commission_percent'] }}" min="0" max="100" required
                           class="w-full p-3 pr-10 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-muted-foreground">%</span>
                </div>
            </div>

            <!-- Platform Expense -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Platform Expense per Order (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-muted-foreground">₹</span>
                    <input type="number" name="platform_expense" value="{{ $settings['platform_expense'] }}" min="0" required
                           class="w-full pl-7 pr-4 p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
                </div>
            </div>

            <!-- Min Cap -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Minimum Cap per Order (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-muted-foreground">₹</span>
                    <input type="number" name="min_cap" value="{{ $settings['min_cap'] }}" min="0" required
                           class="w-full pl-7 pr-4 p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
                </div>
            </div>

            <!-- Max Cap -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Maximum Cap per Order (₹)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-muted-foreground">₹</span>
                    <input type="number" name="max_cap" value="{{ $settings['max_cap'] }}" min="0" required
                           class="w-full pl-7 pr-4 p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
                </div>
            </div>

            <!-- Payment Day -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Payment Day of Month</label>
                <div class="relative">
                    <input type="number" name="payment_day" value="{{ $settings['payment_day'] }}" min="1" max="31" required
                           class="w-full p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-muted-foreground">th</span>
                </div>
            </div>

            <!-- Amendment Trigger -->
            <div class="space-y-1">
                <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider block">Amendment Trigger (orders/month)</label>
                <input type="number" name="amendment_trigger" value="{{ $settings['amendment_trigger'] }}" min="1" required
                       class="w-full p-3 text-xs border border-border rounded-lg bg-muted hover:bg-muted focus:bg-card focus:ring-1 focus:ring-ring focus:border-ring focus:outline-none transition-all font-semibold text-foreground">
            </div>
        </div>

        <div class="p-4 bg-muted border border-border rounded-xl text-[11px] text-muted-foreground font-semibold space-y-1.5">
            <p class="font-bold text-foreground flex items-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4 text-foreground"></i>
                Important Notice regarding site parameters:
            </p>
            <p>1. Changing these values does not affect previously finalized / paid monthly payroll statements.</p>
            <p>2. Calculated earnings formula per order is: <code>net_earning = max(min_cap, min(max_cap, order_amount * commission_percent)) - platform_expense</code>.</p>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-border">
            <button type="submit" class="bg-primary text-primary-foreground hover:bg-primary/90 hover:text-primary-foreground font-medium px-6 py-2.5 rounded-lg text-xs transition-all shadow-sm flex items-center gap-1.5">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Settings</span>
            </button>
        </div>
    </form>
</div>
@endsection
