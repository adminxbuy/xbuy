<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #374151;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }
        .amount {
            font-size: 28px;
            font-weight: bold;
            color: #16a34a;
            margin: 10px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .details-table th, .details-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }
        .details-table th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #4b5563;
        }
        .footer {
            text-align: center;
            font-size: 11px;
            color: #9ca3af;
            margin-top: 30px;
            border-top: 1px solid #f3f4f6;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">X-BUY</div>
            <p style="margin: 5px 0; color: #6b7280; font-size: 14px;">Salary Disbursement Notice</p>
            <div class="amount">₹{{ number_format($totalEarned, 2) }}</div>
            <p style="margin: 0; font-size: 12px; color: #9ca3af;">Has been disbursed to your account</p>
        </div>

        <p>Dear {{ $staff->name }},</p>
        <p>Your salary commission earnings for the cycle <strong>{{ $monthName }}</strong> have been successfully processed and disbursed. A detailed salary slip PDF has been attached to this email for your records.</p>

        <div style="background-color: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #f3f4f6; margin-bottom: 20px;">
            <table style="width: 100%; font-size: 13px;">
                <tr>
                    <td style="color: #6b7280;">Month / Year:</td>
                    <td style="font-weight: bold; text-align: right;">{{ $monthName }}</td>
                </tr>
                <tr>
                    <td style="color: #6b7280;">Qualifying Orders:</td>
                    <td style="font-weight: bold; text-align: right;">{{ $earnings->count() }}</td>
                </tr>
                <tr>
                    <td style="color: #6b7280;">Payment Reference:</td>
                    <td style="font-weight: bold; text-align: right; font-family: monospace;">{{ $paymentRef }}</td>
                </tr>
            </table>
        </div>

        <h3 style="font-size: 14px; margin-bottom: 10px; color: #374151;">Qualifying Orders Breakdown</h3>
        <table class="details-table" style="font-size: 12px;">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Category</th>
                    <th style="text-align: right;">Sale Value</th>
                    <th style="text-align: right;">Earning</th>
                </tr>
            </thead>
            <tbody>
                @foreach($earnings as $earn)
                <tr>
                    <td>#{{ $earn->order->order_number ?? '-' }}</td>
                    <td style="text-transform: uppercase;">{{ $earn->category }}</td>
                    <td style="text-align: right;">₹{{ number_format($earn->sale_value, 2) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #16a34a;">₹{{ number_format($earn->final_earning, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p style="font-size: 13px;">You can view and download all your past statements directly inside the <a href="{{ route('admin.my-earnings') }}">My Earnings</a> dashboard on the platform.</p>

        <div class="footer">
            <p>This is an automated dispatch from X-Buy Finance Operations. Please do not reply directly to this email.</p>
            <p>&copy; {{ date('Y') }} X-Buy Marketplace Platforms. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
