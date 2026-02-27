<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'client_total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($payload, $request) {
            $items = collect($payload['items']);
            $productIds = $items->pluck('product_id')->all();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $orderItems = [];
            $subtotal = 0.0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => ['One or more selected products are not available for ordering.'],
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;
                $lineTotal = round($unitPrice * $quantity, 2);
                $subtotal += $lineTotal;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $total = round($subtotal, 2);

            if (isset($payload['client_total']) && abs(((float) $payload['client_total']) - $total) > 0.01) {
                throw ValidationException::withMessages([
                    'client_total' => ['Total price is invalid. Please refresh cart and try again.'],
                ]);
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => 'placed',
                'subtotal' => $subtotal,
                'total' => $total,
                'placed_at' => now(),
            ]);

            $order->items()->createMany($orderItems);

            return $order;
        });

        return response()->json([
            'message' => 'Checkout successful',
            'order' => OrderResource::make($order->load('items')),
        ], 201);
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $data = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $data['per_page'] ?? 10;

        $orders = Order::query()
            ->where('user_id', $request->user()->id)
            ->withCount('items')
            ->latest('id')
            ->limit($perPage)
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'You are not allowed to view this order.');
        }

        $order->load('items');

        return OrderResource::make($order);
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4));
    }
}
