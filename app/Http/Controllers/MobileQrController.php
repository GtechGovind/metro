<?php

namespace App\Http\Controllers;

use App\Models\MobileQr;
use App\Models\SaleOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileQrController extends Controller
{

    // GENERATE NEW QR CODE
    public function createNewQr(Request $request)
    {
        $QrResponse = $this->issueToken($request);
        $qrResponse = json_decode($QrResponse);
        $requestBody = json_decode($request->getContent());

        if ($qrResponse->status == "OK") {

            foreach ($qrResponse->data->trips as $trip) {
                $qr = new MobileQr();
                $qr->crateMobileQr($request, $qrResponse->data->masterTxnId, $trip);
            }

            //UPDATE ORDER STATUS AND MASTER
            $order = new SaleOrder();
            $order->updateOrderStatus(env("STATUS_ORDER_QR_GENERATED"), $requestBody->data->operatorTransactionId);
            $order->updateOrderMaster($qrResponse->data->masterTxnId, $requestBody->data->operatorTransactionId);

            $qrNewResponse = json_decode($QrResponse, true);
            $qrNewResponse['order_no'] = $requestBody->data->operatorTransactionId;
            return json_encode($qrNewResponse);

        } else return json_encode($QrResponse);

    }

    // FETCH ALL ACTIVE QR CODES FROM ORDER NUMBER
    public function getQrData(Request $request): JsonResponse
    {
        $Qr = new MobileQr();
        $QrData = $Qr -> getActiveQr($request);

        if (empty($QrData)) return response() -> json(['status' => false, 'code' => env("STATUS_NO_QR_CODE_FOUND"), 'error' => 'No Qrs found']);
        else return response() -> json(['status' => true, 'code' => env('STATUS_QR_CODE_FETCHED_SUCCESSFULLY'), 'qrs' => $QrData]);

    }

    // UPDATE INDIVIDUAL QR STATUS
    public function updateIndividualQrStatus(Request $request): JsonResponse
    {
        $UpdatedQrResponse = $this->tokenStatus($request->input('slave_qr_code'));

        if ($UpdatedQrResponse->status == "OK") {

            $MobileQr = new MobileQr();
            $MobileQr->updateQrStatusWithSlaveId($UpdatedQrResponse->data->trips[0]->tokenStatus, $request->input('slave_qr_code'));

            return response()->json(["status" => true, 'code' => env('STATUS_QR_STATUS_UPDATED_SUCCESSFULLY'), 'qr' => $UpdatedQrResponse->data->trips[0]]);

        } else return response()->json(['status' => true, 'code' => env('STATUS_QR_FAILED_TO_FETCH_STATUS'), 'error' => 'Unable to fetch status please try after some time!']);
    }

    /* CURLS */

    // ISSUE QR TOKEN
    private function issueToken(Request $request)
    {

        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $requestBody = json_decode($request->getContent());

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$BASE_URL/qrcode/issueToken",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => '',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "data": {
                    "activationTime"        : "' . $requestBody->data->activationTime . '",
                    "destination"           : "' . $requestBody->data->destination . '",
                    "email"                 : "' . $requestBody->data->email . '",
                    "fare"                  : "' . $requestBody->data->fare . '",
                    "mobile"                : "' . $requestBody->data->mobile . '",
                    "name"                  : "' . $requestBody->data->name . '",
                    "operationTypeId"       : "' . $requestBody->data->operationTypeId . '",
                    "operatorId"            : "' . $requestBody->data->operatorId . '",
                    "operatorTransactionId" : "' . $requestBody->data->operatorTransactionId . '",
                    "qrType"                : "' . $requestBody->data->qrType . '",
                    "source"                : "' . $requestBody->data->source . '",
                    "supportType"           : "' . $requestBody->data->supportType . '",
                    "tokenType"             : "' . $requestBody->data->tokenType . '",
                    "trips"                 : "' . $requestBody->data->trips . '"
                },
                "payment": {
                    "pass_price"            : "' . $requestBody->payment->pass_price . '",
                    "pgId"                  : "' . $requestBody->payment->pgId . '"
                }
            }',
            CURLOPT_HTTPHEADER => [
                "Authorization:  $AUTHORIZATION",
                'Content-Type:  application/json'
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    // GET TOKEN STATUS
    private function tokenStatus($slaveId)
    {
        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$BASE_URL/qrcode/status/$slaveId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                "Authorization: $AUTHORIZATION",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    //CRON
    public function statusCron()
    {

        $saleOrder = new SaleOrder();
        $orders = $saleOrder->getOrderToUpdateStatus();

        foreach ($orders as $order) {

            $mobileQr = new MobileQr();
            $qrs = $mobileQr->getQrFromOrderNo($order->order_no);
            $ticket_count = 0;

            foreach ($qrs as $qr) {

                $old_status = $qr->qr_status;
                $new_status = $this->tokenStatus($qr->slave_qr_code);

                if ($old_status != $new_status) $mobileQr->updateQrStatus($new_status, $order->order_no);
                if ($old_status === "COMPLETED" || $old_status === "EXPIRED") $ticket_count += 1;

            }

            if ($ticket_count == count($qrs)) $saleOrder->updateOrderStatus(600, $order->order_no);

        }

    }

}
