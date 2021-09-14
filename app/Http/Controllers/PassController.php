<?php

namespace App\Http\Controllers;

use App\Models\MobileQr;
use App\Models\Pass;
use App\Models\SaleOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PassController extends Controller
{
    // CREATE NEW PASS
    public function createNewPass(Request $request)
    {

        $PassResponse = $this->issuePass($request);
        $requestBody = json_decode($request->getContent());

        if ($PassResponse->status === "OK") {

            $pass = new Pass();
            $pass->createPass($request, $PassResponse);

            $order = new SaleOrder();
            $order->updateOrderStatus(env('STATUS_ORDER_QR_GENERATED'), $requestBody->data->operatorTransactionId);
            $order->updateOrderMaster($PassResponse->data->masterTxnId, $requestBody->data->operatorTransactionId);

            return json_encode($PassResponse);

        } else return json_encode($PassResponse);

    }

    // CHECK IF PASS EXIST / UPDATE STATUS OF PASS
    public function getUserPassWithStatus(Request $request): JsonResponse
    {

        $Pass = new Pass();
        $isPassExist = $Pass->isPassExist($request->input('number'), $request->input('pass_type'));

        if (empty($isPassExist)) return response()->json(["status" => false, "code" => env('STATUS_NO_PASS_FOUND'), "error" => "User have no active Pass!"]);
        else {
            $passData = $this->passStatus($isPassExist->master_qr_code);
            $newPassData = json_decode($passData, true);
            $newPassData['data']['order_no'] = $isPassExist->order_no;
            return response()->json(['status' => true, 'code' => env('STATUS_PASS_FETCHED_SUCCESSFULLY'), 'data' => $newPassData->data]);
        }

    }

    // GENERATE A NEW TRIP FOR PASS
    public function generateNewTrip(Request $request)
    {

        $TripResponse = $this->issueTrip($request);

        if ($TripResponse->status == "OK") {

            foreach ($TripResponse->data->trips as $trip) {
                $qr = new MobileQr();
                $qr->crateMobileQr($request, $TripResponse->data->masterTxnId, $trip);
            }

            $order = new SaleOrder();
            $order->updateOrderStatus(env('STATUS_ORDER_QR_GENERATED'), $request->input('order_no'));

            return json_encode($TripResponse);

        } else return json_encode($TripResponse);

    }

    // RELOAD PASS
    public function reloadOldPass(Request $request)
    {
        $ReloadPass = $this->reloadPass($request);

        if ($ReloadPass->status == "OK") {

            $Pass = new Pass();
            $Pass->updatePass($ReloadPass);

            return json_encode($ReloadPass);

        } else return json_encode($ReloadPass);

    }

    /*CURL*/

    // ISSUE NEW PASS
    public function issuePass(Request $request)
    {
        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");
        $requestBody = json_decode($request->getContent());

        $source = ($requestBody->data->source != null) ? $requestBody->data->source : null;
        $destination = ($requestBody->data->destination != null) ? $requestBody->data->destination : null;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$BASE_URL/qrcode/issuePass",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "data": {
                    "fare"                  : "' . $requestBody->data->fare . '",
                    "supportType"           : "' . $requestBody->data->supportType . '",
                    "qrType"                : "' . $requestBody->data->qrType . '",
                    "tokenType"             : "' . $requestBody->data->tokenType . '",
                    "operationTypeId"       : "' . $requestBody->data->operationTypeId . '",
                    "source"                : "' . $source . '",
                    "destination"           : "' . $destination . '",
                    "operatorId"            : "' . $requestBody->data->operatorId . '",
                    "name"                  : "' . $requestBody->data->name . '",
                    "email"                 : "' . $requestBody->data->email . '",
                    "mobile"                : "' . $requestBody->data->mobile . '",
                    "activationTime"        : "' . $requestBody->data->activationTime . '",
                    "operatorTransactionId" : "' . $requestBody->data->operatorTransactionId . '"
                },
                "payment": {
                    "pass_price"            : "' . $requestBody->payment->pass_price . '",
                    "pgId"                  : "' . $requestBody->payment->pgId . '"
                }
            }',
            CURLOPT_HTTPHEADER => [
                "Authorization: $AUTHORIZATION",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);
    }

    // ISSUE NEW TRIP
    public function issueTrip(Request $request)
    {
        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$BASE_URL/qrcode/issueTrip",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "data": {
                    "tokenType"             : "' . $request->input('tokenType') . '",
                    "operationTypeId"       : "' . $request->input('operationTypeId') . '",
                    "operatorId"            : "' . $request->input('operatorId') . '",
                    "name"                  : "' . $request->input('name') . '",
                    "email"                 : "' . $request->input('email') . '",
                    "mobile"                : "' . $request->input('mobile') . '",
                    "activationTime"        : "' . $request->input('activationTime') . '",
                    "masterTxnId"           : "' . $request->input('masterTxnId') . '"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                "Authorization: $AUTHORIZATION",
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    // GET PASS STATUS WITH MASTER TXN ID
    public function passStatus($masterTxnId)
    {
        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$BASE_URL/qrcode/bookings?masterTxnId=$masterTxnId",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                "Authorization: $AUTHORIZATION",
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $Response = json_decode($response);

        $Pass = new Pass();
        $Pass->updatePass($Response);

        return $Response;

    }

    // RELOAD PASS
    public function reloadPass(Request $request)
    {

        $requestBody = json_decode($request->getContent());

        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $source = ($requestBody->data->source != null) ? $requestBody->data->source : null;
        $destination = ($requestBody->data->destination != null) ? $requestBody->data->destination : null;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$BASE_URL/qrcode/reloadPass",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "data": {
                    "fare"                  : "' . $requestBody->data->fare . '",
                    "source"                : "' . $source . '",
                    "destination"           : "' . $destination . '",
                    "tokenType"             : "' . $requestBody->data->tokenType . '",
                    "operationTypeId"       : "' . $requestBody->data->operationTypeId . '",
                    "operatorId"            : "' . $requestBody->data->operatorId . '",
                    "name"                  : "' . $requestBody->data->name . '",
                    "email"                 : "' . $requestBody->data->email . '",
                    "mobile"                : "' . $requestBody->data->mobile . '",
                    "trips"                 : "' . $requestBody->data->trips . '",
                    "activationTime"        : "' . $requestBody->data->activationTime . '",
                    "operatorTransactionId" : "' . $requestBody->data->operatorTransactionId . '",
                    "masterTxnId"           : "' . $requestBody->data->masterTxnId . '"
                },
                "payment": {
                    "pass_price"            : "' . $requestBody->payment->pass_price . '",
                    "pgId"                  : "' . $requestBody->payment->pgId . '"
                }
            }',
            CURLOPT_HTTPHEADER => array(
                "Authorization: $AUTHORIZATION",
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response, true);

    }

}
