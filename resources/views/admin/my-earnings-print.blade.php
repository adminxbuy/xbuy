<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Earnings Report - {{ Auth::user()->name }}</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #ffffff;
            color: #111827;
        }
        h1, h2, h3, h4 {
            font-family: 'Outfit', sans-serif;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body class="p-8 max-w-4xl mx-auto" onload="window.print()">

    <!-- Header Actions -->
    <div class="no-print flex items-center justify-between bg-muted border border-border rounded-xl p-4 mb-6">
        <span class="text-xs text-muted-foreground font-semibold">Ready to save / print as PDF. If the print dialogue did not open, click the button on the right.</span>
        <button onclick="window.print()" class="bg-primary text-primary-foreground hover:bg-primary/90 hover:bg-primary/90 hover:text-primary-foreground font-bold px-4 py-2 rounded-lg text-xs transition-all">
            Trigger Print Dialog
        </button>
    </div>

    <!-- Statement Header -->
    <div class="flex items-center justify-between border-b-2 border-border pb-6 mb-8">
        <div>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-primary text-primary-foreground hover:bg-primary/90 rounded-lg flex items-center justify-center font-bold text-foreground border border-black/10">XB</div>
                <h1 class="font-bold text-2xl tracking-tight">X-BUY</h1>
            </div>
            <p class="text-xs text-muted-foreground mt-1 font-semibold">STAFF COMMISSION DISBURSEMENT STATEMENT</p>
        </div>
        <div class="text-right">
            <span class="text-xs font-bold text-muted-foreground uppercase tracking-widest block">Report Date</span>
            <span class="text-sm font-bold text-foreground">{{ now()->format('d M, Y') }}</span>
        </div>
    </div>

    <!-- Staff Info Summary -->
    <div class="grid grid-cols-2 gap-8 bg-muted border border-border rounded-xl p-6 mb-8">
        <div>
            <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-2">Staff Member Details</h3>
            <p class="text-base font-bold text-foreground leading-tight">{{ Auth::user()->name }}</p>
            <p class="text-xs font-semibold text-muted-foreground mt-1">{{ Auth::user()->staffProfile->designation ?? 'Staff Member' }}</p>
            <p class="text-xs font-semibold text-muted-foreground mt-0.5">{{ Auth::user()->email }}</p>
        </div>
        <div class="text-right flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold text-muted-foreground uppercase tracking-wider mb-1">Lifetime Total Earnings</h3>
                <p class="text-2xl font-bold text-[#16a34a]">₹{{ number_format($totalEarned, 2) }}</p>
            </div>
            <div class="border-t border-border pt-2 mt-2">
                <p class="text-[10px] text-muted-foreground font-semibold">Payment Method: {{ Auth::user()->staffProfile->upi_id ? 'UPI / ' . Auth::user()->staffProfile->upi_id : 'Bank Transfer' }}</p>
            </div>
        </div>
    </div>

    <!-- Earnings Overview Cards -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <div class="border border-border rounded-xl p-4 text-center bg-card">
            <h4 class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">This Month</h4>
            <p class="text-xl font-bold text-foreground mt-1.5">₹{{ number_format($thisMonthEarned, 2) }}</p>
        </div>
        <div class="border border-border rounded-xl p-4 text-center bg-card">
            <h4 class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">Last Month</h4>
            <p class="text-xl font-bold text-foreground mt-1.5">₹{{ number_format($lastMonthEarned, 2) }}</p>
        </div>
    </div>

    <!-- Commissions Table -->
    <div class="mb-8">
        <h3 class="font-bold text-foreground text-sm mb-4">Commissions Statement breakdown</h3>
        <table class="w-full text-left text-xs border border-border rounded-xl overflow-hidden">
            <thead class="bg-muted font-bold border-b border-border text-muted-foreground">
                <tr>
                    <th class="px-4 py-3">Order #</th>
                    <th class="px-4 py-3">Sale Value</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Comm %</th>
                    <th class="px-4 py-3">Expenses</th>
                    <th class="px-4 py-3 text-right">Earning</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($filteredOrders as $order)
                <tr class="font-semibold text-foreground">
                    <td class="px-4 py-3">#{{ $order->order_number }}</td>
                    <td class="px-4 py-3">₹{{ number_format($order->total_amount, 2) }}</td>
                    <td class="px-4 py-3 uppercase text-muted-foreground">{{ $order->listing->category ?? 'GPU' }}</td>
                    <td class="px-4 py-3">{{ $order->commission_percent }}%</td>
                    <td class="px-4 py-3 text-muted-foreground">₹{{ number_format($order->expenses, 2) }}</td>
                    <td class="px-4 py-3 text-[#16a34a] text-right font-bold">₹{{ number_format($order->net, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No orders registered during this cycle.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Signature Footer -->
    <div class="border-t border-border pt-8 mt-12 grid grid-cols-2 text-xs">
        <div>
            <p class="text-muted-foreground font-semibold">For X-Buy Marketplace Platforms</p>
            <div class="h-12"></div>
            <p class="font-bold text-foreground">Authorized Finance signatory</p>
        </div>
        <div class="text-right">
            <p class="text-muted-foreground font-semibold">Employee acceptance</p>
            <div class="h-12"></div>
            <p class="font-bold text-foreground">{{ Auth::user()->name }}</p>
        </div>
    </div>

</body>
</html>
