@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Wishlist</h5>
                </div>

                <div class="card-body">
                    @if($favorites->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-heart text-muted mb-3" style="font-size: 3rem;"></i>
                            <h5>Your wishlist is empty</h5>
                            <p class="text-muted">Browse our products and add items to your wishlist</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                Browse Products
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($favorites as $favorite)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100">
                                        @if($favorite->product->image)
                                            <img src="{{ asset('storage/' . $favorite->product->image) }}" 
                                                 class="card-img-top" 
                                                 alt="{{ $favorite->product->name }}">
                                        @endif
                                        <div class="card-body">
                                            <h5 class="card-title">{{ $favorite->product->name }}</h5>
                                            <p class="card-text text-muted">
                                                {{ Str::limit($favorite->product->description, 100) }}
                                            </p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="h5 mb-0">${{ number_format($favorite->product->price, 2) }}</span>
                                                <div>
                                                    <form action="{{ route('cart.add', $favorite->product) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Add to Cart
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('favorites.destroy', $favorite->product) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center mt-4">
                            {{ $favorites->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
