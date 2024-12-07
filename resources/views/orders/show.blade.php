@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order #{{ $order->id }}</h5>
                    <div>
                        <a href="{{ route('orders.invoice.download', $order) }}" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Invoice
                        </a>
                        <a href="{{ route('orders.invoice.view', $order) }}" class="btn btn-secondary" target="_blank">
                            <i class="fas fa-eye"></i> View Invoice
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p>
                                <strong>Order Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}<br>
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $order->status_color }}">
                                    {{ ucfirst($order->status) }}
                                </span><br>
                                <strong>Payment Status:</strong> 
                                <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Billing Details</h6>
                            <p>
                                {{ $order->billing_name }}<br>
                                {{ $order->billing_address }}<br>
                                {{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_zip }}<br>
                                {{ $order->billing_country }}<br>
                                {{ $order->billing_email }}
                            </p>
                        </div>
                    </div>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
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
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td>${{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                @if($order->tax > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Tax:</strong></td>
                                    <td>${{ number_format($order->tax, 2) }}</td>
                                </tr>
                                @endif
                                @if($order->shipping_cost > 0)
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                    <td>${{ number_format($order->shipping_cost, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>${{ number_format($order->total, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($order->notes)
                    <div class="mt-4">
                        <h6>Order Notes</h6>
                        <p>{{ $order->notes }}</p>
                    </div>
                    @endif

                    @if($order->status !== 'cancelled' && $order->status !== 'completed')
                    <div class="mt-4">
                        <form action="{{ route('orders.cancel', $order) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                Cancel Order
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
