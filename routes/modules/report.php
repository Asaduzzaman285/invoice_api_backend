<?php

use App\Http\Controllers\Report\DetailsReportController;
use App\Http\Controllers\Report\SummaryReportController;
use App\Http\Controllers\Report\CheckByMSISDNReportController;


// Details Report
Route::group(['prefix' => 'v1/details-report'],function (){

    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [DetailsReportController::class, 'filterData'])->middleware('CheckPermission:details report');
        Route::get('list-paginate', [DetailsReportController::class, 'listPaginate'])->middleware('CheckPermission:details report');
        Route::post('report-download', [DetailsReportController::class, 'reportDownload'])->middleware('CheckPermission:details report download');
    });
});

// Summary Report
Route::group(['prefix' => 'v1/summary-report'],function (){

    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('filter-data', [SummaryReportController::class, 'filterData'])->middleware('CheckPermission:summary report');
        Route::get('list-paginate', [SummaryReportController::class, 'listPaginate'])->middleware('CheckPermission:summary report');
        Route::post('report-download', [SummaryReportController::class, 'reportDownload'])->middleware('CheckPermission:summary report download');
    });
});


// Check By MSISDN Report
Route::group(['prefix' => 'v1/check-by-msisdn-report'],function (){
    Route::group([ 'middleware'=>'auth:api'],function (){
        Route::get('report', [CheckByMSISDNReportController::class, 'report'])->middleware('CheckPermission:check by msisdn report');
    });
});

