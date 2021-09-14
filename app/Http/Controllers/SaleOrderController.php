<?php

namespace App\Http\Controllers;

use App\Models\SaleOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleOrderController extends Controller
{
    // CREATE NEW ORDER
    public function createNewOrder(Request $request): JsonResponse
    {
        $order = new SaleOrder();
        $newOrder = $order->createOrder($request);
        if ($newOrder) return response()->json(["status" => true, "code" => env("STATUS_ORDER_CREATED"), "order_no" => $request->input('order_no'), "message" => "Order created successfully."]);
        else return response()->json(["status" => false, "code" => env("STATUS_ORDER_CREATION_FAILED"), "error" => "Order can't be created please try again!"]);
    }

    // GET ORDER DETAILS WITH ORDER NUMBER
    public function getOrder(Request $request)
    {
        $order = new SaleOrder();
        return $order->isOrderExist($request->input('order_no'));
    }

    // GET UPCOMING ORDERS
    public function getUpcomingOrders(Request $request): JsonResponse
    {
        $UpcomingOrder = new SaleOrder();
        $upcomingOrders = $UpcomingOrder->getUpcomingOrders($request);

        if (empty($upcomingOrders)) return response()->json(['status' => false, 'code' => env("STATUS_NO_ORDER_FOUND"), 'error' => 'No active orders found!']);
        else return response()->json(['status' => true, 'code' => env("STATUS_FETCHED_ORDER_SUCCESSFULLY"), 'orders' => $upcomingOrders]);

    }

    // GET ALL ORDERS
    public function getAllOrders(Request $request): JsonResponse
    {
        $AllOrder = new SaleOrder();
        $allOrders = $AllOrder->getAllOrders($request);

        if (empty($allOrders)) return response()->json(['status' => false, 'code' => env("STATUS_NO_ORDER_FOUND"), 'error' => 'No active orders found!']);
        else return response()->json(['status' => true, 'code' => env("STATUS_FETCHED_ORDER_SUCCESSFULLY"), 'orders' => $allOrders]);
    }

}
