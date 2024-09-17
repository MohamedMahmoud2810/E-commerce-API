<?php


namespace App\Repositories;

use App\DTOs\GetOrderDTO;
use App\Models\Order;

class OrderRepository
{
    public function getUserOrders($user , GetOrderDTO $dto)
    {
        if ($user->hasRole('Customer')) {
            return $user->orders()->with('orderDetails.product')->paginate($dto->per_page);
        } elseif ($user->hasRole('Admin')) {
            return Order::with('orderDetails.product')->paginate($dto->per_page);
        } elseif ($user->hasRole('Vendor')) {
            return Order::where('user_id', $user->id)->with('orderDetails.product')->paginate($dto->per_page);
        }
        return null;
    }

    public function getOrderById($user, $id)
    {
        return Order::where('user_id', $user->id)
                    ->where('id', $id)
                    ->with('orderDetails.product')
                    ->firstOrFail();
    }

    public function createOrder(array $data)
    {
        return Order::create($data);
    }

    public function cancelOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => 'canceled']);
        return $order;
    }

    public function updateOrderStatus(Order $order, $status)
    {
        $order->update(['status' => $status]);
        return $order->fresh(); 
    }


    public function findByUserId($id, $userId)
    {
        return Order::where('id', $id)
                    ->where('user_id', $userId)
                    ->first();
    }
}
