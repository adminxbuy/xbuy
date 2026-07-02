<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #{{ $order->order_number }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1f2937;
            line-height: 1.6;
            padding: 40px;
            background-color: #f3f4f6;
            margin: 0;
        }

        .invoice-box {
            max-width: 850px;
            margin: auto;
            padding: 40px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: hidden;
        }

        /* Abstract Top Accent Bar */
        .invoice-box::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #09090b 0%, #ffd747 100%);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            margin-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .logo-block {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo-badge {
            width: 32px;
            height: 32px;
            background-color: #09090b;
            color: #000000;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 18px;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: #000000;
            letter-spacing: -0.025em;
        }

        .logo-text span {
            color: #ffd747;
        }

        .invoice-title {
            text-align: right;
        }

        .invoice-title h1 {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .invoice-title p {
            margin: 6px 0 0;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            background-color: #f9fafb;
            padding: 16px 20px;
            border-radius: 16px;
            border: 1px solid #f3f4f6;
            margin-bottom: 30px;
        }

        .meta-item label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 4px;
        }

        .meta-item span {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .details-block {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
        }

        .details-block h3 {
            margin-top: 0;
            margin-bottom: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #ffd747;
            letter-spacing: 0.05em;
            border-bottom: 1px dashed #e5e7eb;
            padding-bottom: 8px;
        }

        .details-block p {
            margin: 4px 0;
            font-size: 13px;
            color: #4b5563;
            font-weight: 500;
        }

        .details-block p strong {
            color: #111827;
            font-size: 14px;
        }

        .table-container {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .table th {
            background-color: #f9fafb;
            padding: 12px 16px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 1px solid #e5e7eb;
            letter-spacing: 0.05em;
        }

        .table td {
            padding: 16px;
            font-size: 13px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            vertical-align: top;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .totals-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
        }

        .escrow-shield {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-radius: 16px;
            padding: 16px;
            max-width: 400px;
            display: flex;
            gap: 12px;
        }

        .shield-icon {
            font-size: 24px;
            line-height: 1;
        }

        .shield-text h4 {
            margin: 0 0 4px;
            font-size: 12px;
            font-weight: 700;
            color: #92400e;
        }

        .shield-text p {
            margin: 0;
            font-size: 11px;
            color: #b45309;
            line-height: 1.4;
            font-weight: 500;
        }

        .totals-box {
            width: 280px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 16px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12px;
            font-weight: 600;
            color: #4b5563;
        }

        .totals-row.grand-total {
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            margin-top: 6px;
            font-size: 16px;
            font-weight: 800;
            color: #111827;
        }

        .footer {
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 24px;
            font-size: 11px;
            color: #9ca3af;
            font-weight: 500;
        }

        .footer p {
            margin: 4px 0;
        }

        /* Action Bar for printing / closing */
        .action-bar {
            max-width: 850px;
            margin: 0 auto 20px auto;
            padding: 16px 24px;
            background: #111827;
            border-radius: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .action-bar-text {
            color: #ffffff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }

        .action-bar-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary {
            background-color: #09090b;
            color: #000000;
        }

        .btn-primary:hover {
            background-color: #ffd747;
        }

        .btn-secondary {
            background-color: #374151;
            color: #ffffff;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        @media print {
            body {
                background-color: #ffffff;
                padding: 0;
            }

            .invoice-box {
                box-shadow: none;
                border: none;
                padding: 0;
            }

            .action-bar {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <!-- Print / Action Floating Bar (Hidden in Print) -->
    <div class="action-bar">
        <div class="action-bar-text">Tax Invoice #{{ $order->order_number }}</div>
        <div class="action-bar-buttons">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Print / Save as PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                ❌ Close Window
            </button>
        </div>
    </div>

    <div class="invoice-box">
        <!-- Logo & Header -->
        <div class="header">
            <div class="logo-block">
                <div class="logo-badge">XB</div>
                <div class="logo-text">X-<span>Buy</span></div>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p>Tax Invoice / Bill of Sale</p>
            </div>
        </div>

        <!-- Meta Grid Info -->
        <div class="meta-grid">
            <div class="meta-item">
                <label>Order Number</label>
                <span>#{{ $order->order_number }}</span>
            </div>
            <div class="meta-item">
                <label>Date Issued</label>
                <span>{{ $order->created_at->format('M d, Y') }}</span>
            </div>
            <div class="meta-item">
                <label>Payment Method</label>
                <span>Razorpay Escrow</span>
            </div>
            <div class="meta-item">
                <label>Order Status</label>
                <span
                    style="color: #059669; font-weight: 700;">{{ ucwords(str_replace('_', ' ', $order->status)) }}</span>
            </div>
        </div>

        <!-- Billing details grid -->
        <div class="details-grid">
            <div class="details-block">
                <h3>Billed To (Buyer)</h3>
                <p><strong>{{ $order->delivery_address['name'] ?? $order->buyer->name }}</strong></p>
                <p>Phone: {{ $order->delivery_address['phone'] ?? $order->buyer->phone }}</p>
                <p>{{ $order->delivery_address['street'] ?? '' }}</p>
                <p>{{ $order->delivery_address['city'] ?? '' }}, {{ $order->delivery_address['state'] ?? '' }} -
                    {{ $order->delivery_address['pincode'] ?? '' }}</p>
            </div>
            <div class="details-block">
                <h3>Sold By (Seller)</h3>
                <p><strong>{{ $order->seller->shop_name }}</strong></p>
                <p>Email: {{ $order->seller->user->email }}</p>
                <p>Address: {{ $order->seller->shop_city }}, {{ $order->seller->shop_state }} -
                    {{ $order->seller->shop_pincode }}</p>
            </div>
        </div>

        <!-- Product Table -->
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th>Grade</th>
                        <th>Serial Number</th>
                        <th class="text-right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #111827;">{{ $order->listing->title }}</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 4px;">Component Category:
                                {{ $order->listing->category->name ?? 'PC Parts' }}</div>
                        </td>
                        <td>
                            <span style="font-weight: 700; text-transform: uppercase;">Grade
                                {{ $order->listing->grade }}</span>
                        </td>
                        <td>
                            <code
                                style="font-family: monospace; font-size: 12px; background-color: #f3f4f6; padding: 2px 6px; border-radius: 4px; color: #4b5563;">{{ $order->listing->serial_number ?: 'N/A' }}</code>
                        </td>
                        <td class="text-right" style="font-weight: 600;">₹{{ number_format($order->product_amount, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="totals-section">
            <!-- Escrow Badge Alert -->
            <div class="escrow-shield">
                <div class="shield-icon">🛡️</div>
                <div class="shield-text">
                    <h4>X-Buy Escrow Protection</h4>
                    <p>Your payment is safely held in escrow and will only be released to the seller after the
                        verification window expires or order is confirmed.</p>
                </div>
            </div>

            <!-- Totals Box -->
            <div class="totals-box">
                <div class="totals-row">
                    <span>Subtotal</span>
                    <span>₹{{ number_format($order->product_amount, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Shipping Charges</span>
                    <span>₹{{ number_format($order->shipping_amount, 2) }}</span>
                </div>
                <div class="totals-row grand-total">
                    <span>Total Paid</span>
                    <span>₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for buying from <strong>{{ $order->seller->shop_name }}</strong> on X-Buy Escrow PC Parts
                Marketplace!</p>
            <p>For any queries or dispute claims, contact us at <strong>support@xbuy.in</strong></p>
        </div>
    </div>
</body>

</html>