<?php

namespace App\Http\Controllers;

use App\DTOs\GetOrderDTO;
use App\DTOs\OrderDTO;
use App\DTOs\StoreOrderDTO;
use App\Http\Requests\GetOrderRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Notifications\OrderStatusUpdated;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Schema(
 *     schema="OrderDetail",
 *     type="object",
 *     title="Order Detail",
 *     description="Order detail information for products in an order",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="ID of the order detail"
 *     ),
 *     @OA\Property(
 *         property="order_id",
 *         type="integer",
 *         example=1,
 *         description="ID of the related order"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         example=12,
 *         description="ID of the product"
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         example=3,
 *         description="Quantity of the product ordered"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         example=99.99,
 *         description="Price of the product"
 *     ),
 *     @OA\Property(
 *         property="total",
 *         type="number",
 *         format="float",
 *         example=299.97,
 *         description="Total cost for the product (quantity * price)"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-09-10T15:03:27.000000Z",
 *         description="Timestamp of when the order detail was created"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-09-10T15:03:27.000000Z",
 *         description="Timestamp of when the order detail was last updated"
 *     ),
 * )
 */

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

     /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Get list of orders",
     *     description="Returns list of orders for the authenticated user",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(GetOrderRequest $request)
    {
       $dto = new GetOrderDTO($request->all());
       $orders = $this->orderService->getUserOrders(Auth::user() , $dto);
       return OrderResource::collection($orders);
    }

     /**
     * @OA\Get(
     *     path="/api/orders/{id}",
     *     tags={"Orders"},
     *     summary="Get order details",
     *     description="Returns order details for the given ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found"
     *     )
     * )
     */
    public function show($id)
    {
        $user = Auth::user();
        $order = $this->orderService->getOrderById($user, $id);
        return new OrderResource($order);
    }

     /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Creates a new order for the authenticated user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreOrderDTO")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     )
     * )
     */
    public function store(OrderRequest $request)
    {
        $user = Auth::user();
        $orderDTO = new StoreOrderDTO($user, $request->all());
        $order = $this->orderService->createOrder($orderDTO);

        return new OrderResource($order);
    }

      /**
     * @OA\Post(
     *     path="/api/orders/{id}/cancel",
     *     tags={"Orders"},
     *     summary="Cancel an order",
     *     description="Cancels an order with the given ID if it's pending",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order canceled successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Only pending orders can be canceled"
     *     )
     * )
     */
    public function cancel($id)
    {
        $order = $this->orderService->cancelOrder($id);

        if (isset($order['error'])) {
            return response()->json(['message' => $order['error']], $order['status']);
        }

        return new OrderResource($order);
    }

 /**
     * @OA\Patch(
     *     path="/api/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Update order status",
     *     description="Updates the status of an order",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Order ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="New status of the order"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     )
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $order = $this->orderService->updateOrderStatus($id, $request->status, $user);
        return new OrderResource($order);
    }


}
