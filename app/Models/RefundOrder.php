<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundOrder extends Model
{
    use HasFactory;

    public function createRefundOrder(Request $request, $refund): bool
    {
        $requestBody = json_decode($request->getContent());

        return DB::table('refund_orders')
            ->insert([
                'refund_order_no'   => $requestBody -> data -> refund_order_no,
                'order_no'          => $requestBody -> data -> operatorTransactionId,
                'masterTxnId'       => $requestBody -> data -> masterTxnId,
                'number'            => $requestBody -> data -> number,
                'refund_charges'    => $refund -> data -> details -> pass -> processingFeeAmount,
                'refund_amount'     => $refund -> data -> details -> pass -> refundAmount,
                'pg_id'             => $requestBody -> data -> pgId,
                'operator'          => $refund -> data -> operatorId,
            ]);
    }

}
