@extends('layouts.app')

@section('title', 'Shop')

@section('content')
<div class="container">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('shop') }}" method="GET">
                        <!-- Categories -->
                        <div class="mb-4">
                            <h6>Categories</h6>
                            @foreach($categories as $category)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="category[]" value="{{ $category->id }}"
                                           id="category{{ $category->id }}"
                                           {{ in_array($category->id, (array)request('category')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="category{{ $category->id }}">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <h6>Price Range</h6>
                            <div class="input-group mb-2">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="min_price" 
                                       placeholder="Min" value="{{ request('min_price') }}">
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="max_price" 
                                       placeholder="Max" value="{{ request('max_price') }}">
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-4">
                            <h6>Sort By</h6>
                            <select class="form-select" name="sort">
                                <option value="">Default</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>
                                    Price: Low to High
                                </option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>
                                    Price: High to Low
                                </option>
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                                    Newest First
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-md-9">
            <!-- Search Results Info -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    @if(request('search'))
                        <h4>Search Results for "{{ request('search') }}"</h4>
                    @else
                        <h4>All Products</h4>
                    @endif
                    <p class="text-muted mb-0">{{ $products->total() }} products found</p>
                </div>
            </div>

            <!-- Products -->
            <div class="row">
                @forelse($products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <!-- Product Image -->
                            <img src="{{ asset('storage/products/'.$product->image) }}" 
                                 class="card-img-top" 
                                 alt="{{ $product->name }}"
                                 style="height: 200px; object-fit: cover;">
                            
                            <!-- Product Info -->
                            <div class="card-body">
                                <h5 class="card-title">{{ $product->name }}</h5>
                                <p class="card-text text-muted mb-2">{{ $product->category->name }}</p>
                                <h6 class="text-primary mb-3">${{ number_format($product->price, 2) }}</h6>
                                
                                <!-- Add to Cart Form -->
                                <form action="{{ route('cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <div class="d-flex gap-2">
                                        <input type="number" name="quantity" value="1" min="1" 
                                               class="form-control" style="width: 80px;">
                                        <button type="submit" class="btn btn-primary flex-grow-1">
                                            Add to Cart
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info">
                            No products found.
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
