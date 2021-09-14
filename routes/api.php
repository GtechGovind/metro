<?php

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\MobileQrController;
use App\Http\Controllers\PassController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\SaleOrderController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('user', [UserController::class, 'verifyUser']);
Route::post('user/add', [UserController::class, 'createNewUser']);

/*
    MOBILE QR CODE
    1. CREATE ORDER
    2. CALL ISSUE TOKEN API
    3. INSERT DATA TO QR CODE TABLE AND ORDER TABLE
    4. UPDATE ORDER STATUS
    5. FETCH QR CODE
    6. UPDATE QR STATUS ON FETCH
*/

Route::post('order/add', [SaleOrderController::class, 'createNewOrder']);
Route::post('order/upcoming', [SaleOrderController::class, 'getUpcomingOrders']);

// MOBILE QR
Route::post('qr/new', [MobileQrController::class, 'createNewQr']);
Route::post('qr', [MobileQrController::class, 'getQrData']);

// STORE VALUE PASS
Route::post('pass/new', [PassController::class, 'createNewPass']);
Route::post('pass', [PassController::class, 'getUserPassWithStatus']);
Route::post('pass/trip/new', [PassController::class, 'generateNewTrip']);
Route::post('pass/reload', [PassController::class, 'reloadOldPass']);

// REFUND
Route::post('refund', [RefundController::class, 'getRefund']);
Route::post('refund/info', [RefundController::class, 'refundInfo']);

// CRON
Route::get('cron/status', [MobileQrController::class, 'statusCron']);

// CONFIG
Route::get('fares', [ConfigController::class, 'getFares']);
Route::get('stations', [ConfigController::class, 'getStation']);



