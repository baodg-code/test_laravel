@extends('layouts.app')

@section('title', 'Admin Panel')

@section('content')
    <div class="page page-narrow page-tall">
        <div class="card card-large">
            <div class="admin-header">
                <div>
                    <h1>Admin Panel</h1>
                    <p>Welcome, admin. This page is protected by the admin middleware.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="link" type="submit">Logout</button>
                </form>
            </div>

            <div class="tabs">
                <a class="tab-button {{ $tab === 'categories' ? 'active' : '' }}" href="{{ route('admin.index', ['tab' => 'categories']) }}">Categories</a>
                <a class="tab-button {{ $tab === 'products' ? 'active' : '' }}" href="{{ route('admin.index', ['tab' => 'products']) }}">Products</a>
                <a class="tab-button {{ $tab === 'users' ? 'active' : '' }}" href="{{ route('admin.index', ['tab' => 'users']) }}">Users</a>
            </div>

            @if ($tab === 'categories')
                <div class="actions">
                    <a class="button" href="{{ route('categories.create') }}">+ Add category</a>
                </div>
                <div class="card filter-box">
                    <div class="filter-grid filter-grid-1">
                        <div>
                            <label for="category-q">Search category</label>
                            <input id="category-q" type="text" placeholder="Category name">
                        </div>
                    </div>
                    <div class="actions actions-tight">
                        <button id="btn-apply-category" type="button">Apply</button>
                        <button id="btn-reset-category" type="button">Reset</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-categories-body">
                        <tr>
                            <td class="empty" colspan="3">Loading...</td>
                        </tr>
                    </tbody>
                </table>
                <div id="admin-categories-summary" class="products-summary"></div>
                <div id="admin-categories-pagination" class="pagination-wrap"></div>
            @endif

            @if ($tab === 'products')
                <div class="actions">
                    <a class="button" href="{{ route('products.create') }}">+ Add product</a>
                </div>
                <div class="card filter-box">
                    <div class="filter-grid filter-grid-4">
                        <div>
                            <label for="product-q">Search</label>
                            <input id="product-q" type="text" placeholder="Name or description">
                        </div>
                        <div>
                            <label for="product-category">Category</label>
                            <select id="product-category">
                                <option value="">All</option>
                            </select>
                        </div>
                        <div>
                            <label for="product-min-price">Min price</label>
                            <input id="product-min-price" type="number" min="0" step="0.01">
                        </div>
                        <div>
                            <label for="product-max-price">Max price</label>
                            <input id="product-max-price" type="number" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="actions actions-tight">
                        <button id="btn-apply-product" type="button">Apply</button>
                        <button id="btn-reset-product" type="button">Reset</button>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-products-body">
                        <tr>
                            <td class="empty" colspan="6">Loading...</td>
                        </tr>
                    </tbody>
                </table>
                <div id="admin-products-summary" class="products-summary"></div>
                <div id="admin-products-pagination" class="pagination-wrap"></div>
            @endif

            @if ($tab === 'users')
                <div class="actions">
                    <a class="button" href="{{ route('users.create') }}">+ Add user</a>
                </div>
                <div class="card filter-box">
                    <div class="filter-grid filter-grid-2">
                        <div>
                            <label for="user-q">Search user</label>
                            <input id="user-q" type="text" placeholder="Name or email">
                        </div>
                        <div>
                            <label for="user-role">Role</label>
                            <select id="user-role">
                                <option value="">All</option>
                                <option value="1">Admin</option>
                                <option value="0">User</option>
                            </select>
                        </div>
                    </div>
                    <div class="actions actions-tight">
                        <button id="btn-apply-user" type="button">Apply</button>
                        <button id="btn-reset-user" type="button">Reset</button>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="admin-users-body">
                        <tr>
                            <td class="empty" colspan="4">Loading...</td>
                        </tr>
                    </tbody>
                </table>
                <div id="admin-users-summary" class="products-summary"></div>
                <div id="admin-users-pagination" class="pagination-wrap"></div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const tab = @json($tab);
        const csrfToken = @json(csrf_token());
        const apiToken = @json(session('api_token', ''));

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function apiFetch(url) {
            const headers = {
                Accept: 'application/json',
            };

            if (apiToken) {
                headers.Authorization = `Bearer ${apiToken}`;
            }

            return fetch(url, { headers });
        }

        function renderPagination(container, meta, onChangePage) {
            if (!meta || !meta.last_page || meta.last_page <= 1) {
                container.innerHTML = '';
                return;
            }

            const current = Number(meta.current_page);
            const last = Number(meta.last_page);

            container.innerHTML = Array.from({ length: last }, (_, index) => {
                const page = index + 1;
                const activeClass = page === current ? ' active' : '';

                return `<button type="button" class="page-btn${activeClass}" data-page="${page}">${page}</button>`;
            }).join('');

            container.querySelectorAll('button[data-page]').forEach((button) => {
                button.addEventListener('click', () => {
                    onChangePage(button.dataset.page || '1');
                });
            });
        }

        function stateFromUrl() {
            const params = new URLSearchParams(window.location.search);
            const common = {
                tab,
                per_page: '10',
                page: params.get('page') ?? '1',
            };

            if (tab === 'categories') {
                return {
                    ...common,
                    q: params.get('q') ?? '',
                };
            }

            if (tab === 'products') {
                return {
                    ...common,
                    q: params.get('q') ?? '',
                    category_id: params.get('category_id') ?? '',
                    min_price: params.get('min_price') ?? '',
                    max_price: params.get('max_price') ?? '',
                };
            }

            return {
                ...common,
                q: params.get('q') ?? '',
                is_admin: params.get('is_admin') ?? '',
            };
        }

        function toQueryString(state) {
            const params = new URLSearchParams();
            params.set('tab', tab);

            Object.entries(state).forEach(([key, value]) => {
                if (key !== 'tab' && value !== '' && value !== null && value !== undefined) {
                    params.set(key, value);
                }
            });

            return params.toString();
        }

        function applyState(state, pushHistory = false) {
            const query = toQueryString(state);
            const nextUrl = `${window.location.pathname}?${query}`;

            if (pushHistory) {
                window.history.pushState({}, '', nextUrl);
            } else {
                window.history.replaceState({}, '', nextUrl);
            }

            syncInputs(state);
            loadTabData();
        }

        function syncInputs(state) {
            if (tab === 'categories') {
                document.getElementById('category-q').value = state.q;
            } else if (tab === 'products') {
                document.getElementById('product-q').value = state.q;
                document.getElementById('product-category').value = state.category_id;
                document.getElementById('product-min-price').value = state.min_price;
                document.getElementById('product-max-price').value = state.max_price;
            } else if (tab === 'users') {
                document.getElementById('user-q').value = state.q;
                document.getElementById('user-role').value = state.is_admin;
            }
        }

        async function loadCategoriesOptions() {
            if (tab !== 'products') {
                return;
            }

            const categorySelect = document.getElementById('product-category');
            categorySelect.innerHTML = '<option value="">All</option>';

            try {
                const response = await apiFetch('/api/admin/categories?per_page=50');
                if (!response.ok) {
                    return;
                }

                const payload = await response.json();
                const items = payload.data ?? [];

                items.forEach((category) => {
                    const option = document.createElement('option');
                    option.value = String(category.id);
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
            } catch {
                // Keep default option only.
            }
        }

        async function loadCategoryRows(state) {
            const body = document.getElementById('admin-categories-body');
            const summary = document.getElementById('admin-categories-summary');
            const pagination = document.getElementById('admin-categories-pagination');

            const params = new URLSearchParams({
                per_page: state.per_page,
                page: state.page,
            });

            if (state.q) {
                params.set('q', state.q);
            }

            body.innerHTML = '<tr><td class="empty" colspan="3">Loading...</td></tr>';

            try {
                const response = await apiFetch(`/api/admin/categories?${params.toString()}`);
                if (!response.ok) {
                    throw new Error('Failed to load categories');
                }

                const payload = await response.json();
                const items = payload.data ?? [];
                const meta = payload.meta ?? null;

                if (!items.length) {
                    body.innerHTML = '<tr><td class="empty" colspan="3">No categories yet.</td></tr>';
                } else {
                    body.innerHTML = items.map((category) => `
                        <tr>
                            <td>${escapeHtml(category.name)}</td>
                            <td>${escapeHtml(category.description ?? '-')}</td>
                            <td>
                                <a class="link" href="/categories/${category.id}/edit">Edit</a>
                                <form method="POST" action="/categories/${category.id}" class="inline-form">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    `).join('');
                }

                if (meta) {
                    summary.textContent = `Showing ${meta.from ?? 0}-${meta.to ?? 0} of ${meta.total ?? 0} categories`;
                } else {
                    summary.textContent = '';
                }

                renderPagination(pagination, meta, (page) => {
                    const nextState = stateFromUrl();
                    nextState.page = page;
                    applyState(nextState, true);
                });
            } catch {
                body.innerHTML = '<tr><td class="empty" colspan="3">Cannot load categories from API.</td></tr>';
                summary.textContent = '';
                pagination.innerHTML = '';
            }
        }

        async function loadProductRows(state) {
            const body = document.getElementById('admin-products-body');
            const summary = document.getElementById('admin-products-summary');
            const pagination = document.getElementById('admin-products-pagination');

            const params = new URLSearchParams({
                per_page: state.per_page,
                page: state.page,
            });

            if (state.q) params.set('q', state.q);
            if (state.category_id) params.set('category_id', state.category_id);
            if (state.min_price) params.set('min_price', state.min_price);
            if (state.max_price) params.set('max_price', state.max_price);

            body.innerHTML = '<tr><td class="empty" colspan="6">Loading...</td></tr>';

            try {
                const response = await apiFetch(`/api/admin/products?${params.toString()}`);
                if (!response.ok) {
                    throw new Error('Failed to load products');
                }

                const payload = await response.json();
                const items = payload.data ?? [];
                const meta = payload.meta ?? null;

                if (!items.length) {
                    body.innerHTML = '<tr><td class="empty" colspan="6">No products yet.</td></tr>';
                } else {
                    body.innerHTML = items.map((product) => {
                        const imageCell = product.image_url
                            ? `<img src="${escapeHtml(product.image_url)}" alt="${escapeHtml(product.name)}">`
                            : '-';

                        return `
                            <tr>
                                <td>${imageCell}</td>
                                <td>${escapeHtml(product.name)}</td>
                                <td>${escapeHtml(product.category?.name ?? '-')}</td>
                                <td>$${Number(product.price).toFixed(2)}</td>
                                <td>${product.is_active ? 'Active' : 'Hidden'}</td>
                                <td>
                                    <a class="link" href="/products/${product.id}/edit">Edit</a>
                                    <form method="POST" action="/products/${product.id}" class="inline-form">
                                        <input type="hidden" name="_token" value="${csrfToken}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                if (meta) {
                    summary.textContent = `Showing ${meta.from ?? 0}-${meta.to ?? 0} of ${meta.total ?? 0} products`;
                } else {
                    summary.textContent = '';
                }

                renderPagination(pagination, meta, (page) => {
                    const nextState = stateFromUrl();
                    nextState.page = page;
                    applyState(nextState, true);
                });
            } catch {
                body.innerHTML = '<tr><td class="empty" colspan="6">Cannot load products from API.</td></tr>';
                summary.textContent = '';
                pagination.innerHTML = '';
            }
        }

        async function loadUserRows(state) {
            const body = document.getElementById('admin-users-body');
            const summary = document.getElementById('admin-users-summary');
            const pagination = document.getElementById('admin-users-pagination');

            const params = new URLSearchParams({
                per_page: state.per_page,
                page: state.page,
            });

            if (state.q) params.set('q', state.q);
            if (state.is_admin !== '') params.set('is_admin', state.is_admin);

            body.innerHTML = '<tr><td class="empty" colspan="4">Loading...</td></tr>';

            try {
                const response = await apiFetch(`/api/admin/users?${params.toString()}`);
                if (!response.ok) {
                    throw new Error('Failed to load users');
                }

                const payload = await response.json();
                const items = payload.data ?? [];
                const meta = payload.meta ?? null;

                if (!items.length) {
                    body.innerHTML = '<tr><td class="empty" colspan="4">No users yet.</td></tr>';
                } else {
                    body.innerHTML = items.map((user) => `
                        <tr>
                            <td>${escapeHtml(user.name)}</td>
                            <td>${escapeHtml(user.email)}</td>
                            <td>${user.is_admin ? 'Admin' : 'User'}</td>
                            <td>
                                <a class="link" href="/users/${user.id}/edit">Edit</a>
                                <form method="POST" action="/users/${user.id}" class="inline-form">
                                    <input type="hidden" name="_token" value="${csrfToken}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button class="danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    `).join('');
                }

                if (meta) {
                    summary.textContent = `Showing ${meta.from ?? 0}-${meta.to ?? 0} of ${meta.total ?? 0} users`;
                } else {
                    summary.textContent = '';
                }

                renderPagination(pagination, meta, (page) => {
                    const nextState = stateFromUrl();
                    nextState.page = page;
                    applyState(nextState, true);
                });
            } catch {
                body.innerHTML = '<tr><td class="empty" colspan="4">Cannot load users from API.</td></tr>';
                summary.textContent = '';
                pagination.innerHTML = '';
            }
        }

        async function loadTabData() {
            const state = stateFromUrl();

            if (tab === 'categories') {
                await loadCategoryRows(state);
                return;
            }

            if (tab === 'products') {
                await loadProductRows(state);
                return;
            }

            await loadUserRows(state);
        }

        function bindActions() {
            if (tab === 'categories') {
                document.getElementById('btn-apply-category').addEventListener('click', () => {
                    const state = {
                        ...stateFromUrl(),
                        q: document.getElementById('category-q').value.trim(),
                        page: '1',
                    };

                    applyState(state, true);
                });

                document.getElementById('btn-reset-category').addEventListener('click', () => {
                    const state = {
                        tab,
                        q: '',
                        per_page: '10',
                        page: '1',
                    };

                    applyState(state, true);
                });
            } else if (tab === 'products') {
                document.getElementById('btn-apply-product').addEventListener('click', () => {
                    const state = {
                        ...stateFromUrl(),
                        q: document.getElementById('product-q').value.trim(),
                        category_id: document.getElementById('product-category').value,
                        min_price: document.getElementById('product-min-price').value,
                        max_price: document.getElementById('product-max-price').value,
                        page: '1',
                    };

                    applyState(state, true);
                });

                document.getElementById('btn-reset-product').addEventListener('click', () => {
                    const state = {
                        tab,
                        q: '',
                        category_id: '',
                        min_price: '',
                        max_price: '',
                        per_page: '10',
                        page: '1',
                    };

                    applyState(state, true);
                });
            } else if (tab === 'users') {
                document.getElementById('btn-apply-user').addEventListener('click', () => {
                    const state = {
                        ...stateFromUrl(),
                        q: document.getElementById('user-q').value.trim(),
                        is_admin: document.getElementById('user-role').value,
                        page: '1',
                    };

                    applyState(state, true);
                });

                document.getElementById('btn-reset-user').addEventListener('click', () => {
                    const state = {
                        tab,
                        q: '',
                        is_admin: '',
                        per_page: '10',
                        page: '1',
                    };

                    applyState(state, true);
                });
            }
        }

        window.addEventListener('popstate', () => {
            syncInputs(stateFromUrl());
            loadTabData();
        });

        (async function init() {
            await loadCategoriesOptions();
            syncInputs(stateFromUrl());
            bindActions();
            await loadTabData();
        })();
    </script>
@endpush
