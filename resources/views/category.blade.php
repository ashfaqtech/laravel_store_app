@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop') }}">Shop</a></li>
            @if($category->parent)
                <li class="breadcrumb-item">
                    <a href="{{ route('category', $category->parent->slug) }}">
                        {{ $category->parent->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ $category->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Category Info -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        @if($category->image)
                            <div class="col-md-3">
                                <img src="{{ asset('storage/categories/'.$category->image) }}" 
                                     alt="{{ $category->name }}" 
                                     class="img-fluid rounded">
                            </div>
                        @endif
                        <div class="col">
                            <h1 class="mb-3">{{ $category->name }}</h1>
                            @if($category->description)
                                <p class="mb-0">{{ $category->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subcategories -->
        @if($category->children->count() > 0)
            <div class="col-12 mb-4">
                <h3>Subcategories</h3>
                <div class="row">
                    @foreach($category->children as $subcategory)
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('category', $subcategory->slug) }}" 
                               class="text-decoration-none">
                                <div class="card h-100">
                                    @if($subcategory->image)
                                        <img src="{{ asset('storage/categories/'.$subcategory->image) }}" 
                                             class="card-img-top" 
                                             alt="{{ $subcategory->name }}"
                                             style="height: 150px; object-fit: cover;">
                                    @endif
                                    <div class="card-body">
                                        <h5 class="card-title text-dark mb-0">{{ $subcategory->name }}</h5>
                                        <p class="text-muted mb-0">
                                            {{ $subcategory->products->count() }} Products
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Products Grid -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Products</h3>
                <div class="d-flex align-items-center">
                    <span class="me-2">Sort by:</span>
                    <select class="form-select" style="width: auto;" 
                            onchange="window.location.href=this.value">
                        <option value="{{ route('category', ['slug' => $category->slug]) }}"
                                {{ !request('sort') ? 'selected' : '' }}>
                            Default
                        </option>
                        <option value="{{ route('category', ['slug' => $category->slug, 'sort' => 'price_low']) }}"
                                {{ request('sort') === 'price_low' ? 'selected' : '' }}>
                            Price: Low to High
                        </option>
                        <option value="{{ route('category', ['slug' => $category->slug, 'sort' => 'price_high']) }}"
                                {{ request('sort') === 'price_high' ? 'selected' : '' }}>
                            Price: High to Low
                        </option>
                        <option value="{{ route('category', ['slug' => $category->slug, 'sort' => 'newest']) }}"
                                {{ request('sort') === 'newest' ? 'selected' : '' }}>
                            Newest First
                        </option>
                    </select>
                </div>
            </div>

            @if($products->count() > 0)
                <div class="row">
                    @foreach($products as $product)
                        <div class="col-md-4 mb-4">
                            <div class="card h-100">
                                <img src="{{ asset('storage/products/'.$product->image) }}" 
                                     class="card-img-top" 
                                     alt="{{ $product->name }}"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $product->name }}</h5>
                                    <p class="card-text text-muted mb-2">{{ $product->category->name }}</p>
                                    <h6 class="text-primary mb-3">${{ number_format($product->price, 2) }}</h6>
                                    
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
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $products->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    No products found in this category.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
