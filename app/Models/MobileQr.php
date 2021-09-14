<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MobileQr extends Model
{
    use HasFactory;

    // CREATE MOBILE QR CODE OR SVP OR TP
    public function crateMobileQr(Request $request, $masterTrxId, $qr)
    {
        $requestBody = json_decode($request->getContent());

        $source = ($requestBody->data->source != null) ? $requestBody->data->source : null;
        $destination = ($requestBody->data->destination != null) ? $requestBody->data->destination : null;

        DB::table('mobile_qrs')
            ->insert([
                'order_no' => $requestBody->data->operatorTransactionId,
                'number' => $requestBody->data->mobile,
                'source' => $source,
                'destination' => $destination,
                'type' => $requestBody->data->tokenType,
                'masterTxnId' => $masterTrxId,
                'slave_qr_code' => $qr->qrCodeId,
                'qr_direction' => $qr->type,
                'qr_code_data' => $qr->qrCodeData,
                'qr_status' => $qr->tokenStatus,
                'slave_expiry_date' => date('y-m-d h:i:m', $qr->expiryTime)
            ]);
    }

    // GET ACTIVE QR
    public function getActiveQr(Request $request): Collection
    {
        return DB::table('mobile_qrs')
            ->where('order_no', '=', $request->input('order_no'))
            ->where('qr_status', '=', "GENERATED")
            ->orWhere('qr_status', '=', "IN_JOURNEY")
            ->get();
    }

    // GET QR FROM ORDER NUMBER
    public function getQrFromOrderNo($order_no): Collection
    {
        return DB::table('mobile_qrs')
            ->where('order_no', '=', $order_no)
            ->get();
    }

    // UPDATE QR CODE STATUS
    public function updateQrStatus($order_no, $status): int
    {
        return DB::table('mobile_qrs')
            ->where('order_no', '=', $order_no)
            ->update([
                'qr_status' => $status
            ]);
    }

    // UPDATE STATUS WITH SLAVE ID
    public function updateQrStatusWithSlaveId($status, $slaveId): int
    {
        return DB::table('mobile_qrs')
            ->where('slave_qr_code', '=', $slaveId)
            ->update([
                'qr_status' => $status
            ]);
    }

}
