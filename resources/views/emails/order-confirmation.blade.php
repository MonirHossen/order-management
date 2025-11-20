<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .order-details { background-color: white; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Confirmed!</h1>
        </div>
        <div class="content">
            <h2>Thank you for your order, {{ $order->shipping_name }}!</h2>
            <p>We've received your order and will process it shortly.</p>
            
            <div class="order-details">
                <h3>Order Details</h3>
                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
                <p><strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            </div>

            <h3>Items Ordered</h3>
            @foreach($order->items as $item)
            <p>• {{ $item->product_name }} - Qty: {{ $item->quantity }} - ${{ number_format($item->total_price, 2) }}</p>
            @endforeach

            <h3>Shipping Address</h3>
            <p>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_city }}, {{ $order->shipping_state }}<br>
                {{ $order->shipping_country }} {{ $order->shipping_postal_code }}
            </p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/orders/' . $order->id) }}" class="button">View Order</a>
            </p>
        </div>
        <div class="footer">
            <p>If you have any questions, please contact us at support@example.com</p>
        </div>
    </div>
</body>
</html>