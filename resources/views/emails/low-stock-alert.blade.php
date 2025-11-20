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
            <h2>Low Stock Alert</h2>
        </div>
        <div class="content">

            <p>The following product is running low on stock:</p>

            <ul>
            <li><strong>Product:</strong> {{ $product->name }}</li>
            <li><strong>SKU:</strong> {{ $product->sku }}</li>
            <li><strong>Current Stock:</strong> {{ $product->stock_quantity }}</li>
            <li><strong>Threshold:</strong> {{ $product->low_stock_threshold }}</li>
            </ul>

        </div>
        <div class="footer">
            <p>Please restock soon.</p>
        </div>
    </div>
</body>
</html>