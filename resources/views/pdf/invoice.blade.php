<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
        }
        .invoice-header {
            padding: 20px 0;
            border-bottom: 2px solid #ddd;
            margin-bottom: 30px;
        }
        .company-details {
            float: left;
        }
        .invoice-details {
            float: right;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .customer-details {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        .totals {
            float: right;
            width: 300px;
        }
        .totals table {
            width: 100%;
        }
        .totals table td {
            border: none;
        }
        .totals table tr:last-child {
            font-weight: bold;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div class="company-details">
            <h2>{{ config('app.name') }}</h2>
            <p>123 Business Street<br>
               City, State 12345<br>
               Phone: (123) 456-7890<br>
               Email: info@company.com</p>
        </div>
        <div class="invoice-details">
            <h1>INVOICE</h1>
            <p>Invoice #: {{ $order->id }}<br>
               Date: {{ $order->created_at->format('M d, Y') }}<br>
               Due Date: {{ $order->created_at->addDays(30)->format('M d, Y') }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-details">
        <h3>Bill To:</h3>
        <p>{{ $order->billing_name }}<br>
           {{ $order->billing_address }}<br>
           {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_zip }}<br>
           {{ $order->billing_country }}<br>
           Email: {{ $order->billing_email }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->price, 2) }}</td>
                <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
            </tr>
            @if($order->tax > 0)
            <tr>
                <td>Tax:</td>
                <td>${{ number_format($order->tax, 2) }}</td>
            </tr>
            @endif
            @if($order->shipping_cost > 0)
            <tr>
                <td>Shipping:</td>
                <td>${{ number_format($order->shipping_cost, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Total:</td>
                <td>${{ number_format($order->total, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="clear"></div>
    
    <div style="margin-top: 50px;">
        <p><strong>Payment Terms:</strong> Net 30</p>
        <p><strong>Thank you for your business!</strong></p>
    </div>
</body>
</html>
