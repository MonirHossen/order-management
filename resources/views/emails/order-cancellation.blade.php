<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f44336; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Order Cancelled</h1>
        </div>
        <div class="content">
            <h2>Order Cancellation Confirmation</h2>
            <p>Hello {{ $order->shipping_name }},</p>
            <p>Your order has been successfully cancelled.</p>
            
            <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
            <p><strong>Cancellation Reason:</strong> {{ $reason }}</p>
            <p><strong>Order Total:</strong> ${{ number_format($order->total_amount, 2) }}</p>

            <p>If you paid for this order, your refund will be processed within 5-7 business days.</p>
        </div>
        <div class="footer">
            <p>If you have any questions, please contact us at support@example.com</p>
        </div>
    </div>
</body>
</html>