@extends('layouts.app')

@section('title', 'Product Detail')

@section('content')
    <div class="page page-narrow">
        <div class="card">
            <h1>Product Detail</h1>
            <div class="actions">
                <a class="link" href="{{ route('products.index') }}">Back to products</a>
            </div>

            <div id="product-detail-error" class="error"></div>

            <div id="product-detail-content" class="detail-grid" hidden>
                <div class="detail-image-box">
                    <img id="product-detail-image" src="" alt="Product image">
                </div>
                <div>
                    <h2 id="product-detail-name"></h2>
                    <p class="hint" id="product-detail-category"></p>
                    <p class="hint" id="product-detail-status"></p>
                    <p class="detail-price" id="product-detail-price"></p>
                    <p id="product-detail-description"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const productId = @json($productId);

        const errorBox = document.getElementById('product-detail-error');
        const contentBox = document.getElementById('product-detail-content');
        const imageEl = document.getElementById('product-detail-image');
        const nameEl = document.getElementById('product-detail-name');
        const categoryEl = document.getElementById('product-detail-category');
        const statusEl = document.getElementById('product-detail-status');
        const priceEl = document.getElementById('product-detail-price');
        const descriptionEl = document.getElementById('product-detail-description');

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        async function loadProductDetail() {
            errorBox.textContent = '';
            contentBox.hidden = true;

            try {
                const response = await fetch(`/api/product-detail/${productId}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Cannot load product detail.');
                }

                const payload = await response.json();
                const product = payload?.data;

                if (!product) {
                    throw new Error('Invalid product payload.');
                }

                const imageUrl = product.image_url || '';

                nameEl.textContent = product.name ?? '-';
                categoryEl.textContent = `Category: ${product.category?.name ?? '-'}`;
                statusEl.textContent = `Status: ${product.is_active ? 'Active' : 'Hidden'}`;
                priceEl.textContent = `$${Number(product.price ?? 0).toFixed(2)}`;
                descriptionEl.textContent = product.description || 'No description.';

                imageEl.src = imageUrl || 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
                imageEl.alt = escapeHtml(product.name ?? 'Product image');

                contentBox.hidden = false;
            } catch {
                errorBox.textContent = 'Cannot load product detail from API.';
            }
        }

        loadProductDetail();
    </script>
@endpush
