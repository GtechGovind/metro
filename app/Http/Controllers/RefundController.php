<?php

namespace App\Http\Controllers;

use App\Models\RefundOrder;
use App\Models\SaleOrder;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    // REFUND THE ORDER
    public function getRefund(Request $request)
    {
        $Refund = $this->refund($request);
        $requestBody = json_decode($request->getContent());

        if ($Refund->status == "OK") {

            $saleOrder = new SaleOrder();
            $saleOrder->updateOrderStatus(env("STATUS_ORDER_REFUNDED"), $requestBody->data->operatorTransactionId);

            $refundOrder = new RefundOrder();
            $refundOrder->createRefundOrder($request, $Refund);

            return json_encode($Refund);

        } else return json_encode($Refund);

    }

    /* CURLS */

    // REFUND FOR ALL
    function refund(Request $request)
    {
        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $requestBody = json_decode($request->getContent());

        $source = ($requestBody->data->source != null) ? $requestBody->data->source : null;
        $destination = ($requestBody->data->destination != null) ? $requestBody->data->destination : null;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$BASE_URL/qrcode/refund",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "data": {
                    "operatorId"                    : "' . $requestBody->data->operatorId . '",
                    "supportType"                   : "' . $requestBody->data->supportType . '",
                    "qrType"                        : "' . $requestBody->data->qrType . '",
                    "tokenType"                     : "' . $requestBody->data->tokenType . '",
                    "source"                        : "' . $source . '",
                    "destination"                   : "' . $destination . '",
                    "remainingBalance"              : "' . $requestBody->data->remainingBalance . '",
                    "details": {
                        "registration": {
                            "processingFee"         : "' . $requestBody->data->details->registration->processingFee . '",
                            "refundType"            : "' . $requestBody->data->details->registration->refundType . '",
                            "processingFeeAmount"   : "' . $requestBody->data->details->registration->processingFeeAmount . '",
                            "refundAmount"          : "' . $requestBody->data->details->registration->refundAmount . '",
                            "passPrice"             : "' . $requestBody->data->details->registration->passPrice . '"
                        },
                        "pass": {
                            "processingFee"         : "' . $requestBody->data->details->pass->processingFee . '",
                            "refundType"            : "' . $requestBody->data->details->pass->refundType . '",
                            "processingFeeAmount"   : "' . $requestBody->data->details->pass->processingFeeAmount . '",
                            "refundAmount"          : "' . $requestBody->data->details->pass->refundAmount . '",
                            "passPrice"             : "' . $requestBody->data->details->pass->passPrice . '"
                        }
                    },
                    "operatorTransactionId"         : "' . $requestBody->data->operatorTransactionId . '",
                    "operationTypeId"               : "' . $requestBody->data->operationTypeId . '",
                    "masterTxnId"                   : "' . $requestBody->data->masterTxnId . '"
                }
            }',
            CURLOPT_HTTPHEADER => [
                "Authorization: $AUTHORIZATION",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }

    // GET REFUND INFO FOR ALL
    public function refundInfo(Request $request): string {

        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $tokenType = $request -> input('tokenType');
        $masterTxnId = $request -> input('masterTxnId');
        $operatorId = $request -> input('operatorId');


        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "$BASE_URL/qrcode/refund/info?tokenType=$tokenType&masterTxnId=$masterTxnId&operatorId=$operatorId",
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
        return $response;
    }


}
