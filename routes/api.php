<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RSAController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\SMS\SmsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Dashboard\DashboardController;


// ========Auth===========
// ========Auth===========


Route::group([
    'prefix' => 'v1'
], function(){
    Route::post('login', [AuthController::class, 'login'])->name('login')->middleware("throttle:30,5");
    // ->middleware("throttle:3,5")
    Route::post('signup', [AuthController::class, 'signup']);
});

Route::group([
    'prefix' => 'v1',
    'middleware' => 'auth:api',
], function () {
    Route::post('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    // Route::post('changePassword', [AuthController::class, 'changePassword']);
    Route::post('profileUpdate', [AuthController::class, 'profileUpdate']);
});
// ========Auth===========
// ========Auth===========







// =======List Data==============
// =======List Data==============
Route::group(['prefix' => 'v1/list', 'middleware'=>'auth:api'],function (){

    Route::post('getAllUserList', [ListController::class, 'getAllUserList']);
});
// =======List Data==============
// =======List Data==============

// =======Audit Log Data==============
// =======Audit Log Data==============
Route::group(['prefix' => 'v1/audit-log'],function (){
    Route::post('createAuditLog', [AuditLogController::class, 'createAuditLog']);
    Route::group(['middleware'=>'auth:api'],function (){
        Route::post('getAllAuditLog_p', [AuditLogController::class, 'getAllAuditLog_p'])->middleware('CheckPermission:audit log list');
    });
});
// =======Audit Log Data==============
// =======Audit Log Data==============

// common
// common
// Route::get('bcryptGenerator/{password}', [CommonController::class, 'bcryptGenerator']);
Route::post('clear', [CommonController::class, 'clearCache']);
Route::get('test', [CommonController::class, 'test']);
Route::get('testDB', [CommonController::class, 'testDB']);

// Route::post('/test/preg_match', [TestController::class, 'preg_match']);


// =======RSA==============
// =======RSA==============
Route::group(['prefix' => 'v1/rsa', 'middleware'=>'auth:api' ],function (){
    Route::post('encrypt', [RSAController::class, 'encrypt']);
    Route::post('decrypt', [RSAController::class, 'decrypt']);
});
// =======RSA==============
// =======RSA==============




Route::get('send-sms', [SmsController::class, 'sendSms']);



// Dashboard Routes
Route::group(['prefix' => 'v1/dashboard', 'middleware' => 'auth:api'],function(){
    Route::get('dashboard-data', [DashboardController::class, 'getDashboardData']);
});


// Cart Routes
Route::group(['prefix' => 'v1/cart', 'middleware' => 'auth:api'],function(){
    Route::get('filter-data', [CartController::class, 'filterData'])->middleware('CheckPermission:cart list');
    Route::get('single-data/{id}', [CartController::class, 'singleData'])->middleware('CheckPermission:cart list');
    Route::get('list-paginate', [CartController::class, 'listPaginate'])->middleware('CheckPermission:cart list');
    Route::put('update', [CartController::class, 'update'])->middleware('CheckPermission:cart update');

    Route::get('payment-methods', [CartController::class, 'getPaymentMethodsData']);
});
