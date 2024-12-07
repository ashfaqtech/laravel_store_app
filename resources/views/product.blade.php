@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="container">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}">Shop</a></li>
            <li class="breadcrumb-item">
                <a href="{{ route('category', $product->category->slug) }}">
                    {{ $product->category->name }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <!-- Product Details -->
    <div class="row mb-5">
        <!-- Product Image -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <img src="{{ asset('storage/products/'.$product->image) }}" 
                     class="card-img-top" 
                     alt="{{ $product->name }}"
                     style="max-height: 500px; object-fit: cover;">
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-6">
            <h1 class="mb-3">{{ $product->name }}</h1>
            <p class="text-muted mb-3">Category: {{ $product->category->name }}</p>
            
            <div class="mb-4">
                <h2 class="text-primary">${{ number_format($product->price, 2) }}</h2>
            </div>

            <div class="mb-4">
                <h5>Description</h5>
                <p>{{ $product->description }}</p>
            </div>

            <!-- Add to Cart Form -->
            <form action="{{ route('cart.add') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="row g-3">
                    <div class="col-auto">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" 
                               value="1" min="1" style="width: 100px;">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </form>

            <!-- Additional Info -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Product Information</h5>
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted">SKU:</td>
                            <td>{{ $product->sku }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Stock:</td>
                            <td>
                                @if($product->stock > 0)
                                    <span class="badge bg-success">In Stock ({{ $product->stock }})</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Shipping:</td>
                            <td>Free shipping</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Section -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4">Customer Reviews</h3>

            @if($product->reviews->count() > 0)
                <div class="row">
                    @foreach($product->reviews as $review)
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <h6 class="card-subtitle text-muted">{{ $review->user->name }}</h6>
                                        <small class="text-muted">
                                            {{ $review->created_at->format('M d, Y') }}
                                        </small>
                                    </div>
                                    <div class="mb-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="card-text">{{ $review->comment }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No reviews yet. Be the first to review this product!</p>
            @endif

            @auth
                <!-- Review Form -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Write a Review</h5>
                        <form action="{{ route('reviews.store', $product) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select @error('rating') is-invalid @enderror" 
                                        id="rating" name="rating" required>
                                    <option value="">Select rating</option>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="1">1 - Poor</option>
                                </select>
                                @error('rating')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Your Review</label>
                                <textarea class="form-control @error('comment') is-invalid @enderror" 
                                          id="comment" name="comment" rows="3" required></textarea>
                                @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-info mt-4">
                    Please <a href="{{ route('login') }}">login</a> to write a review.
                </div>
            @endauth
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-5">
            <h3 class="mb-4">Related Products</h3>
            <div class="row">
                @foreach($relatedProducts as $relatedProduct)
                    <div class="col-md-3 mb-4">
                        <div class="card h-100">
                            <img src="{{ asset('storage/products/'.$relatedProduct->image) }}" 
                                 class="card-img-top" 
                                 alt="{{ $relatedProduct->name }}"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title">{{ $relatedProduct->name }}</h5>
                                <p class="card-text text-primary mb-2">
                                    ${{ number_format($relatedProduct->price, 2) }}
                                </p>
                                <a href="{{ route('product', $relatedProduct->slug) }}" 
                                   class="btn btn-outline-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
