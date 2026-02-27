@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="page">
        <div class="card">
            <h1>Products</h1>
            <div class="actions">
                @if ($isAdmin)
                    <a class="button" href="{{ route('products.create') }}">+ Add product</a>
                    <a class="link" href="{{ route('admin.index', ['tab' => 'products']) }}">Back to admin</a>
                @endif
                @if (!$isAdmin)
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button class="danger" type="submit">Logout</button>
                    </form>
                @endif
            </div>

            <div class="filters-panel">
                <div class="filters-grid">
                    <div>
                        <label for="filter-q">Search</label>
                        <input id="filter-q" type="text" placeholder="Name or description">
                    </div>
                    <div>
                        <label for="filter-category">Category</label>
                        <select id="filter-category">
                            <option value="">All</option>
                        </select>
                    </div>
                    <div>
                        <label for="filter-min-price">Min price</label>
                        <input id="filter-min-price" type="number" min="0" step="0.01">
                    </div>
                    <div>
                        <label for="filter-max-price">Max price</label>
                        <input id="filter-max-price" type="number" min="0" step="0.01">
                    </div>
                </div>
                <div class="actions actions-tight">
                    <button id="btn-apply-filters" type="button">Apply</button>
                    <button id="btn-reset-filters" type="button">Reset</button>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        @if ($isAdmin)
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="products-table-body">
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}">Loading...</td>
                    </tr>
                </tbody>
            </table>

            <div id="products-summary" class="products-summary"></div>
            <div id="products-pagination" class="pagination-wrap"></div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const isAdmin = @json($isAdmin);
        const csrfToken = @json(csrf_token());
        const detailBaseUrl = @json(url('/product-detail'));

        const tableBody = document.getElementById('products-table-body');
        const paginationBox = document.getElementById('products-pagination');
        const summaryBox = document.getElementById('products-summary');

        const filterQ = document.getElementById('filter-q');
        const filterCategory = document.getElementById('filter-category');
        const filterMinPrice = document.getElementById('filter-min-price');
        const filterMaxPrice = document.getElementById('filter-max-price');

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function getQueryState() {
            const params = new URLSearchParams(window.location.search);

            return {
                q: params.get('q') ?? '',
                category_id: params.get('category_id') ?? '',
                min_price: params.get('min_price') ?? '',
                max_price: params.get('max_price') ?? '',
                per_page: '10',
                page: params.get('page') ?? '1',
            };
        }

        function syncInputs(state) {
            filterQ.value = state.q;
            filterCategory.value = state.category_id;
            filterMinPrice.value = state.min_price;
            filterMaxPrice.value = state.max_price;
        }

        function toQueryString(state) {
            const params = new URLSearchParams();

            if (state.q) params.set('q', state.q);
            if (state.category_id) params.set('category_id', state.category_id);
            if (state.min_price) params.set('min_price', state.min_price);
            if (state.max_price) params.set('max_price', state.max_price);
            if (state.per_page) params.set('per_page', state.per_page);
            if (state.page) params.set('page', state.page);

            return params.toString();
        }

        async function loadCategories() {
            try {
                const response = await fetch('/api/categories?per_page=50', {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const payload = await response.json();
                const categories = payload.data ?? [];

                for (const category of categories) {
                    const option = document.createElement('option');
                    option.value = String(category.id);
                    option.textContent = category.name;
                    filterCategory.appendChild(option);
                }
            } catch {
                // keep default "All" option
            }
        }

        function renderRows(items) {
            if (!items.length) {
                tableBody.innerHTML = `<tr><td colspan="${isAdmin ? 6 : 5}">No products found.</td></tr>`;
                return;
            }

            tableBody.innerHTML = items.map((product) => {
                const imageCell = product.image_url
                    ? `<img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name)}">`
                    : '-';

                const actionCell = isAdmin
                    ? `<td>
                           <a class="link" href="/products/${product.id}/edit">Edit</a>
                           <form method="POST" action="/products/${product.id}" class="inline-form">
                               <input type="hidden" name="_token" value="${csrfToken}">
                               <input type="hidden" name="_method" value="DELETE">
                               <button class="danger" type="submit">Delete</button>
                           </form>
                       </td>`
                    : '';

                return `<tr class="row-clickable" data-detail-url="${detailBaseUrl}/${product.id}">
                    <td>${imageCell}</td>
                    <td>${escapeHtml(product.name)}</td>
                    <td>${escapeHtml(product.category?.name ?? '-')}</td>
                    <td>$${Number(product.price).toFixed(2)}</td>
                    <td>${product.is_active ? 'Active' : 'Hidden'}</td>
                    ${actionCell}
                </tr>`;
            }).join('');

            tableBody.querySelectorAll('tr[data-detail-url]').forEach((row) => {
                row.addEventListener('click', (event) => {
                    if (event.target.closest('a, button, form, input, select, textarea')) {
                        return;
                    }

                    const url = row.dataset.detailUrl;
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        }

        function extractPageFromUrl(url) {
            if (!url) return null;
            const u = new URL(url, window.location.origin);
            return u.searchParams.get('page');
        }

        function renderPagination(meta) {
            if (!meta || !meta.last_page || meta.last_page <= 1) {
                paginationBox.innerHTML = '';
                return;
            }

            const current = Number(meta.current_page);
            const last = Number(meta.last_page);

            paginationBox.innerHTML = Array.from({ length: last }, (_, index) => {
                const page = index + 1;
                const activeClass = page === current ? ' active' : '';

                return `<button type="button" class="page-btn${activeClass}" data-page="${page}">${page}</button>`;
            }).join('');

            paginationBox.querySelectorAll('button[data-page]').forEach((button) => {
                button.addEventListener('click', () => {
                    const state = getQueryState();
                    state.page = button.dataset.page || '1';
                    applyState(state, true);
                });
            });
        }

        async function loadProducts() {
            const state = getQueryState();
            const query = toQueryString(state);

            try {
                tableBody.innerHTML = `<tr><td colspan="${isAdmin ? 6 : 5}">Loading...</td></tr>`;

                const response = await fetch(`/api/products?${query}`, {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                const payload = await response.json();

                renderRows(payload.data ?? []);
                renderPagination(payload.meta ?? null);

                if (payload.meta) {
                    summaryBox.textContent = `Showing ${payload.meta.from ?? 0}-${payload.meta.to ?? 0} of ${payload.meta.total ?? 0} products`;
                } else {
                    summaryBox.textContent = '';
                }
            } catch {
                tableBody.innerHTML = `<tr><td colspan="${isAdmin ? 6 : 5}">Cannot load products from API.</td></tr>`;
                paginationBox.innerHTML = '';
                summaryBox.textContent = '';
            }
        }

        function applyState(state, pushHistory = false) {
            const query = toQueryString(state);
            const nextUrl = query ? `${window.location.pathname}?${query}` : window.location.pathname;

            if (pushHistory) {
                window.history.pushState({}, '', nextUrl);
            } else {
                window.history.replaceState({}, '', nextUrl);
            }

            syncInputs(state);
            loadProducts();
        }

        document.getElementById('btn-apply-filters').addEventListener('click', () => {
            const state = {
                q: filterQ.value.trim(),
                category_id: filterCategory.value,
                min_price: filterMinPrice.value,
                max_price: filterMaxPrice.value,
                per_page: '10',
                page: '1',
            };

            applyState(state, true);
        });

        document.getElementById('btn-reset-filters').addEventListener('click', () => {
            const state = {
                q: '',
                category_id: '',
                min_price: '',
                max_price: '',
                per_page: '10',
                page: '1',
            };

            applyState(state, true);
        });

        window.addEventListener('popstate', () => {
            const state = getQueryState();
            syncInputs(state);
            loadProducts();
        });

        (async function init() {
            await loadCategories();
            const state = getQueryState();
            syncInputs(state);
            loadProducts();
        })();
    </script>
@endpush
