<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Salary Slip - {{ $staff->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            padding: 20px;
        }
        .header {
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }
        .subtitle {
            font-size: 10px;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }
        .meta-title {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }
        .meta-value {
            font-weight: bold;
            font-size: 12px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #374151;
        }
        .earnings-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .earnings-table th {
            background-color: #f3f4f6;
            color: #4b5563;
            font-weight: bold;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #d1d5db;
        }
        .earnings-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .total-badge {
            font-size: 16px;
            font-weight: bold;
            color: #16a34a;
        }
        .footer {
            margin-top: 50px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .signature-table {
            width: 100%;
        }
        .signature-table td {
            width: 50%;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="logo">X-BUY</div>
                    <div class="subtitle">Staff Commission Disbursement Statement</div>
                </td>
                <td style="text-align: right;">
                    <div style="font-weight: bold; font-size: 14px; color: #4b5563;">SALARY SLIP</div>
                    <div style="color: #6b7280;">{{ $monthName }}</div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <div class="meta-title">Staff Name</div>
                <div class="meta-value">{{ $staff->name }}</div>
            </td>
            <td style="width: 50%;">
                <div class="meta-title">Designation</div>
                <div class="meta-value">{{ $staff->staffProfile->designation ?? 'Platform Operations Executive' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="meta-title">Disbursement Period</div>
                <div class="meta-value">{{ $monthName }}</div>
            </td>
            <td>
                <div class="meta-title">Payment Reference</div>
                <div class="meta-value" style="font-family: monospace;">{{ $paymentRef }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="meta-title">Qualifying Orders Completed</div>
                <div class="meta-value">{{ $earnings->count() }}</div>
            </td>
            <td>
                <div class="meta-title">Total Earned Amount</div>
                <div class="meta-value total-badge">₹{{ number_format($totalEarned, 2) }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Order Breakdown Statement</div>
    <table class="earnings-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Category</th>
                <th class="text-right">Sale Value</th>
                <th class="text-right">Comm %</th>
                <th class="text-right">Platform Expense</th>
                <th class="text-right">Earning</th>
            </tr>
        </thead>
        <tbody>
            @foreach($earnings as $earn)
            <tr>
                <td style="font-weight: bold;">#{{ $earn->order->order_number ?? '-' }}</td>
                <td style="text-transform: uppercase;">{{ $earn->category }}</td>
                <td class="text-right">₹{{ number_format($earn->sale_value, 2) }}</td>
                <td class="text-right">{{ $earn->commission_percentage }}%</td>
                <td class="text-right">₹{{ number_format($earn->platform_expenses, 2) }}</td>
                <td class="text-right" style="font-weight: bold; color: #16a34a;">₹{{ number_format($earn->final_earning, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table class="signature-table">
            <tr>
                <td>
                    <p style="color: #9ca3af; margin-bottom: 40px;">For X-Buy Marketplace Platforms</p>
                    <p style="font-weight: bold; margin: 0;">Authorized Finance Signatory</p>
                </td>
                <td style="text-align: right;">
                    <p style="color: #9ca3af; margin-bottom: 40px;">Employee Acceptance Signature</p>
                    <p style="font-weight: bold; margin: 0;">{{ $staff->name }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
