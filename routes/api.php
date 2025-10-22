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
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\CompanyInfoController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\PortalRoleController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Invoice2Controller;
// -----------------------
// Banks CRUD
// -----------------------
Route::get('/banks', [BankController::class, 'index']);      // List all banks
Route::get('/banks/{id}', [BankController::class, 'show']);  // Show single bank
Route::post('/banks', [BankController::class, 'store']);     // Create bank
Route::put('/banks/{id}', [BankController::class, 'update']); // Update bank
Route::delete('/banks/{id}', [BankController::class, 'destroy']); // Delete bank

// -----------------------
// Bank Accounts CRUD
// -----------------------
Route::get('/bank-accounts', [BankAccountController::class, 'index']);
Route::get('/bank-accounts/{id}', [BankAccountController::class, 'show']);
Route::post('/bank-accounts', [BankAccountController::class, 'store']);
Route::put('/bank-accounts/{id}', [BankAccountController::class, 'update']);
Route::delete('/bank-accounts/{id}', [BankAccountController::class, 'destroy']);

// -----------------------
// Company Info CRUD
// -----------------------
Route::get('/companies', [CompanyInfoController::class, 'index']);
Route::get('/companies/{id}', [CompanyInfoController::class, 'show']);
Route::post('/companies', [CompanyInfoController::class, 'store']);
Route::put('/companies/{id}', [CompanyInfoController::class, 'update']);
Route::delete('/companies/{id}', [CompanyInfoController::class, 'destroy']);

// -----------------------
// Applications CRUD
// -----------------------
Route::get('/applications', [ApplicationController::class, 'index']);
Route::get('/applications/{id}', [ApplicationController::class, 'show']);
Route::post('/applications', [ApplicationController::class, 'store']);
Route::put('/applications/{id}', [ApplicationController::class, 'update']);
Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);

// -----------------------
// Portal Roles CRUD
// -----------------------
Route::get('/roles', [PortalRoleController::class, 'index']);
Route::get('/roles/{id}', [PortalRoleController::class, 'show']);
Route::post('/roles', [PortalRoleController::class, 'store']);
Route::put('/roles/{id}', [PortalRoleController::class, 'update']);
Route::delete('/roles/{id}', [PortalRoleController::class, 'destroy']);

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
// Route::get('index/{id}', [UsersController::class, 'index']);
Route::get('/index/{id?}', [UsersController::class, 'index']);


// Cart Routes
Route::group(['prefix' => 'v1/cart', 'middleware' => 'auth:api'],function(){
    Route::get('filter-data', [CartController::class, 'filterData'])->middleware('CheckPermission:cart list');
    Route::get('single-data/{id}', [CartController::class, 'singleData'])->middleware('CheckPermission:cart list');
    Route::get('list-paginate', [CartController::class, 'listPaginate'])->middleware('CheckPermission:cart list');
    Route::put('update', [CartController::class, 'update'])->middleware('CheckPermission:cart update');

    Route::get('payment-methods', [CartController::class, 'getPaymentMethodsData']);
});
Route::get('get-support-data', [Invoice2Controller::class, 'getsupportdata']);
Route::post('get-support-data', [Invoice2Controller::class, 'getsupportdata']);


Route::get('/invoices', [Invoice2Controller::class, 'index']);
Route::post('/invoices', [Invoice2Controller::class, 'store']);
Route::get('/invoices/{id}', [Invoice2Controller::class, 'show']);
Route::put('/invoices/{id}', [Invoice2Controller::class, 'update']); // ADD THIS
Route::delete('/invoices/{id}', [Invoice2Controller::class, 'destroy']);

// Bank accounts route (ADD THIS - your frontend needs it!)
Route::get('/bank-accounts', [Invoice2Controller::class, 'getBankAccounts']);

// Support data route (ADD THIS - your frontend needs it!)
Route::get('/get-support-data', [Invoice2Controller::class, 'getsupportdata']);
