<?php

namespace Tests\Feature;

use App\Models\Category;
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
}
