@component('mail::message')
# Order Confirmation

Dear {{ $order->shipping_name }},

Thank you for your order! We have received your order and it is now being processed.

**Order Number:** {{ $order->order_number }}  
**Order Date:** {{ $order->created_at->format('F j, Y') }}

## Order Details

@component('mail::table')
| Product | Quantity | Price |
|:--------|:---------|:------|
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
@endcomponent

**Total Amount:** ${{ number_format($order->total_amount, 2) }}

## Shipping Information
{{ $order->shipping_name }}  
{{ $order->shipping_address }}  
{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}  
{{ $order->shipping_phone }}

You can track your order status by clicking the button below:

@component('mail::button', ['url' => route('orders.show', $order)])
View Order
@endcomponent

If you have any questions about your order, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
