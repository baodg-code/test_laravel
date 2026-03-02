<?php

namespace Tests\Feature;

use App\Jobs\ExportProductsJob;
use App\Jobs\SendOrderCreatedEmailJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductExport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsyncProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_dispatches_email_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $category = Category::create([
            'name' => 'Async Category',
            'description' => 'Async test',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Async Coffee',
            'price' => 9,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/orders/checkout', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertCreated();

        Queue::assertPushed(SendOrderCreatedEmailJob::class);
    }

    public function test_product_export_api_accepts_request_and_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/exports/products');

        $response->assertStatus(202);
        $this->assertDatabaseCount('product_exports', 1);
        $this->assertDatabaseHas('product_exports', [
            'user_id' => $user->id,
            'format' => 'xlsx',
        ]);

        Queue::assertPushed(ExportProductsJob::class);
    }

    public function test_product_export_can_be_requested_as_xlsx(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/exports/products', [
            'format' => 'xlsx',
        ]);

        $response->assertStatus(202);
        $this->assertDatabaseHas('product_exports', [
            'user_id' => $user->id,
            'format' => 'xlsx',
        ]);

        Queue::assertPushed(ExportProductsJob::class);
    }

    public function test_user_cannot_access_other_users_export_status(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $export = ProductExport::create([
            'user_id' => $owner->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($other);

        $this->getJson('/api/exports/products/'.$export->id)
            ->assertForbidden();
    }
}
