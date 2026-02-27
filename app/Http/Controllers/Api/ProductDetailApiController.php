<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;

class ProductDetailApiController extends Controller
{
    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        $product->load('category:id,name,description');

        return ProductResource::make($product);
    }
}
