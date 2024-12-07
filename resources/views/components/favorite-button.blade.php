<button 
    onclick="toggleFavorite({{ $product->id }})"
    class="btn btn-outline-danger btn-sm favorite-btn"
    data-product-id="{{ $product->id }}"
    data-favorited="{{ Auth::check() && Auth::user()->hasFavorited($product) ? 'true' : 'false' }}"
>
    <i class="fas fa-heart{{ Auth::check() && Auth::user()->hasFavorited($product) ? '' : '-broken' }}"></i>
</button>

@once
@push('scripts')
<script>
function toggleFavorite(productId) {
    if (!{{ Auth::check() ? 'true' : 'false' }}) {
        window.location.href = '{{ route('login') }}';
        return;
    }

    const button = document.querySelector(`.favorite-btn[data-product-id="${productId}"]`);
    const isFavorited = button.dataset.favorited === 'true';
    const icon = button.querySelector('i');

    if (isFavorited) {
        // Remove from favorites
        fetch(`/favorites/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            button.dataset.favorited = 'false';
            icon.classList.remove('fa-heart');
            icon.classList.add('fa-heart-broken');
            showToast(data.message);
        });
    } else {
        // Add to favorites
        fetch(`/favorites/${productId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            button.dataset.favorited = 'true';
            icon.classList.remove('fa-heart-broken');
            icon.classList.add('fa-heart');
            showToast(data.message);
        });
    }
}

function showToast(message) {
    // You can implement your preferred toast notification here
    alert(message); // Basic implementation
}
</script>
@endpush
@endonce
