<?php

namespace App\OpenApi;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         title="Laravel Test API",
 *         version="1.0.0",
 *         description="API documentation for auth, catalog, orders, exports, and admin management features."
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_HOST,
 *         description="Application base URL"
 *     )
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Paste raw token from /api/login or /api/register (without Bearer prefix)"
 * )
 *
 * @OA\Tag(name="Author", description="Authentication")
 * @OA\Tag(name="Public", description="Public product/category APIs")
 * @OA\Tag(name="Orders", description="Order checkout and tracking")
 * @OA\Tag(name="Exports", description="Async product exports")
 * @OA\Tag(name="Admin Categories", description="Category management for admins")
 * @OA\Tag(name="Admin Products", description="Product management for admins")
 * @OA\Tag(name="Admin Users", description="User listing for admins")
 *
 * @OA\Schema(
 *     schema="MessageResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Operation successful")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         additionalProperties=@OA\Schema(type="array", @OA\Items(type="string"))
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Cafe Manager"),
 *     @OA\Property(property="email", type="string", format="email", example="manager@example.com"),
 *     @OA\Property(property="is_admin", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="RegisterRequest",
 *     type="object",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="Nguyen Van A"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="user@example.com"),
 *     @OA\Property(property="password", type="string", minLength=8, example="secret123")
 * )
 *
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="password", type="string", example="secret123")
 * )
 *
 * @OA\Schema(
 *     schema="AuthSuccess",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Login successful"),
 *     @OA\Property(property="token", type="string", example="1|long-sanctum-token"),
 *     @OA\Property(property="token_type", type="string", example="Bearer"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 *
 * @OA\Schema(
 *     schema="Category",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="name", type="string", example="Coffee"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Coffee-based drinks")
 * )
 *
 * @OA\Schema(
 *     schema="CategoryCreateUpdateRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", maxLength=100, example="Tea"),
 *     @OA\Property(property="description", type="string", nullable=true, maxLength=255, example="Tea and herbal drinks")
 * )
 *
 * @OA\Schema(
 *     schema="PaginationLinks",
 *     type="object",
 *     @OA\Property(property="first", type="string", nullable=true),
 *     @OA\Property(property="last", type="string", nullable=true),
 *     @OA\Property(property="prev", type="string", nullable=true),
 *     @OA\Property(property="next", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     type="object",
 *     @OA\Property(property="current_page", type="integer", example=1),
 *     @OA\Property(property="from", type="integer", nullable=true, example=1),
 *     @OA\Property(property="last_page", type="integer", example=3),
 *     @OA\Property(
 *         property="links",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="url", type="string", nullable=true),
 *             @OA\Property(property="label", type="string"),
 *             @OA\Property(property="active", type="boolean")
 *         )
 *     ),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="per_page", type="integer", example=10),
 *     @OA\Property(property="to", type="integer", nullable=true, example=10),
 *     @OA\Property(property="total", type="integer", example=24)
 * )
 *
 * @OA\Schema(
 *     schema="CategoryListResponse",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Category")),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 *
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=101),
 *     @OA\Property(property="name", type="string", example="Espresso"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Strong and rich coffee"),
 *     @OA\Property(property="price", type="number", format="float", example=2.5),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="image_path", type="string", nullable=true, example="products/espresso.png"),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="/storage/products/espresso.png"),
 *     @OA\Property(property="category", ref="#/components/schemas/Category"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ProductListResponse",
 *     type="object",
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Product")),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 *
 * @OA\Schema(
 *     schema="ProductCreateUpdateRequest",
 *     type="object",
 *     required={"name", "category_id", "price"},
 *     @OA\Property(property="name", type="string", maxLength=120, example="Cappuccino"),
 *     @OA\Property(property="category_id", type="integer", example=10),
 *     @OA\Property(property="price", type="number", format="float", minimum=0, example=3.75),
 *     @OA\Property(property="description", type="string", nullable=true, maxLength=255, example="Milk foam and espresso"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="image", type="string", format="binary")
 * )
 *
 * @OA\Schema(
 *     schema="OrderItemInput",
 *     type="object",
 *     required={"product_id", "quantity"},
 *     @OA\Property(property="product_id", type="integer", example=101),
 *     @OA\Property(property="quantity", type="integer", minimum=1, maximum=100, example=2)
 * )
 *
 * @OA\Schema(
 *     schema="CheckoutRequest",
 *     type="object",
 *     required={"items"},
 *     @OA\Property(property="items", type="array", minItems=1, @OA\Items(ref="#/components/schemas/OrderItemInput")),
 *     @OA\Property(property="client_total", type="number", format="float", nullable=true, example=7.5)
 * )
 *
 * @OA\Schema(
 *     schema="OrderItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_id", type="integer", example=101),
 *     @OA\Property(property="product_name", type="string", example="Espresso"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=2.5),
 *     @OA\Property(property="line_total", type="number", format="float", example=5.0)
 * )
 *
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-20260303090001-ABCD"),
 *     @OA\Property(property="user_id", type="integer", example=2),
 *     @OA\Property(property="status", type="string", example="placed"),
 *     @OA\Property(property="subtotal", type="number", format="float", example=7.5),
 *     @OA\Property(property="total", type="number", format="float", example=7.5),
 *     @OA\Property(property="placed_at", type="string", format="date-time"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(
 *         property="user",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string", format="email")
 *     ),
 *     @OA\Property(property="items_count", type="integer", nullable=true, example=2),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItem"))
 * )
 *
 * @OA\Schema(
 *     schema="OrderListResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", nullable=true, example="This account has no order."),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order"))
 * )
 *
 * @OA\Schema(
 *     schema="CheckoutSuccess",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Checkout successful"),
 *     @OA\Property(property="order", ref="#/components/schemas/Order")
 * )
 *
 * @OA\Schema(
 *     schema="ProductExport",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=5),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="format", type="string", enum={"csv", "xlsx"}, example="xlsx"),
 *     @OA\Property(property="file_name", type="string", nullable=true, example="products_export_20260303.xlsx"),
 *     @OA\Property(property="file_path", type="string", nullable=true, example="private/exports/products_export_20260303.xlsx"),
 *     @OA\Property(property="error_message", type="string", nullable=true),
 *     @OA\Property(property="finished_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="ProductExportStoreRequest",
 *     type="object",
 *     @OA\Property(property="format", type="string", enum={"csv", "xlsx"}, default="xlsx")
 * )
 *
 * @OA\Schema(
 *     schema="ProductExportStoreResponse",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Export request accepted. Please check status later."),
 *     @OA\Property(property="data", ref="#/components/schemas/ProductExport")
 * )
 *
 * @OA\Schema(
 *     schema="AdminUsersResponse",
 *     type="object",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Admin User"),
 *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *             @OA\Property(property="is_admin", type="boolean", example=true),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *     @OA\Property(property="links", ref="#/components/schemas/PaginationLinks"),
 *     @OA\Property(property="meta", ref="#/components/schemas/PaginationMeta")
 * )
 *
 * @OA\Post(
 *     path="/api/register",
 *     tags={"Author"},
 *     operationId="register",
 *     summary="Register a new account",
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/RegisterRequest")),
 *     @OA\Response(response=201, description="Register success", @OA\JsonContent(ref="#/components/schemas/AuthSuccess")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Post(
 *     path="/api/login",
 *     tags={"Author"},
 *     operationId="login",
 *     summary="Login and issue Sanctum token",
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/LoginRequest")),
 *     @OA\Response(response=200, description="Login success", @OA\JsonContent(ref="#/components/schemas/AuthSuccess")),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Post(
 *     path="/api/logout",
 *     tags={"Author"},
 *     operationId="logout",
 *     summary="Logout current user from all tokens",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Logout success", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/me",
 *     tags={"Author"},
 *     operationId="me",
 *     summary="Get current authenticated user",
 *     security={{"sanctum":{}}},
 *     @OA\Response(response=200, description="Current user", @OA\JsonContent(ref="#/components/schemas/User")),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/categories",
 *     tags={"Public"},
 *     operationId="publicCategories",
 *     summary="List categories",
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50, default=10)),
 *     @OA\Response(response=200, description="Category list", @OA\JsonContent(ref="#/components/schemas/CategoryListResponse"))
 * )
 *
 * @OA\Post(
 *     path="/api/categories",
 *     tags={"Admin Categories"},
 *     operationId="createCategory",
 *     summary="Create category",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryCreateUpdateRequest")),
 *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Category")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/api/products",
 *     tags={"Public"},
 *     operationId="publicProducts",
 *     summary="List active products",
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50, default=10)),
 *     @OA\Response(response=200, description="Product list", @OA\JsonContent(ref="#/components/schemas/ProductListResponse"))
 * )
 *
 * @OA\Post(
 *     path="/api/products",
 *     tags={"Admin Products"},
 *     operationId="createProduct",
 *     summary="Create product",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/ProductCreateUpdateRequest")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/api/product-detail/{product}",
 *     tags={"Public"},
 *     operationId="productDetail",
 *     summary="Get public product detail",
 *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Product detail", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=404, description="Not found")
 * )
 *
 * @OA\Get(
 *     path="/api/admin/categories",
 *     tags={"Admin Categories"},
 *     operationId="adminCategories",
 *     summary="List categories for admin",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50, default=10)),
 *     @OA\Response(response=200, description="Category list", @OA\JsonContent(ref="#/components/schemas/CategoryListResponse")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Get(
 *     path="/api/admin/products",
 *     tags={"Admin Products"},
 *     operationId="adminProducts",
 *     summary="List products for admin",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="category_id", in="query", @OA\Schema(type="integer")),
 *     @OA\Parameter(name="min_price", in="query", @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="max_price", in="query", @OA\Schema(type="number", format="float")),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50, default=10)),
 *     @OA\Response(response=200, description="Product list", @OA\JsonContent(ref="#/components/schemas/ProductListResponse")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Get(
 *     path="/api/admin/users",
 *     tags={"Admin Users"},
 *     operationId="adminUsers",
 *     summary="List users for admin",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="q", in="query", @OA\Schema(type="string")),
 *     @OA\Parameter(name="is_admin", in="query", @OA\Schema(type="integer", enum={0,1})),
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50, default=10)),
 *     @OA\Response(response=200, description="User list", @OA\JsonContent(ref="#/components/schemas/AdminUsersResponse")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Put(
 *     path="/api/categories/{category}",
 *     tags={"Admin Categories"},
 *     operationId="updateCategory",
 *     summary="Update category",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryCreateUpdateRequest")),
 *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Category")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Patch(
 *     path="/api/categories/{category}",
 *     tags={"Admin Categories"},
 *     operationId="patchCategory",
 *     summary="Partially update category",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CategoryCreateUpdateRequest")),
 *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Category")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Delete(
 *     path="/api/categories/{category}",
 *     tags={"Admin Categories"},
 *     operationId="deleteCategory",
 *     summary="Delete category",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="category", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Put(
 *     path="/api/products/{product}",
 *     tags={"Admin Products"},
 *     operationId="updateProduct",
 *     summary="Update product",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/ProductCreateUpdateRequest")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Patch(
 *     path="/api/products/{product}",
 *     tags={"Admin Products"},
 *     operationId="patchProduct",
 *     summary="Partially update product",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(ref="#/components/schemas/ProductCreateUpdateRequest")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Product")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Delete(
 *     path="/api/products/{product}",
 *     tags={"Admin Products"},
 *     operationId="deleteProduct",
 *     summary="Delete product",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="product", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Deleted", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Post(
 *     path="/api/orders/checkout",
 *     tags={"Orders"},
 *     operationId="checkout",
 *     summary="Create order from cart items",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CheckoutRequest")),
 *     @OA\Response(response=201, description="Checkout success", @OA\JsonContent(ref="#/components/schemas/CheckoutSuccess")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/api/orders",
 *     tags={"Orders"},
 *     operationId="listOrders",
 *     summary="List orders of current user or all if admin",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", minimum=1, maximum=50, default=10)),
 *     @OA\Response(response=200, description="Order list", @OA\JsonContent(ref="#/components/schemas/OrderListResponse")),
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 *
 * @OA\Get(
 *     path="/api/orders/{order}",
 *     tags={"Orders"},
 *     operationId="showOrder",
 *     summary="Get order detail",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Order detail", @OA\JsonContent(ref="#/components/schemas/Order")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Post(
 *     path="/api/exports/products",
 *     tags={"Exports"},
 *     operationId="requestProductExport",
 *     summary="Queue product export",
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(required=false, @OA\JsonContent(ref="#/components/schemas/ProductExportStoreRequest")),
 *     @OA\Response(response=202, description="Accepted", @OA\JsonContent(ref="#/components/schemas/ProductExportStoreResponse")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=422, description="Validation error", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
 * )
 *
 * @OA\Get(
 *     path="/api/exports/products/{productExport}",
 *     tags={"Exports"},
 *     operationId="showProductExport",
 *     summary="Get export job status",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="productExport", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Export status", @OA\JsonContent(ref="#/components/schemas/ProductExport")),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 *
 * @OA\Get(
 *     path="/api/exports/products/{productExport}/download",
 *     tags={"Exports"},
 *     operationId="downloadProductExport",
 *     summary="Download completed export file",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(name="productExport", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(
 *         response=200,
 *         description="File download",
 *         @OA\MediaType(mediaType="application/octet-stream", @OA\Schema(type="string", format="binary"))
 *     ),
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="File not found", @OA\JsonContent(ref="#/components/schemas/MessageResponse")),
 *     @OA\Response(response=422, description="File not ready", @OA\JsonContent(ref="#/components/schemas/MessageResponse"))
 * )
 */
class ApiDocumentation
{
}
