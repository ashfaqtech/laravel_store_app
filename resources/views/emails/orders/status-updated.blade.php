@component('mail::message')
# Order Status Update

Dear {{ $order->shipping_name }},

Your order status has been updated.

**Order Number:** {{ $order->order_number }}  
**New Status:** {{ ucfirst($order->status) }}

@if($order->status === 'processing')
Your order is now being processed and will be shipped soon.
@elseif($order->status === 'completed')
Your order has been completed and shipped. You should receive it within the estimated delivery time.
@elseif($order->status === 'cancelled')
Your order has been cancelled. If you did not request this cancellation, please contact us immediately.
@endif

## Order Details

@component('mail::table')
| Product | Quantity | Price |
|:--------|:---------|:------|
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
@endcomponent

**Total Amount:** ${{ number_format($order->total_amount, 2) }}

You can view your order details by clicking the button below:

@component('mail::button', ['url' => route('orders.show', $order)])
View Order
@endcomponent

If you have any questions about your order, please don't hesitate to contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
