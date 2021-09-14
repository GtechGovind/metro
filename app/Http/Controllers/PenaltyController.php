<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PenaltyController extends Controller
{

    /* CURL */

    // GET PENALTY INFO
    public function penaltyInfo(Request $request)
    {

        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");

        $QrId = $request->input('QrId');
        $StationID = $request->input('StationID');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$BASE_URL/qrcode/penalty/status?transactionId=$QrId&station=$StationID",
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
        return $response;
    }

    // GENERATE NEW QR IN CASE OF PENALTY
    public function issueTokenPenalty(Request $request)
    {

        $BASE_URL = env("MMOPL_BASE_API_URL");
        $AUTHORIZATION = env("MMOPL_BASE_AUTH_KEY");
        $requestBody = json_decode($request->getContent());

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$BASE_URL/qrcode/penalty/issueToken",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "data": {
                    "fare"                      : "'. $requestBody -> data -> fare .'",
                    "destination"               : "'. $requestBody -> data -> destination .'",
                    "refTxnId"                  : "'. $requestBody -> data -> refTxnId .'"",
                    "tokenType"                 : "'. $requestBody -> data -> tokenType .'",
                    "supportType"               : "'. $requestBody -> data -> supportType .'",
                    "qrType"                    : "'. $requestBody -> data -> qrType .'",
                    "operatorId"                : "'. $requestBody -> data -> operatorId .'",
                    "operatorTransactionId"     : "'. $requestBody -> data -> operatorTransactionId .'",
                    "activationTime"            : "'. $requestBody -> data -> activationTime .'",
                    "freeExitOptionId"          : "'. $requestBody -> data -> freeExitOptionId .'",
                    "penalties": [
                        {
                            "excessTime"        : "'. $requestBody -> data -> penalties[0] -> excessTime .'",
                            "operationTypeId"   : "'. $requestBody -> data -> penalties[0] -> operationTypeId .'",
                            "amount"            : "'. $requestBody -> data -> penalties[0] -> amount .'"
                        }
                    ],
                    "overTravelCharges": [
                        {
                            "operationTypeId"   : "'. $requestBody -> data -> overTravelCharges[0] -> operationTypeId .'",
                            "amount"            : "'. $requestBody -> data -> overTravelCharges[0] -> amount .'"
                        }
                    ]
                },
                "payment": {
                    "pass_price"                : "'. $requestBody -> payment -> pass_price .'"
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
}
