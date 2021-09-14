<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Pass extends Model
{
    use HasFactory;

    // CHECK WEATHER THE PASS EXIST OR NOT
    public function isPassExist($number, $pass_type)
    {
        return DB::table('passes')
            ->where('number', '=', $number)
            ->where('pass_status', '=', 1)
            ->where('pass_type', '=', $pass_type)
            ->first();
    }

    // CREATE A NEW PASS
    public function createPass(Request $request, $response)
    {
        $requestBody = json_decode($request->getContent());
        $isPassExist = $this->isPassExist($requestBody->data->number, $response->data->tokenType);

        if (empty($isPassExist)) {

            return DB::table('s_passes')
                ->insert([

                    'order_no' => $requestBody->data->operatorTransactionId,
                    'masterTxnId' => $response->data->masterTxnId,
                    'pass_type' => $response->data->tokenType,
                    'number' => $requestBody->data->number,
                    'price' => $response->data->amount,
                    'source' => $response->data->source,
                    'destination' => $response->data->destination,
                    'balance' => $response->data->balance,
                    'trips' => $response->data->balanceTrip,
                    'operator_id' => $requestBody->data->operatorId,
                    'pass_status' => env('STATUS_PASS_GENERATED'),
                    'travel_date' => date('y-m-d h:i:m', $response->data->travelDate),
                    'master_expiry' => date('y-m-d h:i:m', $response->data->masterExpiry),
                    'grace_expiry' => date('y-m-d h:i:m', $response->data->graceExpiry)

                ]);

        } else return $isPassExist;

    }

    // UPDATE STATUS OF PASS
    public function updatePass($response): int
    {
        return DB::table('passes')
            ->where('masterTxnId', '=', $response -> data -> masterTxnId)
            ->update([
                'balance' => $response -> data -> balance,
                'trips' => $response->data->balanceTrip
            ]);
    }

}
