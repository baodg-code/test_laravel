<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_total_keeps_old_item_price_after_product_price_changes(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Coffee',
            'description' => 'Coffee drinks',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Latte',
            'price' => 10,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $checkoutResponse = $this->postJson('/api/orders/checkout', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
            'client_total' => 20,
        ]);

        $checkoutResponse->assertCreated();
        $orderId = $checkoutResponse->json('order.id');

        $product->update([
            'price' => 50,
        ]);

        $detailResponse = $this->getJson('/api/orders/'.$orderId);

        $detailResponse
            ->assertOk()
            ->assertJsonPath('data.total', 20)
            ->assertJsonPath('data.items.0.unit_price', 10)
            ->assertJsonPath('data.items.0.line_total', 20);
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $category = Category::create([
            'name' => 'Tea',
            'description' => 'Tea drinks',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Peach Tea',
            'price' => 8,
            'is_active' => true,
        ]);

        Sanctum::actingAs($userA);

        $checkoutResponse = $this->postJson('/api/orders/checkout', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'client_total' => 8,
        ]);

        $checkoutResponse->assertCreated();
        $orderId = $checkoutResponse->json('order.id');

        Sanctum::actingAs($userB);

        $this->getJson('/api/orders/'.$orderId)
            ->assertForbidden();
    }

    public function test_checkout_with_one_valid_and_one_invalid_product_creates_no_order(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'name' => 'Cake',
            'description' => 'Cake menu',
        ]);

        $validProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Tiramisu',
            'price' => 7,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders/checkout', [
            'items' => [
                [
                    'product_id' => $validProduct->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => 999999,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_user_order_history_only_contains_own_orders(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-U1-0001',
            'status' => 'placed',
            'subtotal' => 10,
            'total' => 10,
            'placed_at' => now(),
        ]);

        Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-U2-0001',
            'status' => 'placed',
            'subtotal' => 20,
            'total' => 20,
            'placed_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/orders');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user_id', $user->id)
            ->assertJsonMissingPath('data.1');
    }

    public function test_admin_can_view_all_orders_with_order_owner_info(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orderA = Order::create([
            'user_id' => $userA->id,
            'order_number' => 'ORD-A-0001',
            'status' => 'placed',
            'subtotal' => 15,
            'total' => 15,
            'placed_at' => now(),
        ]);

        $orderB = Order::create([
            'user_id' => $userB->id,
            'order_number' => 'ORD-B-0001',
            'status' => 'placed',
            'subtotal' => 25,
            'total' => 25,
            'placed_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $orderA->id,
                'user_id' => $userA->id,
                'name' => $userA->name,
                'email' => $userA->email,
            ])
            ->assertJsonFragment([
                'id' => $orderB->id,
                'user_id' => $userB->id,
                'name' => $userB->name,
                'email' => $userB->email,
            ]);
    }

    public function test_admin_can_view_another_users_order_detail(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $owner = User::factory()->create();

        $order = Order::create([
            'user_id' => $owner->id,
            'order_number' => 'ORD-DETAIL-0001',
            'status' => 'placed',
            'subtotal' => 30,
            'total' => 30,
            'placed_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $this->getJson('/api/orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.user_id', $owner->id)
            ->assertJsonPath('data.user.id', $owner->id);
    }

    public function test_order_history_returns_message_when_account_has_no_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('message', 'This account has no order.')
            ->assertJsonPath('data', []);
    }
}
