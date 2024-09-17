<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\DTOs\GetOrderDTO;
use App\Repositories\OrderRepository;
use Database\Seeders\RolesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class OrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $orderRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderRepository = new OrderRepository();
        $this->seed(RolesTableSeeder::class);

    }

    public function testGetUserOrdersForCustomer()
    {
        $user = User::factory()->create();
        $user->assignRole('Customer'); // Assuming you use Spatie roles

        $order = Order::factory()->create(['user_id' => $user->id]);

        $dto = new GetOrderDTO(['per_page' => 15]);
        $orders = $this->orderRepository->getUserOrders($user, $dto);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $orders);
        $this->assertCount(1, $orders->items());
        $this->assertEquals($order->id, $orders->items()[0]->id);
    }

    public function testGetUserOrdersForAdmin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin'); // Assuming you use Spatie roles

        $order = Order::factory()->create();

        $dto = new GetOrderDTO(['per_page' => 15]);
        $orders = $this->orderRepository->getUserOrders($admin, $dto);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $orders);
        $this->assertCount(1, $orders->items());
        $this->assertEquals($order->id, $orders->items()[0]->id);
    }

    public function testGetUserOrdersForVendor()
    {
        $vendor = User::factory()->create();
        $vendor->assignRole('Vendor');
        Auth::login($vendor);

        $order = Order::factory()->create(['user_id' => $vendor->id]);

        $dto = new GetOrderDTO(['per_page' => 15]);
        $orders = $this->orderRepository->getUserOrders($vendor, $dto);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $orders);
        $this->assertCount(1, $orders->items());
        $this->assertEquals($order->id, $orders->items()[0]->id);
    }

    public function testGetOrderById()
    {
        $user = User::factory()->create();
        $user->assignRole('Customer');

        $order = Order::factory()->create(['user_id' => $user->id]);

        $retrievedOrder = $this->orderRepository->getOrderById($user, $order->id);

        $this->assertEquals($order->id, $retrievedOrder->id);
    }

    public function testCreateOrder()
    {
        $user = User::factory()->create();
        $user->assignRole('Customer');

        $data = [
            'user_id' => $user->id,
            'status' => 'pending',
        ];

        $order = $this->orderRepository->createOrder($data);

        $this->assertDatabaseHas('orders', $data);
        $this->assertEquals($data['user_id'], $order->user_id);
    }

    public function testCancelOrder()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $canceledOrder = $this->orderRepository->cancelOrder($order->id);

        $this->assertEquals('canceled', $canceledOrder->status);
    }

    public function testUpdateOrderStatus()
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $updatedOrder = $this->orderRepository->updateOrderStatus($order, 'completed');

        $this->assertEquals('completed', $updatedOrder->status);
    }
}
