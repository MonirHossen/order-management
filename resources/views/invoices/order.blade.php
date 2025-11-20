<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; color: #333; }
        .info-section { margin-bottom: 30px; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-column { width: 48%; }
        .info-label { font-weight: bold; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background-color: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .text-right { text-align: right; }
        .totals { margin-top: 30px; text-align: right; }
        .totals-row { display: flex; justify-content: flex-end; margin: 8px 0; }
        .totals-label { width: 150px; font-weight: bold; text-align: right; padding-right: 20px; }
        .totals-value { width: 120px; text-align: right; }
        .grand-total { font-size: 18px; font-weight: bold; color: #333; padding-top: 10px; border-top: 2px solid #333; }
        .footer { margin-top: 50px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>INVOICE</h1>
            <p>{{ $invoiceNumber }}</p>
        </div>

        <div class="info-section">
            <div class="info-row">
                <div class="info-column">
                    <div class="info-label">From:</div>
                    <strong>{{ config('app.name') }}</strong><br>
                    123 Business Street<br>
                    City, State 12345<br>
                    Email: info@example.com
                </div>
                <div class="info-column">
                    <div class="info-label">Invoice Details:</div>
                    <strong>Order Number:</strong> {{ $order->order_number }}<br>
                    <strong>Date:</strong> {{ $order->created_at->format('F d, Y') }}<br>
                    <strong>Status:</strong> {{ ucfirst($order->status) }}
                </div>
            </div>

            <div class="info-row">
                <div class="info-column">
                    <div class="info-label">Bill To:</div>
                    <strong>{{ $order->shipping_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }}<br>
                    {{ $order->shipping_country }} {{ $order->shipping_postal_code }}<br>
                    Email: {{ $order->shipping_email }}<br>
                    Phone: {{ $order->shipping_phone }}
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}
                        @if($item->variant_details)
                            <br><small style="color: #666;">
                                @foreach($item->variant_details as $key => $value)
                                    {{ ucfirst($key) }}: {{ $value }}
                                @endforeach
                            </small>
                        @endif
                    </td>
                    <td>{{ $item->product_sku }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div class="totals-row">
                <div class="totals-label">Subtotal:</div>
                <div class="totals-value">${{ number_format($order->subtotal, 2) }}</div>
            </div>
            @if($order->tax > 0)
            <div class="totals-row">
                <div class="totals-label">Tax:</div>
                <div class="totals-value">${{ number_format($order->tax, 2) }}</div>
            </div>
            @endif
            @if($order->shipping_fee > 0)
            <div class="totals-row">
                <div class="totals-label">Shipping:</div>
                <div class="totals-value">${{ number_format($order->shipping_fee, 2) }}</div>
            </div>
            @endif
            @if($order->discount > 0)
            <div class="totals-row">
                <div class="totals-label">Discount:</div>
                <div class="totals-value">-${{ number_format($order->discount, 2) }}</div>
            </div>
            @endif
            <div class="totals-row grand-total">
                <div class="totals-label">Total Amount:</div>
                <div class="totals-value">${{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For questions about this invoice, please contact us at support@example.com</p>
        </div>
    </div>
</body>
</html>