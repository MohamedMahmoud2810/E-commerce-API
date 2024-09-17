<?php

namespace App\Services;

use App\DTOs\GetOrderDTO;
use App\DTOs\OrderDTO;
use App\DTOs\StoreOrderDTO;
use App\Notifications\OrderStatusUpdated;
use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $orderRepository;
    protected $cacheTime = 60 * 5;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getUserOrders($user , GetOrderDTO $dto)
    {
        $cacheKey = 'user_orders_' . $user->id;

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($user , $dto) {
            return $this->orderRepository->getUserOrders($user , $dto);
        });
    }

    public function getOrderById($user, $id)
    {
        return $this->orderRepository->getOrderById($user, $id);
    }

    public function createOrder(StoreOrderDTO $orderDTO)
    {
        $order = $this->orderRepository->createOrder([
            'user_id' => $orderDTO->user->id,
            'status' => 'pending',
        ]);

        $order->save();
    
        // Create order details
        foreach ($orderDTO->items as $item) {
            $order->orderDetails()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['quantity'] * $item['price'],
            ]);
        }
    
        // Clear the cache for the user's orders
        $cacheKey = 'user_orders_' . $orderDTO->user->id;
        Cache::forget($cacheKey);
        $order = $order->load('orderDetails.product');
    
        return  $order;
    }
    

    // public function cancelOrder($id)
    // {
    //     return DB::transaction(function () use ($id) {
    //         $order = $this->orderRepository->findByUserId($id, Auth::id());

    //         if ($order->status !== 'pending') {
    //             return ['error' => 'Only pending orders can be canceled', 'status' => 400];
    //         }

    //         $this->orderRepository->updateOrderStatus($order, 'canceled');
    //         Cache::forget('user_orders_' . Auth::id());

    //         return $order;
    //     });
    // }


    public function cancelOrder($id)
    {
        return DB::transaction(function () use ($id) {
                $order = $this->orderRepository->findByUserId($id, Auth::id());

                if (!$order) {
                    return ['error' => 'Order not found', 'status' => 404];
                }

                if ($order->status !== 'pending') {
                    return ['error' => 'Only pending orders can be canceled', 'status' => 400];
                }

                $this->orderRepository->updateOrderStatus($order, 'canceled');
                Cache::forget('user_orders_' . Auth::id());
                $order->refresh();

                return $order;
            
        });
    }





    public function updateOrderStatus($orderId, $status)
{
    $user = Auth::user();

    if (!$user) {
        return ['error' => 'User not authenticated', 'status' => 401];
    }
    
    $order = $this->orderRepository->findByUserId($orderId, $user->id);

    if (!$order) {
        return ['error' => 'Order not found', 'status' => 404];
    }

    $order = $this->orderRepository->updateOrderStatus($order, $status);

    if (!$order) {
        return ['error' => 'Failed to update order', 'status' => 500];
    }

    $order->user->notify(new OrderStatusUpdated($order));

    $cacheKey = 'user_orders_' . $order->user->id;
    Cache::forget($cacheKey);

    return $order;
}

}
