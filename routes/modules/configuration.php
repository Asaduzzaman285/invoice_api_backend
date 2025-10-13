<?php
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Configuration\FileController;
use App\Http\Controllers\Configuration\EventsController;
use App\Http\Controllers\Configuration\HomeAdsController;
use App\Http\Controllers\Configuration\MembersController;
use App\Http\Controllers\Configuration\ProductController;
use App\Http\Controllers\Configuration\HomeMainSliderController;
use App\Http\Controllers\Configuration\SuccessStoriesController;
use App\Http\Controllers\Configuration\EventTicketTypeController;
use App\Http\Controllers\CorporateServiceController;
// =======super admin==============
// =======super admin==============

Route::group([
    'prefix' => 'v1'

], function () {
    // users
    Route::post('getAllUsers_p', [UsersController::class, 'getAllUsers_p']);
    Route::post('getAllUsers', [UsersController::class, 'getAllUsers']);
    Route::post('getUser', [UsersController::class, 'getUser']);
    // ->middleware('CheckPermission:user data')
    Route::post('createUser', [UsersController::class, 'createUser']);
    Route::post('updateUser', [UsersController::class, 'updateUser']);

});


// Roles routes
Route::group(['prefix' => 'v1/role', 'middleware' => 'auth:api'],function(){
    Route::post('getAllRoles', [RoleController::class, 'getAllRoles'])->middleware('CheckPermission:role list');
    Route::post('getAllRoles_p', [RoleController::class, 'getAllRoles_p'])->middleware('CheckPermission:role list');
    Route::post('getRole', [RoleController::class, 'getRole'])->middleware('CheckPermission:role list');
    Route::post('createRole', [RoleController::class, 'createRole'])->middleware('CheckPermission:role create');
    Route::post('updateRole', [RoleController::class, 'updateRole'])->middleware('CheckPermission:role update');
    Route::post('deleteRole', [RoleController::class, 'deleteRole'])->middleware('CheckPermission:role delete');
});

//  Permission routes
Route::group(['prefix' => 'v1/permission', 'middleware'=>'auth:api'],function (){
    Route::post('getAllpermissions', [PermissionController::class, 'getAllpermissions'])->middleware('CheckPermission:permission list');
    Route::post('getAllPermissions_p', [PermissionController::class, 'getAllPermissions_p'])->middleware('CheckPermission:permission list');
    Route::post('getPermission', [PermissionController::class, 'getPermission'])->middleware('CheckPermission:permission data');
    Route::post('createPermission', [PermissionController::class, 'createPermission'])->middleware('CheckPermission:permission create');
    Route::post('updatePermission', [PermissionController::class, 'updatePermission'])->middleware('CheckPermission:permission update');
    Route::post('deletePermission', [PermissionController::class, 'deletePermission'])->middleware('CheckPermission:permission delete');
});

//  Permission Module routes
Route::group(['prefix' => 'v1/module', 'middleware'=>'auth:api'],function (){
    Route::post('getAllModules', [ModuleController::class, 'getAllModules']);
});

// =======super admin==============
// =======super admin==============

// Members
Route::group(['prefix' => 'v1/members'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [MembersController::class, 'filterData'])->middleware('CheckPermission:members list');
        Route::get('list-paginate', [MembersController::class, 'listPaginate'])->middleware('CheckPermission:members list');
        Route::get('single-data/{id}', [MembersController::class, 'singleData'])->middleware('CheckPermission:members list');
        Route::post('create', [MembersController::class, 'create'])->middleware('CheckPermission:members create');
        Route::put('update', [MembersController::class, 'update'])->middleware('CheckPermission:members update');
    });
});


// Success Stories
Route::group(['prefix' => 'v1/success-stories'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [SuccessStoriesController::class, 'filterData'])->middleware('CheckPermission:success stories list');
        Route::get('list-paginate', [SuccessStoriesController::class, 'listPaginate'])->middleware('CheckPermission:success stories list');
        Route::get('single-data/{id}', [SuccessStoriesController::class, 'singleData'])->middleware('CheckPermission:success stories list');
        Route::post('create', [SuccessStoriesController::class, 'create'])->middleware('CheckPermission:success stories create');
        Route::put('update', [SuccessStoriesController::class, 'update'])->middleware('CheckPermission:success stories update');
    });
});

// Product
Route::group(['prefix' => 'v1/product'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [ProductController::class, 'filterData'])->middleware('CheckPermission:product list');
        Route::get('list-paginate', [ProductController::class, 'listPaginate'])->middleware('CheckPermission:product list');
        Route::get('single-data/{id}', [ProductController::class, 'singleData'])->middleware('CheckPermission:product list');
        Route::post('create', [ProductController::class, 'create'])->middleware('CheckPermission:product create');
        Route::put('update', [ProductController::class, 'update'])->middleware('CheckPermission:product update');
    });
});




// HomeMainSlider
// Route::group(['prefix' => 'v1/home-main-slider'],function (){
//     Route::group([ 'middleware'=>'auth:api'],function (){
//         Route::get('filter-data', [HomeMainSliderController::class, 'filterData'])->middleware('CheckPermission:home main slider list');
//         Route::get('list-paginate', [HomeMainSliderController::class, 'listPaginate'])->middleware('CheckPermission:home main slider list');
//         Route::get('single-data/{id}', [HomeMainSliderController::class, 'singleData'])->middleware('CheckPermission:home main slider list');
//         Route::post('create', [HomeMainSliderController::class, 'create'])->middleware('CheckPermission:home main slider create');
//         Route::put('update', [HomeMainSliderController::class, 'update'])->middleware('CheckPermission:home main slider update');
//     });
// });
Route::group(['prefix' => 'v1/home-main-slider'], function () {
    Route::get('filter-data', [HomeMainSliderController::class, 'filterData']);
    Route::get('list-paginate', [HomeMainSliderController::class, 'listPaginate']);
    Route::get('single-data/{id}', [HomeMainSliderController::class, 'singleData']);
    Route::post('create', [HomeMainSliderController::class, 'create']);
    Route::put('update', [HomeMainSliderController::class, 'update']);
});

// CorporateService
Route::group(['prefix' => 'v1/corporate-services'], function () {
    Route::get('list', [CorporateServiceController::class, 'index']);         // List all services (paginated)
    Route::get('single/{id}', [CorporateServiceController::class, 'show']);   // Get single by ID
    Route::post('create', [CorporateServiceController::class, 'store']);      // Create new
    Route::put('update', [CorporateServiceController::class, 'update']);      // Update
    Route::delete('delete/{id}', [CorporateServiceController::class, 'destroy']); // Delete
});

// HomeAds
Route::group(['prefix' => 'v1/home-ads'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [HomeAdsController::class, 'filterData'])->middleware('CheckPermission:home ads list');
        Route::get('list-paginate', [HomeAdsController::class, 'listPaginate'])->middleware('CheckPermission:home ads list');
        Route::get('single-data/{id}', [HomeAdsController::class, 'singleData'])->middleware('CheckPermission:home ads list');
        Route::post('create', [HomeAdsController::class, 'create'])->middleware('CheckPermission:home ads create');
        Route::put('update', [HomeAdsController::class, 'update'])->middleware('CheckPermission:home ads update');
    });
});





// File
// Route::group(['prefix' => 'v1/file'],function (){

//     Route::group(function (){
//         Route::post('file-upload', [FileController::class, 'fileUpload']);
//     });
// });

Route::group(['prefix' => 'v1/general'], function () {
    Route::post('/file/file-upload', [FileController::class, 'fileUpload']);
});




// Events
Route::group(['prefix' => 'v1/events'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [EventsController::class, 'filterData'])->middleware('CheckPermission:event list');
        Route::get('list-paginate', [EventsController::class, 'listPaginate'])->middleware('CheckPermission:event list');
        Route::get('single-data/{id}', [EventsController::class, 'singleData'])->middleware('CheckPermission:event list');
        Route::post('create', [EventsController::class, 'create'])->middleware('CheckPermission:event create');
        Route::put('update', [EventsController::class, 'update'])->middleware('CheckPermission:event update');
    });
});


// Events Ticket Types
Route::group(['prefix' => 'v1/event-ticket-type'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [EventTicketTypeController::class, 'filterData'])->middleware('CheckPermission:event ticket type list');
        Route::get('list-paginate', [EventTicketTypeController::class, 'listPaginate'])->middleware('CheckPermission:event ticket type list');
        Route::get('single-data/{id}', [EventTicketTypeController::class, 'singleData'])->middleware('CheckPermission:event ticket type list');
        Route::post('create', [EventTicketTypeController::class, 'create'])->middleware('CheckPermission:event ticket type create');
        Route::put('update', [EventTicketTypeController::class, 'update'])->middleware('CheckPermission:event ticket type update');
    });
});
