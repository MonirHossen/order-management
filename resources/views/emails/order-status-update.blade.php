<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #2196F3; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .status-badge { display: inline-block; padding: 8px 15px; border-radius: 20px; font-weight: bold; }
        .status-processing { background-color: #FFC107; color: #000; }
        .status-shipped { background-color: #2196F3; color: white; }
        .status-delivered { background-color: #4CAF50; color: white; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Status Update</h1>
        </div>
        <div class="content">
            <h2>Hello {{ $order->shipping_name }},</h2>
            <p>Your order status has been updated.</p>
            
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>New Status:</strong> <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span></p>

            @if($status === 'shipped')
            <p>Your order has been shipped and is on its way to you!</p>
            @elseif($status === 'delivered')
            <p>Your order has been delivered. We hope you enjoy your purchase!</p>
            @elseif($status === 'processing')
            <p>Your order is being prepared for shipment.</p>
            @endif
        </div>
        <div class="footer">
            <p>Track your order at any time by logging into your account.</p>
        </div>
    </div>
</body>
</html>