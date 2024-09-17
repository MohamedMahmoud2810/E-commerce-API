<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use App\Services\OrderService;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed the database with roles and other necessary data
        $this->seed(RolesTableSeeder::class);
        $this->orderService = Mockery::mock(OrderService::class);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndex()
    {
        $user = User::factory()->create();
        Auth::login($user);
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);
        $response = $this->getJson('/api/orders');
        $response->assertStatus(200)
                ->assertJsonFragment(['id' => $orders->first()->id]);
    }

    public function testShow()
    {
        $user = User::factory()->create();
        $user->assignRole('Customer');
        Auth::login($user);

        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $order->id]);
    }

    public function testStore()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $user->assignRole('Customer');
        Auth::login($user);

        $orderData = [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2, 'price' => 10.0],
                ['product_id' => $product2->id, 'quantity' => 1, 'price' => 20.0],
            ],
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                 ->assertJsonFragment(['status' => 'pending']);
    }

    public function testCancel()
    {
        $user = User::factory()->create();
        Auth::shouldReceive('user')->andReturn($user);
        $user->assignRole('Customer');

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->orderService->shouldReceive('cancelOrder')
            ->once()
            ->with($order->id)
            ->andReturn($order->refresh());

            $this->app->instance(OrderService::class, $this->orderService);

        $response = $this->patchJson("/api/orders/{$order->id}/cancel");

        $response->assertStatus(200);
        $this->assertEquals('canceled', $order->refresh()->status);
    }

    public function testUpdateStatus()
    {
        $user = User::factory()->create();
        $user->assignRole('Vendor');
        Auth::login($user);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->putJson("/api/orders/{$order->id}/status", ['status' => 'completed']);

        $response->assertStatus(200)
                ->assertJsonFragment(['status' => 'completed']);
    }
}
