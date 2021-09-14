<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SaleOrder extends Model
{
    use HasFactory;

    // UPCOMING ORDERS
    public function getUpcomingOrders(Request $request): Collection
    {
        return DB::table('sale_orders')
            ->where('number', '=', $request->input('number'))
            ->where('order_status', '=', env("STATUS_ORDER_QR_GENERATED"))
            ->orderBy('timestamp', 'desc')
            ->get();
    }

    // HISTORY ORDERS
    public function getAllOrders(Request $request): Collection
    {
        return DB::table('sale_orders')
            ->where('number', '=', $request->input('number'))
            ->orderBy('timestamp', 'desc')
            ->get();
    }

    // IS ORDER EXIST
    public function isOrderExist($order_no)
    {
        return DB::table('sale_orders')
            ->where('order_no', '=', $order_no)
            ->first();
    }

    // CREATE NEW ORDER
    public function createOrder(Request $request): bool
    {
        return DB::table('sale_orders')
            ->insert([
                'order_no' => $request->input('order_no'),
                'number' => $request->input('number'),
                'source' => $request->input('source'),
                'destination' => $request->input('destination'),
                'type' => $request->input('type'),
                'count' => $request->input('count'),
                'fare' => $request->input('fare'),
                'pg_id' => $request->input('pg_id'),
                'operator' => $request->input('operator'),
                'order_status' => $request->input('order_status'),
                'order_type' => $request->input('order_type')
            ]);
    }

    // UPDATE ORDER STATUS
    public function updateOrderStatus($status, $order_no): int
    {
        return DB::table('sale_orders')
            ->where('order_no', '=', $order_no)
            ->update([
                'order_status' => $status
            ]);
    }

    // UPDATE ORDER MASTER
    public function updateOrderMaster($masterTrxId, $order_no): int
    {
        return DB::table('sale_orders')
            ->where('order_no', '=', $order_no)
            ->update([
                'master_qr_code' => $masterTrxId
            ]);
    }

    /* CRON */

    // GET ORDERS TO UPDATE STATUS
    public function getOrderToUpdateStatus(): Collection
    {
        return DB::table('sale_orders')
            -> where('order_status', '=', 400)
            ->get();
    }

}
