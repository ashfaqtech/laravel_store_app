@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container">
    <h1 class="mb-4">Shopping Cart</h1>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(count($products) > 0)
        <form action="{{ route('cart.update') }}" method="POST">
            @csrf
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('storage/products/thumbnails/'.$product->image) }}" 
                                                             alt="{{ $product->name }}"
                                                             style="width: 50px; height: 50px; object-fit: cover;"
                                                             class="me-3">
                                                        <div>
                                                            <h6 class="mb-0">{{ $product->name }}</h6>
                                                            <small class="text-muted">
                                                                {{ $product->category->name }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>${{ number_format($product->price, 2) }}</td>
                                                <td style="width: 150px;">
                                                    <input type="number" 
                                                           name="quantities[{{ $product->id }}]" 
                                                           value="{{ $product->quantity }}"
                                                           min="0"
                                                           class="form-control">
                                                </td>
                                                <td>${{ number_format($product->price * $product->quantity, 2) }}</td>
                                                <td>
                                                    <form action="{{ route('cart.remove', $product->id) }}" 
                                                          method="POST" 
                                                          class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Are you sure you want to remove this item?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ route('shop') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                                </a>
                                <div>
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-sync-alt me-2"></i> Update Cart
                                    </button>
                                    <a href="{{ route('cart.clear') }}" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Are you sure you want to clear your cart?')">
                                        <i class="fas fa-trash me-2"></i> Clear Cart
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Cart Summary</h5>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total</strong>
                                <strong>${{ number_format($total, 2) }}</strong>
                            </div>

                            <a href="{{ route('cart.checkout') }}" class="btn btn-success w-100">
                                <i class="fas fa-lock me-2"></i> Proceed to Checkout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Add some products to your cart and come back here to complete your purchase.</p>
            <a href="{{ route('shop') }}" class="btn btn-primary">
                <i class="fas fa-shopping-bag me-2"></i> Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection
